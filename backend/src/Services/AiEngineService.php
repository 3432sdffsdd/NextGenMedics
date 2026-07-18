<?php
namespace App\Services;

use App\AI\MeteredAiProviderInterface;
use App\AI\Prompts\PromptBuilder;
use App\Repositories\AiContentRepository;
use App\Repositories\AiGenerationLogRepository;
use App\Repositories\AiJobRepository;
use App\Repositories\AiJobStageRepository;
use App\Repositories\ClinicalCaseRepository;
use App\Repositories\ContentRepository;
use App\Repositories\DiseaseComparisonRepository;
use App\Repositories\DrugTableRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\McqRepository;
use App\Repositories\MnemonicRepository;
use App\Repositories\RevisionSheetRepository;
use App\Repositories\VideoSimulationRepository;
use App\Repositories\VivaQuestionRepository;
use App\Services\AiEngine\StageCatalog;

/**
 * Gemini-powered staged generation engine (6 stage groups).
 *
 * One HTTP / worker call to step() advances exactly one unit of work so the
 * UI can poll live progress. Completed stages are never regenerated.
 * Students never call this service — they read cached SQL rows only.
 */
class AiEngineService
{
    private const FLASHCARD_BATCH = 10;
    private const MCQ_BATCH = 10;
    private const VIVA_BATCH = 10;
    private const CASE_BATCH = 5;

    private int $maxInputChars;

    public function __construct(
        private MeteredAiProviderInterface $gemini,
        private PromptBuilder $prompts,
        private TextExtractionService $extractor,
        private AiJobRepository $jobs,
        private AiJobStageRepository $stages,
        private AiGenerationLogRepository $logs,
        private AiContentRepository $content,
        private FlashcardRepository $flashcards,
        private McqRepository $mcqs,
        private DrugTableRepository $drugs,
        private DiseaseComparisonRepository $comparisons,
        private MnemonicRepository $mnemonics,
        private VivaQuestionRepository $viva,
        private ClinicalCaseRepository $cases,
        private RevisionSheetRepository $revisionSheets,
        private VideoSimulationRepository $videos,
        private ContentRepository $lectures,
        private NotificationService $notifications,
        ?int $maxInputChars = null
    ) {
        $this->maxInputChars = max(4000, $maxInputChars ?? 100000);
    }

    public function isReady(): bool
    {
        return $this->gemini->isConfigured();
    }

    public function providerName(): string
    {
        return $this->gemini->name();
    }

    public function model(): string
    {
        return $this->gemini->model();
    }

    /**
     * Enqueue a full engine run for a lecture.
     * @param string|null $text Optional pre-supplied lecture text (skips extraction).
     */
    public function enqueue(int $lectureId, ?int $courseId, ?int $userId, ?string $text = null): int
    {
        $options = [
            'flashcards' => 30,
            'mcqs'       => 30,
            'viva'       => 10,
            'cases'      => 5,
            'model'      => $this->gemini->model(),
        ];

        $jobId = $this->jobs->create($lectureId, $courseId, $userId, $options, true);
        $this->content->ensure($lectureId, $courseId, $userId);
        $this->stages->seedForJob($jobId, StageCatalog::all());

        if ($text !== null && trim($text) !== '') {
            $this->jobs->update($jobId, [
                'source_text'  => $text,
                'source_chars' => mb_strlen($text),
                'stage_label'  => 'Text Extraction',
                'progress'     => 5,
                'model'        => $this->gemini->model(),
            ]);
            $extract = $this->stages->findByKey($jobId, 'extract');
            if ($extract) {
                $this->stages->markCompleted((int) $extract['id']);
            }
        }

        // Background worker continues the job even if the teacher closes the tab.
        AiWorkerLauncher::kick();

        return $jobId;
    }

    /**
     * Advance one unit of work. Returns job + stages for the progress UI.
     * Transient API/parsing errors are re-queued automatically — the job stays
     * in processing so the UI can keep polling without a manual Resume click.
     */
    public function step(int $jobId): array
    {
        $job = $this->jobs->find($jobId);
        if (!$job) {
            throw new \RuntimeException('Generation job not found.');
        }
        if (in_array($job['status'], ['completed', 'cancelled'], true)) {
            return $this->snapshot($jobId);
        }

        // Auto-heal any failed stages so the next unit of work can continue
        // without a manual Resume click (worker + UI both rely on this).
        $this->stages->resetFailed($jobId);
        if (($job['status'] ?? '') === 'failed') {
            $this->jobs->update($jobId, ['status' => 'processing', 'error' => null]);
            $job = $this->jobs->find($jobId) ?? $job;
        }

        $this->jobs->update($jobId, [
            'status' => 'processing',
            'model'  => $this->gemini->model(),
        ]);

        $stage = $this->stages->nextActionable($jobId);
        // Skip stages that are no longer in the catalog (e.g. old full-pack jobs).
        while ($stage && !StageCatalog::find($stage['stage_key'])) {
            $this->stages->markSkipped((int) $stage['id']);
            $stage = $this->stages->nextActionable($jobId);
        }
        if (!$stage) {
            $this->finish($job);
            return $this->snapshot($jobId);
        }

        try {
            $this->runStage($job, $stage);
        } catch (\Throwable $e) {
            $this->handleStageFailure($job, $stage, $e);
        }

        return $this->snapshot($jobId);
    }

    /**
     * Soft-fail a stage and keep the job running whenever the error looks transient.
     * Only permanent configuration/content errors mark the job as failed.
     */
    private function handleStageFailure(array $job, array $stage, \Throwable $e): void
    {
        $jobId = (int) $job['id'];
        $stageId = (int) $stage['id'];
        $msg = $e->getMessage();
        $stageRetries = (int) ($stage['retries'] ?? 0) + 1;
        $jobRetries = (int) ($job['retries'] ?? 0) + 1;

        $this->logs->log([
            'job_id'    => $jobId,
            'stage_id'  => $stageId,
            'stage_key' => $stage['stage_key'] ?? null,
            'model'     => $this->gemini->model(),
            'status'    => 'error',
            'retries'   => $stageRetries,
            'error'     => $msg,
        ]);

        if ($this->isPermanentError($msg)) {
            $this->stages->markFailed($stageId, $msg, ['retries' => 1]);
            $this->jobs->setError($jobId, $msg);
            $this->jobs->update($jobId, [
                'stage_label' => $stage['title'] ?? $stage['stage_key'],
                'retries'     => $jobRetries,
            ]);
            return;
        }

        // Transient: re-queue the same stage and keep job processing for auto-resume.
        $this->stages->requeueAfterError($stageId, $msg, $stageRetries);
        $this->jobs->update($jobId, [
            'status'       => 'processing',
            'error'        => null,
            'stage_label'  => 'Retrying: ' . ($stage['title'] ?? $stage['stage_key'] ?? 'stage'),
            'current_step' => $stage['stage_key'] ?? ($job['current_step'] ?? null),
            'retries'      => $jobRetries,
        ]);
    }

    private function isPermanentError(string $message): bool
    {
        $m = strtolower($message);
        $permanent = [
            'gemini is not configured',
            'set gemini_api_key',
            'no powerpoint',
            'no powerpoint, pdf',
            'upload a file',
            'lecture text is missing',
            'cancelled',
            'job not found',
        ];
        foreach ($permanent as $needle) {
            if (str_contains($m, $needle)) {
                return true;
            }
        }
        return false;
    }

    public function snapshot(int $jobId): array
    {
        $job = $this->jobs->find($jobId);
        $stages = $this->stages->listByJob($jobId);
        $allowed = array_column(StageCatalog::all(), 'key');
        $stages = array_values(array_filter(
            $stages,
            fn ($s) => in_array($s['stage_key'] ?? '', $allowed, true)
        ));
        if ($job) {
            $job['stages'] = $stages;
            $job['progress'] = StageCatalog::progressPercent($stages);
        }
        return $job ?? ['id' => $jobId, 'stages' => $stages];
    }

    public function resume(int $jobId): array
    {
        $job = $this->jobs->find($jobId);
        if (!$job) {
            throw new \RuntimeException('Job not found.');
        }
        if ($job['status'] === 'completed') {
            return $this->snapshot($jobId);
        }
        if ($job['status'] === 'cancelled') {
            throw new \RuntimeException('Cancelled jobs cannot be resumed. Start a new generation.');
        }
        $this->stages->resetFailed($jobId);
        $this->jobs->update($jobId, [
            'status' => 'pending',
            'error'  => null,
        ]);
        return $this->step($jobId);
    }

    public function cancel(int $jobId): array
    {
        $this->jobs->cancel($jobId);
        return $this->snapshot($jobId);
    }

    public function publishEngineContent(int $lectureId, string $status = 'published'): void
    {
        foreach ([
            $this->drugs, $this->comparisons, $this->mnemonics,
            $this->viva, $this->cases, $this->revisionSheets, $this->videos,
        ] as $repo) {
            $repo->setStatusForLecture($lectureId, $status === 'approved' ? 'approved' : 'published');
        }
    }

    // ── stage runners ──────────────────────────────────────────

    private function runStage(array $job, array $stage): void
    {
        $jobId = (int) $job['id'];
        $stageId = (int) $stage['id'];
        $key = $stage['stage_key'];

        $this->stages->markRunning($stageId);
        $this->jobs->update($jobId, [
            'stage_label'  => $stage['title'],
            'current_step' => $key,
            'progress'     => StageCatalog::progressPercent($this->stages->listByJob($jobId)),
        ]);

        if ($key === 'extract') {
            $this->runExtract($job, $stage);
            return;
        }

        $text = $this->lectureContext($job);
        match ($key) {
            'detailed_notes'     => $this->runDetailedNotes($job, $stage, $text),
            'summary'            => $this->runSummary($job, $stage, $text),
            'high_yield'         => $this->runHighYield($job, $stage, $text),
            'drug_table'         => $this->runDrugTable($job, $stage, $text),
            'disease_comparison' => $this->runDiseaseComparison($job, $stage, $text),
            'mnemonics'          => $this->runMnemonics($job, $stage, $text),
            'flashcards'         => $this->runFlashcards($job, $stage, $text),
            'viva'               => $this->runViva($job, $stage, $text),
            'mcqs'               => $this->runMcqs($job, $stage, $text),
            'clinical_cases'     => $this->runClinicalCases($job, $stage, $text),
            'revision_sheet'     => $this->runRevisionSheet($job, $stage, $text),
            'video_simulation'   => $this->runVideoSimulation($job, $stage, $text),
            default              => throw new \RuntimeException("Unknown stage: {$key}"),
        };
    }

    private function runExtract(array $job, array $stage): void
    {
        if (!empty($job['source_text']) && mb_strlen((string) $job['source_text']) >= 200) {
            $this->stages->markCompleted((int) $stage['id']);
            $this->syncJobProgress((int) $job['id']);
            return;
        }

        $source = $this->lectures->getLectureSourceFile((int) $job['lecture_id']);
        if (!$source) {
            throw new \RuntimeException(
                'No PowerPoint, PDF, or document found on this lecture. Upload a file or paste lecture text.'
            );
        }

        $text = $this->extractor->extract($source['file_path'], $source['ext']);
        if (mb_strlen($text) < 200) {
            throw new \RuntimeException(
                'Could only extract ' . mb_strlen($text) . ' characters. The file may be scanned/image-based. Paste text manually.'
            );
        }

        $this->jobs->update((int) $job['id'], [
            'source_text'  => $text,
            'source_chars' => mb_strlen($text),
        ]);
        $this->stages->markCompleted((int) $stage['id']);
        $this->syncJobProgress((int) $job['id']);
    }

    private function runDetailedNotes(array $job, array $stage, string $text): void
    {
        $data = $this->callJson($job, $stage, 'detailed_notes', $text);
        $notes = trim((string) ($data['detailed_notes'] ?? ''));
        $this->content->updateByLecture((int) $job['lecture_id'], [
            'detailed_notes' => $notes,
            // Keep legacy revision_notes populated for older student UI.
            'revision_notes' => $notes !== '' ? $notes : null,
        ]);
        $this->completeStage($job, $stage, $data['_usage'] ?? []);
    }

    private function runSummary(array $job, array $stage, string $text): void
    {
        $data = $this->callJson($job, $stage, 'summary', $text);
        $this->content->updateByLecture((int) $job['lecture_id'], [
            'summary' => trim((string) ($data['summary'] ?? '')),
        ]);
        $this->completeStage($job, $stage, $data['_usage'] ?? []);
    }

    private function runHighYield(array $job, array $stage, string $text): void
    {
        $data = $this->callJson($job, $stage, 'high_yield', $text);
        $points = is_array($data['high_yield_points'] ?? null) ? array_values($data['high_yield_points']) : [];
        $this->content->updateByLecture((int) $job['lecture_id'], [
            'high_yield_notes'  => trim((string) ($data['high_yield_notes'] ?? '')),
            'high_yield_points' => array_map('strval', $points),
        ]);
        $this->completeStage($job, $stage, $data['_usage'] ?? []);
    }

    private function runDrugTable(array $job, array $stage, string $text): void
    {
        $data = $this->callJson($job, $stage, 'drug_table', $text);
        $drugs = is_array($data['drugs'] ?? null) ? $data['drugs'] : [];
        $this->drugs->replaceForLecture(
            (int) $job['lecture_id'],
            $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null,
            $drugs
        );
        $this->completeStage($job, $stage, $data['_usage'] ?? []);
    }

    private function runDiseaseComparison(array $job, array $stage, string $text): void
    {
        $data = $this->callJson($job, $stage, 'disease_comparison', $text);
        $items = is_array($data['comparisons'] ?? null) ? $data['comparisons'] : [];
        $this->comparisons->replaceForLecture(
            (int) $job['lecture_id'],
            $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null,
            $items
        );
        $this->completeStage($job, $stage, $data['_usage'] ?? []);
    }

    private function runMnemonics(array $job, array $stage, string $text): void
    {
        $data = $this->callJson($job, $stage, 'mnemonics', $text);
        $items = is_array($data['mnemonics'] ?? null) ? $data['mnemonics'] : [];
        $this->mnemonics->replaceForLecture(
            (int) $job['lecture_id'],
            $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null,
            $items
        );
        $this->completeStage($job, $stage, $data['_usage'] ?? []);
    }

    private function runFlashcards(array $job, array $stage, string $text): void
    {
        $target = max(1, (int) ($stage['target'] ?: 30));
        $done = (int) ($stage['done'] ?? 0);
        if ($done === 0 && $this->flashcards->countByLecture((int) $job['lecture_id']) > 0) {
            // Fresh engine run: clear prior AI drafts for this lecture once.
            $this->flashcards->deleteAllByLecture((int) $job['lecture_id']);
        }
        $remaining = $target - $done;
        if ($remaining <= 0) {
            $this->completeStage($job, $stage);
            return;
        }

        $batch = min(self::FLASHCARD_BATCH, $remaining);
        $existing = $this->flashcards->existingFronts((int) $job['lecture_id']);
        $context = $this->compactContext($job, $text);
        $data = $this->callJson($job, $stage, 'flashcards', $context, [
            'count' => $batch,
            'avoid' => $existing,
        ]);
        $cards = is_array($data['flashcards'] ?? null) ? $data['flashcards'] : [];
        $normalized = [];
        foreach ($cards as $c) {
            $front = trim((string) ($c['front'] ?? ''));
            $back = trim((string) ($c['back'] ?? ''));
            if ($front === '' || $back === '') {
                continue;
            }
            $normalized[] = [
                'front' => $front,
                'back' => $back,
                'topic' => $c['topic'] ?? null,
                'difficulty' => $this->difficulty($c['difficulty'] ?? 'moderate'),
            ];
        }
        $inserted = $this->flashcards->insertMany(
            (int) $job['lecture_id'],
            $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null,
            $normalized
        );
        $newDone = min($target, $done + max($inserted, 1));
        $usage = $data['_usage'] ?? [];
        if ($newDone >= $target) {
            $this->stages->updateProgress((int) $stage['id'], $newDone, 100, $usage);
            $this->completeStage($job, $stage, $usage);
        } else {
            $pct = (int) round(100 * $newDone / $target);
            $this->stages->updateProgress((int) $stage['id'], $newDone, $pct, $usage);
            $this->accumulateJobUsage((int) $job['id'], $usage);
            $this->syncJobProgress((int) $job['id']);
        }
    }

    private function runViva(array $job, array $stage, string $text): void
    {
        $target = max(1, (int) ($stage['target'] ?: 10));
        $done = (int) ($stage['done'] ?? 0);
        if ($done === 0) {
            $this->viva->deleteAiByLecture((int) $job['lecture_id']);
        }
        $remaining = $target - $done;
        if ($remaining <= 0) {
            $this->completeStage($job, $stage);
            return;
        }

        $batch = min(self::VIVA_BATCH, $remaining);
        $data = $this->callJson($job, $stage, 'viva', $this->compactContext($job, $text), [
            'count' => $batch,
            'avoid' => $this->viva->existingQuestions((int) $job['lecture_id']),
        ]);
        $items = is_array($data['viva_questions'] ?? null) ? $data['viva_questions'] : [];
        $inserted = $this->viva->insertMany(
            (int) $job['lecture_id'],
            $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null,
            $items
        );
        $newDone = min($target, $done + max($inserted, 1));
        $usage = $data['_usage'] ?? [];
        if ($newDone >= $target) {
            $this->stages->updateProgress((int) $stage['id'], $newDone, 100, $usage);
            $this->completeStage($job, $stage, $usage);
        } else {
            $this->stages->updateProgress((int) $stage['id'], $newDone, (int) round(100 * $newDone / $target), $usage);
            $this->accumulateJobUsage((int) $job['id'], $usage);
            $this->syncJobProgress((int) $job['id']);
        }
    }

    private function runMcqs(array $job, array $stage, string $text): void
    {
        $target = max(1, (int) ($stage['target'] ?: 30));
        $done = (int) ($stage['done'] ?? 0);
        if ($done === 0) {
            $this->mcqs->deleteAllByLecture((int) $job['lecture_id']);
        }
        $remaining = $target - $done;
        if ($remaining <= 0) {
            $this->completeStage($job, $stage);
            return;
        }

        $batch = min(self::MCQ_BATCH, $remaining);
        $data = $this->callJson($job, $stage, 'mcqs', $this->compactContext($job, $text), [
            'count' => $batch,
            'avoid' => $this->mcqs->existingQuestions((int) $job['lecture_id']),
        ]);
        $questions = is_array($data['mcqs'] ?? null) ? $data['mcqs'] : [];
        $normalized = [];
        foreach ($questions as $q) {
            $question = trim((string) ($q['question'] ?? ''));
            $options = $q['options'] ?? [];
            $correct = strtoupper(trim((string) ($q['correct_option'] ?? '')));
            if ($question === '' || !is_array($options) || !in_array($correct, ['A', 'B', 'C', 'D', 'E'], true)) {
                continue;
            }
            $normalized[] = [
                'question' => $question,
                'option_a' => trim((string) ($options['A'] ?? '')),
                'option_b' => trim((string) ($options['B'] ?? '')),
                'option_c' => trim((string) ($options['C'] ?? '')),
                'option_d' => trim((string) ($options['D'] ?? '')),
                'option_e' => trim((string) ($options['E'] ?? '')),
                'correct_option' => $correct,
                'explanation' => trim((string) ($q['explanation'] ?? '')),
                'option_explanations' => is_array($q['option_explanations'] ?? null) ? $q['option_explanations'] : null,
                'topic' => trim((string) ($q['topic'] ?? '')),
                'difficulty' => $this->difficulty($q['difficulty'] ?? 'moderate'),
            ];
        }
        $inserted = $this->mcqs->insertMany(
            (int) $job['lecture_id'],
            $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null,
            $normalized
        );
        $newDone = min($target, $done + max($inserted, 1));
        $usage = $data['_usage'] ?? [];
        if ($newDone >= $target) {
            $this->stages->updateProgress((int) $stage['id'], $newDone, 100, $usage);
            $this->completeStage($job, $stage, $usage);
        } else {
            $this->stages->updateProgress((int) $stage['id'], $newDone, (int) round(100 * $newDone / $target), $usage);
            $this->accumulateJobUsage((int) $job['id'], $usage);
            $this->syncJobProgress((int) $job['id']);
        }
    }

    private function runClinicalCases(array $job, array $stage, string $text): void
    {
        $target = max(1, (int) ($stage['target'] ?: 5));
        $data = $this->callJson($job, $stage, 'clinical_cases', $this->compactContext($job, $text), [
            'count' => $target,
        ]);
        $cases = is_array($data['cases'] ?? null) ? $data['cases'] : [];
        $this->cases->replaceForLecture(
            (int) $job['lecture_id'],
            $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null,
            array_slice($cases, 0, $target)
        );
        $this->completeStage($job, $stage, $data['_usage'] ?? []);
    }

    private function runRevisionSheet(array $job, array $stage, string $text): void
    {
        $data = $this->callJson($job, $stage, 'revision_sheet', $this->compactContext($job, $text));
        $this->revisionSheets->upsert(
            (int) $job['lecture_id'],
            $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null,
            trim((string) ($data['revision_sheet'] ?? ''))
        );
        $this->completeStage($job, $stage, $data['_usage'] ?? []);
    }

    private function runVideoSimulation(array $job, array $stage, string $text): void
    {
        $data = $this->callJson($job, $stage, 'video_simulation', $this->compactContext($job, $text));
        $usage = $data['_usage'] ?? $this->lastUsage;
        unset($data['_usage']);
        $this->videos->upsert(
            (int) $job['lecture_id'],
            $job['course_id'] ? (int) $job['course_id'] : null,
            $job['requested_by'] ? (int) $job['requested_by'] : null,
            $data
        );
        $this->completeStage($job, $stage, $usage);
    }

    private array $lastUsage = [];

    // ── Gemini call helpers ────────────────────────────────────

    private function callJson(array $job, array $stage, string $stageKey, string $text, array $options = []): array
    {
        $built = $this->prompts->build($stageKey, $this->trim($text), $options);
        $meta = $this->gemini->completeWithMeta($built['system'], $built['user'], [
            'json' => true,
            'temperature' => 0.2,
        ]);
        $this->lastUsage = [
            'prompt_tokens'     => (int) ($meta['prompt_tokens'] ?? 0),
            'completion_tokens' => (int) ($meta['completion_tokens'] ?? 0),
            'total_tokens'      => (int) ($meta['total_tokens'] ?? 0),
            'latency_ms'        => (int) ($meta['latency_ms'] ?? 0),
            'retries'           => (int) ($meta['retries'] ?? 0),
            'model'             => (string) ($meta['model'] ?? $this->gemini->model()),
        ];
        $cost = $this->gemini->estimateCost(
            $this->lastUsage['prompt_tokens'],
            $this->lastUsage['completion_tokens']
        );

        try {
            $decoded = $this->decodeJson($meta['text'] ?? '');
        } catch (\RuntimeException $e) {
            // One repair pass: ask Gemini to return only valid JSON.
            $repairUser = "The previous response was not valid JSON. Convert the content below into STRICT valid JSON only. "
                        . "No markdown, no commentary.\n\nCONTENT:\n" . mb_substr((string) ($meta['text'] ?? ''), 0, 12000);
            $repair = $this->gemini->completeWithMeta(
                'You fix malformed JSON. Reply with a single valid JSON object or array only.',
                $repairUser,
                ['json' => true, 'temperature' => 0]
            );
            $this->lastUsage['prompt_tokens'] += (int) ($repair['prompt_tokens'] ?? 0);
            $this->lastUsage['completion_tokens'] += (int) ($repair['completion_tokens'] ?? 0);
            $this->lastUsage['total_tokens'] += (int) ($repair['total_tokens'] ?? 0);
            $this->lastUsage['latency_ms'] += (int) ($repair['latency_ms'] ?? 0);
            $this->lastUsage['retries'] += 1 + (int) ($repair['retries'] ?? 0);
            $cost = $this->gemini->estimateCost(
                $this->lastUsage['prompt_tokens'],
                $this->lastUsage['completion_tokens']
            );
            $decoded = $this->decodeJson($repair['text'] ?? '');
        }

        $this->logs->log([
            'job_id'            => (int) $job['id'],
            'stage_id'          => (int) $stage['id'],
            'stage_key'         => $stageKey,
            'model'             => $this->lastUsage['model'],
            'status'            => 'success',
            'prompt_tokens'     => $this->lastUsage['prompt_tokens'],
            'completion_tokens' => $this->lastUsage['completion_tokens'],
            'total_tokens'      => $this->lastUsage['total_tokens'],
            'latency_ms'        => $this->lastUsage['latency_ms'],
            'retries'           => $this->lastUsage['retries'],
            'estimated_cost'    => $cost,
        ]);

        $decoded['_usage'] = $this->lastUsage;
        $decoded['_usage']['estimated_cost'] = $cost;
        return $decoded;
    }

    private function completeStage(array $job, array $stage, array $usage = []): void
    {
        if (!$usage && $this->lastUsage) {
            $usage = $this->lastUsage;
        }
        $this->stages->markCompleted((int) $stage['id'], $usage);
        $this->accumulateJobUsage((int) $job['id'], $usage);
        $this->syncJobProgress((int) $job['id']);

        // Auto-finish if no more stages.
        if (!$this->stages->nextActionable((int) $job['id'])) {
            $this->finish($this->jobs->find((int) $job['id']) ?? $job);
        }
    }

    private function accumulateJobUsage(int $jobId, array $usage): void
    {
        if (!$usage) {
            return;
        }
        $job = $this->jobs->find($jobId);
        if (!$job) {
            return;
        }
        $prompt = (int) ($job['prompt_tokens'] ?? 0) + (int) ($usage['prompt_tokens'] ?? 0);
        $completion = (int) ($job['completion_tokens'] ?? 0) + (int) ($usage['completion_tokens'] ?? 0);
        $total = (int) ($job['total_tokens'] ?? 0) + (int) ($usage['total_tokens'] ?? 0);
        $retries = (int) ($job['retries'] ?? 0) + (int) ($usage['retries'] ?? 0);
        $cost = (float) ($job['estimated_cost'] ?? 0) + (float) ($usage['estimated_cost'] ?? 0);
        if ($cost <= 0 && ($prompt + $completion) > 0) {
            $cost = $this->gemini->estimateCost($prompt, $completion);
        }
        $fields = [
            'prompt_tokens'     => $prompt,
            'completion_tokens' => $completion,
            'total_tokens'      => $total,
            'retries'           => $retries,
            'estimated_cost'    => $cost,
        ];
        if ($this->jobs->find($jobId) && isset($job['started_at']) && $job['started_at']) {
            $fields['generation_seconds'] = max(0, time() - strtotime((string) $job['started_at']));
        }
        $this->jobs->update($jobId, $fields);
    }

    private function syncJobProgress(int $jobId): void
    {
        $stages = $this->stages->listByJob($jobId);
        $pct = StageCatalog::progressPercent($stages);
        $running = null;
        foreach ($stages as $s) {
            if (($s['status'] ?? '') === 'running') {
                $running = $s;
                break;
            }
        }
        if (!$running) {
            foreach ($stages as $s) {
                if (($s['status'] ?? '') === 'pending') {
                    $running = $s;
                    break;
                }
            }
        }
        $this->jobs->update($jobId, [
            'progress'     => $pct,
            'stage_label'  => $running['title'] ?? 'Done',
            'current_step' => $running['stage_key'] ?? 'done',
        ]);
    }

    private function finish(array $job): void
    {
        $started = !empty($job['started_at']) ? strtotime((string) $job['started_at']) : null;
        $this->jobs->update((int) $job['id'], [
            'current_step'        => 'done',
            'status'              => 'completed',
            'progress'            => 100,
            'stage_label'         => 'Completed',
            'generation_seconds'  => $started ? max(0, time() - $started) : (int) ($job['generation_seconds'] ?? 0),
            'completed_at'        => date('Y-m-d H:i:s'),
            'error'               => null,
        ]);

        if (!empty($job['requested_by'])) {
            $lecture = $this->lectures->getLecture((int) $job['lecture_id']);
            $title = $lecture['title'] ?? 'lecture';
            $this->notifications->notify(
                (int) $job['requested_by'],
                'ai_generation',
                'AI study pack ready for review',
                "Full AI generation for \"{$title}\" is complete. Review and publish when ready.",
                ['lecture_id' => (int) $job['lecture_id']],
                false
            );
        }
    }

    private function lectureContext(array $job): string
    {
        $text = (string) ($job['source_text'] ?? '');
        if (mb_strlen($text) < 200) {
            throw new \RuntimeException('Lecture text is missing. Re-run extraction or paste text.');
        }
        return $text;
    }

    /** Prefer generated notes/summary for batched item stages to stay within TPM. */
    private function compactContext(array $job, string $fallback): string
    {
        $content = $this->content->findByLecture((int) $job['lecture_id']);
        $parts = [];
        if ($content) {
            foreach (['summary', 'detailed_notes', 'high_yield_notes', 'revision_notes'] as $f) {
                if (!empty($content[$f])) {
                    $parts[] = strtoupper(str_replace('_', ' ', $f)) . ":\n" . trim((string) $content[$f]);
                }
            }
            if (!empty($content['high_yield_points']) && is_array($content['high_yield_points'])) {
                $parts[] = "HIGH YIELD POINTS:\n- " . implode("\n- ", $content['high_yield_points']);
            }
        }
        $combined = trim(implode("\n\n", $parts));
        if (mb_strlen($combined) >= 400) {
            return $this->trim($combined);
        }
        return $this->trim($fallback);
    }

    private function trim(string $text): string
    {
        if (mb_strlen($text) <= $this->maxInputChars) {
            return $text;
        }
        $slice = (int) floor($this->maxInputChars / 3);
        $len = mb_strlen($text);
        return mb_substr($text, 0, $slice)
            . "\n...\n"
            . mb_substr($text, (int) ($len / 2 - $slice / 2), $slice)
            . "\n...\n"
            . mb_substr($text, $len - $slice, $slice);
    }

    private function decodeJson(string $response): array
    {
        $s = trim($response);
        if ($s === '') {
            throw new \RuntimeException('Gemini returned malformed JSON.');
        }

        // Strip BOM / code fences / leading commentary.
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = preg_replace('/^```(?:json|JSON)?\s*/', '', $s) ?? $s;
        $s = preg_replace('/\s*```$/', '', $s) ?? $s;
        $s = trim($s);

        $candidates = [$s];
        $extracted = $this->extractJsonBlob($s);
        if ($extracted !== null && $extracted !== $s) {
            $candidates[] = $extracted;
        }

        foreach ($candidates as $candidate) {
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            $repaired = $this->repairJson($candidate);
            if ($repaired !== null) {
                $decoded = json_decode($repaired, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        throw new \RuntimeException('Gemini returned malformed JSON.');
    }

    /** Find the outermost JSON object/array in mixed text. */
    private function extractJsonBlob(string $s): ?string
    {
        $startObj = strpos($s, '{');
        $startArr = strpos($s, '[');
        if ($startObj === false && $startArr === false) {
            return null;
        }
        if ($startObj === false) {
            $start = $startArr;
            $open = '[';
            $close = ']';
        } elseif ($startArr === false) {
            $start = $startObj;
            $open = '{';
            $close = '}';
        } else {
            $start = min($startObj, $startArr);
            $open = $s[$start];
            $close = $open === '{' ? '}' : ']';
        }

        $depth = 0;
        $inString = false;
        $escape = false;
        $len = strlen($s);
        for ($i = $start; $i < $len; $i++) {
            $ch = $s[$i];
            if ($inString) {
                if ($escape) {
                    $escape = false;
                } elseif ($ch === '\\') {
                    $escape = true;
                } elseif ($ch === '"') {
                    $inString = false;
                }
                continue;
            }
            if ($ch === '"') {
                $inString = true;
                continue;
            }
            if ($ch === $open) {
                $depth++;
            } elseif ($ch === $close) {
                $depth--;
                if ($depth === 0) {
                    return substr($s, $start, $i - $start + 1);
                }
            }
        }
        return null;
    }

    /** Best-effort fixes for common model JSON mistakes. */
    private function repairJson(string $s): ?string
    {
        $s = trim($s);
        // Smart quotes → plain quotes
        $s = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"], ['"', '"', "'", "'"], $s);
        // Trailing commas before } or ]
        $s = preg_replace('/,\s*([}\]])/', '$1', $s) ?? $s;
        // Unescaped control chars inside strings are hard; strip bare newlines outside strings only lightly.
        if (json_decode($s, true) !== null || json_last_error() === JSON_ERROR_NONE) {
            return $s;
        }
        return $s;
    }

    private function difficulty(mixed $v): string
    {
        $v = strtolower(trim((string) $v));
        return in_array($v, ['easy', 'moderate', 'difficult'], true) ? $v : 'moderate';
    }
}
