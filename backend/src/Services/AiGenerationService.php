<?php
namespace App\Services;

use App\Repositories\AiJobRepository;
use App\Repositories\AiContentRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\McqRepository;
use App\Repositories\ContentRepository;

/**
 * Orchestrates AI generation for a lecture as a sequence of small steps.
 *
 * Each call to step() performs exactly ONE unit of work (one AI request or one
 * extraction) and advances the job. This keeps every HTTP request short enough
 * for shared hosting, lets the review UI show live progress by polling, and
 * makes generation resumable if a step fails.
 *
 * Generated content is always saved as `draft` — nothing is visible to
 * students until a teacher approves and publishes it.
 */
class AiGenerationService
{
    private const FLASHCARD_BATCH = 20;
    private const MCQ_BATCH = 10;

    public function __construct(
        private AiContentService $ai,
        private TextExtractionService $extractor,
        private AiJobRepository $jobs,
        private AiContentRepository $content,
        private FlashcardRepository $flashcards,
        private McqRepository $mcqs,
        private ContentRepository $lectures,
        private NotificationService $notifications
    ) {}

    public function isReady(): bool
    {
        return $this->ai->isReady();
    }

    /**
     * Queue a generation job for a lecture.
     *
     * @param array $options ['summary'=>bool,'notes'=>bool,'flashcards'=>int,'mcqs'=>int]
     * @param string|null $text Optional pre-supplied text (skips extraction).
     */
    public function enqueue(int $lectureId, ?int $courseId, ?int $userId, array $options, ?string $text = null): int
    {
        $jobId = $this->jobs->create($lectureId, $courseId, $userId, $options);
        $this->content->ensure($lectureId, $courseId, $userId);

        if ($text !== null && trim($text) !== '') {
            $this->jobs->update($jobId, [
                'source_text'  => $text,
                'source_chars' => mb_strlen($text),
                'current_step' => 'summary',
                'progress'     => 10,
            ]);
        }
        return $jobId;
    }

    /**
     * Perform the next step of a job. Returns the refreshed job array.
     * Safe to call repeatedly; becomes a no-op once completed/failed.
     */
    public function step(int $jobId): array
    {
        $job = $this->jobs->find($jobId);
        if (!$job) {
            throw new \RuntimeException('Generation job not found.');
        }
        if (in_array($job['status'], ['completed', 'failed'], true)) {
            return $job;
        }

        $this->jobs->update($jobId, ['status' => 'processing']);

        try {
            match ($job['current_step']) {
                'extract'    => $this->stepExtract($job),
                'summary'    => $this->stepSummary($job),
                'notes'      => $this->stepNotes($job),
                'flashcards' => $this->stepFlashcards($job),
                'mcqs'       => $this->skipMcqs($job),
                default      => $this->finish($job),
            };
        } catch (\Throwable $e) {
            $this->jobs->setError($jobId, $e->getMessage());
        }

        return $this->jobs->find($jobId) ?? $job;
    }

    // ── steps ──────────────────────────────────────────────────

    private function stepExtract(array $job): void
    {
        $source = $this->lectures->getLectureSourceFile((int) $job['lecture_id']);
        if (!$source) {
            throw new \RuntimeException(
                'No PowerPoint, PDF, or document found on this lecture to extract text from. '
                . 'Upload a file first, or provide the lecture text manually.'
            );
        }

        $text = $this->extractor->extract($source['file_path'], $source['ext']);
        if (mb_strlen($text) < 200) {
            throw new \RuntimeException(
                'Could only extract ' . mb_strlen($text) . ' characters. The file may be scanned/image-based. '
                . 'Please paste the lecture text manually to continue.'
            );
        }

        $this->jobs->update((int) $job['id'], [
            'source_text'  => $text,
            'source_chars' => mb_strlen($text),
            'current_step' => 'summary',
            'progress'     => 10,
        ]);
    }

    private function stepSummary(array $job): void
    {
        $summary = $this->ai->generateSummary($job['source_text'] ?? '');
        $this->content->updateByLecture((int) $job['lecture_id'], ['summary' => $summary]);
        $this->jobs->update((int) $job['id'], ['current_step' => 'notes', 'progress' => 25]);
    }

    private function stepNotes(array $job): void
    {
        $opts = $job['options'] ?? [];
        $notes = $this->ai->generateNotes($job['source_text'] ?? '');
        $this->content->updateByLecture((int) $job['lecture_id'], $notes);
        $next = ((int) ($opts['flashcards'] ?? 0)) > 0 ? 'flashcards'
              : (((int) ($opts['mcqs'] ?? 0)) > 0 ? 'mcqs' : 'done');
        $this->jobs->update((int) $job['id'], ['current_step' => $next, 'progress' => 45]);
    }

    private function stepFlashcards(array $job): void
    {
        $target = (int) $job['flashcard_target'];
        $done   = (int) $job['flashcard_done'];
        $remaining = $target - $done;

        if ($remaining <= 0) {
            $this->advanceAfterFlashcards($job);
            return;
        }

        $batch = min(self::FLASHCARD_BATCH, $remaining);
        $existing = $this->flashcards->existingFronts((int) $job['lecture_id']);
        $context = $this->contextForFlashcardsAndMcqs($job);
        $cards = $this->ai->generateFlashcards($context, $batch, $existing);
        $inserted = $this->flashcards->insertMany(
            (int) $job['lecture_id'], $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null, $cards
        );

        // Count the batch as consumed even if dedup produced fewer (avoids loops).
        $newDone = $done + max($inserted, (int) ($batch / 2));
        $newDone = min($newDone, $target);
        $progress = 45 + (int) (20 * $newDone / max($target, 1));

        $this->jobs->update((int) $job['id'], [
            'flashcard_done' => $newDone,
            'progress'       => min($progress, 65),
        ]);

        if ($newDone >= $target || $inserted === 0) {
            $job['flashcard_done'] = $target;
            $this->advanceAfterFlashcards($job);
        }
    }

    private function advanceAfterFlashcards(array $job): void
    {
        // Skip AI MCQ generation — teachers upload MCQs manually.
        $this->jobs->update((int) $job['id'], ['current_step' => 'done', 'progress' => 68]);
        $this->finish($job);
    }

    /** Legacy jobs that still land on the MCQ step: finish without generating. */
    private function skipMcqs(array $job): void
    {
        $this->finish($job);
    }

    private function finish(array $job): void
    {
        $this->jobs->update((int) $job['id'], [
            'current_step' => 'done',
            'status'       => 'completed',
            'progress'     => 100,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        if (!empty($job['requested_by'])) {
            $lecture = $this->lectures->getLecture((int) $job['lecture_id']);
            $title = $lecture['title'] ?? 'lecture';
            $this->notifications->notify(
                (int) $job['requested_by'],
                'ai_generation',
                'Study resources ready for review',
                "Generated study material for \"{$title}\" is ready. Review and approve it to publish.",
                ['lecture_id' => (int) $job['lecture_id']],
                false
            );
        }
    }

    /**
     * Flashcards & MCQs use the generated summary/notes (small) instead of the full
     * lecture PDF text — keeps each Groq request under the TPM limit while still
     * covering the whole lecture (summary/notes were built from the full text).
     */
    private function contextForFlashcardsAndMcqs(array $job): string
    {
        $lectureId = (int) $job['lecture_id'];
        $content = $this->content->findByLecture($lectureId);
        $parts = [];

        if ($content) {
            if (!empty($content['summary'])) {
                $parts[] = "LECTURE SUMMARY:\n" . trim((string) $content['summary']);
            }
            if (!empty($content['revision_notes'])) {
                $parts[] = "REVISION NOTES:\n" . trim((string) $content['revision_notes']);
            }
            if (!empty($content['high_yield_points']) && is_array($content['high_yield_points'])) {
                $parts[] = "HIGH-YIELD POINTS:\n- " . implode("\n- ", $content['high_yield_points']);
            }
            if (!empty($content['clinical_pearls']) && is_array($content['clinical_pearls'])) {
                $parts[] = "CLINICAL PEARLS:\n- " . implode("\n- ", $content['clinical_pearls']);
            }
        }

        $combined = trim(implode("\n\n", $parts));
        if (mb_strlen($combined) >= 500) {
            // ~10k tokens — safely under Groq free 30k TPM per request.
            if (mb_strlen($combined) > 40000) {
                $combined = mb_substr($combined, 0, 40000) . "\n...[content truncated for API limit]";
            }
            return $combined;
        }

        // Fallback if notes step was skipped: sample from source text, not the full PDF.
        $source = $job['source_text'] ?? '';
        if (mb_strlen($source) <= 15000) {
            return $source;
        }
        $slice = 5000;
        $len = mb_strlen($source);
        return mb_substr($source, 0, $slice)
            . "\n...\n"
            . mb_substr($source, (int) ($len / 2 - $slice / 2), $slice)
            . "\n...\n"
            . mb_substr($source, $len - $slice, $slice);
    }
}
