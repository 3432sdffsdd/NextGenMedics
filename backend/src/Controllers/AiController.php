<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AiContentRepository;
use App\Repositories\AiJobRepository;
use App\Repositories\ChallengeRepository;
use App\Repositories\ContentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\McqRepository;
use App\Services\AiGenerationService;
use App\Services\CourseService;
use App\Services\ManualStudyImportService;
use App\Services\NotificationService;

/**
 * AI Learning Assistant — teacher-facing generation & review workflow.
 * All generated content stays as draft until a teacher approves & publishes.
 */
class AiController extends BaseController
{
    public function __construct(
        private AiGenerationService $generator,
        private AiJobRepository $jobs,
        private AiContentRepository $content,
        private FlashcardRepository $flashcards,
        private McqRepository $mcqs,
        private ChallengeRepository $challenges,
        private ContentRepository $lectures,
        private CourseRepository $courses,
        private CourseService $courseService,
        private NotificationService $notifications,
        private ManualStudyImportService $manualImport
    ) {}

    // ── Generation ─────────────────────────────────────────────

    /** Check whether study resource generation is configured (API key set). */
    public function status(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $config = require __DIR__ . '/../../config/config.php';
        $ai = $config['ai'] ?? [];
        $ready = $this->generator->isReady();

        Response::success([
            'ready'          => $ready,
            'enabled'        => (bool) ($ai['enabled'] ?? true),
            'key_configured' => trim((string) ($ai['api_key'] ?? '')) !== '',
            'provider'       => $ai['provider'] ?? 'openai',
            'model'          => $ai['model'] ?? '',
            'base_url'       => $ai['base_url'] ?? '',
            'hint'           => $ready ? null : self::setupHint($ai),
        ]);
    }

    public function generate(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }

        if (!$this->generator->isReady()) {
            $config = require __DIR__ . '/../../config/config.php';
            Response::error(self::setupHint($config['ai'] ?? []), 503);
            return;
        }

        if ($this->jobs->hasActive($lectureId)) {
            Response::error('A generation job is already running for this lecture.', 409);
            return;
        }

        $body = $request->body();
        // Summary and notes always run; only flashcard/MCQ counts come from the form.
        $options = [
            'summary'    => true,
            'notes'      => true,
            'flashcards' => max(0, min(100, (int) ($body['flashcards'] ?? 30))),
            'mcqs'       => max(0, min(200, (int) ($body['mcqs'] ?? 30))),
        ];
        $text = isset($body['text']) ? trim((string) $body['text']) : null;

        $jobId = $this->generator->enqueue($lectureId, $courseId, $request->userId(), $options, $text ?: null);

        Response::success(['job' => $this->jobs->find($jobId)], 'Generation started', 201);
    }

    /** Process the next generation step (frontend polls this until done/failed). */
    public function process(Request $request): void
    {
        $jobId = (int) $request->param('jobId');
        $job = $this->jobs->find($jobId);
        if (!$job) {
            Response::error('Job not found', 404);
            return;
        }
        if (!$this->authorizeLecture($request, (int) $job['lecture_id'], $courseId)) {
            return;
        }

        // Allow resuming a failed job (e.g. after TPM error on flashcards step).
        if ($job['status'] === 'failed') {
            $this->jobs->update($jobId, ['status' => 'pending', 'error' => null]);
            $job = $this->jobs->find($jobId);
        }

        @set_time_limit(180);
        $job = $this->generator->step($jobId);
        // Don't leak the full extracted text on every poll.
        unset($job['source_text']);
        Response::success(['job' => $job]);
    }

    public function jobStatus(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $job = $this->jobs->latestForLecture($lectureId);
        if ($job) {
            unset($job['source_text']);
        }
        Response::success(['job' => $job]);
    }

    // ── Review ─────────────────────────────────────────────────

    public function review(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }

        Response::success([
            'lecture'    => $this->lectures->getLecture($lectureId),
            'content'    => $this->content->findByLecture($lectureId),
            'flashcards' => $this->flashcards->listByLecture($lectureId),
            'mcqs'       => $this->mcqs->listByLecture($lectureId, null, true),
            'challenge'  => $this->challenges->findByLecture($lectureId),
            'counts'     => [
                'flashcards' => $this->flashcards->countByLecture($lectureId),
                'mcqs'       => $this->mcqs->countByLecture($lectureId),
            ],
        ]);
    }

    public function updateContent(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }

        $body = $request->body();
        $allowed = ['summary', 'revision_notes', 'high_yield_points', 'clinical_pearls',
                    'common_mistakes', 'key_definitions', 'memory_tricks', 'key_takeaways'];
        $fields = array_intersect_key($body, array_flip($allowed));

        $this->content->ensure($lectureId, $courseId, $request->userId());
        $this->content->updateByLecture($lectureId, $fields);
        Response::success($this->content->findByLecture($lectureId), 'Content updated');
    }

    // ── Flashcards ─────────────────────────────────────────────

    public function addFlashcard(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $data = $this->validate($request, ['front' => 'required|min:2', 'back' => 'required|min:1']);
        if (!$data) return;

        $this->flashcards->insertMany($lectureId, $courseId, $request->userId(), [[
            'front'      => $data['front'],
            'back'       => $data['back'],
            'topic'      => $request->input('topic'),
            'difficulty' => $request->input('difficulty', 'moderate'),
        ]], 'manual');
        Response::success(null, 'Flashcard added', 201);
    }

    public function updateFlashcard(Request $request): void
    {
        $id = (int) $request->param('id');
        $lectureId = $this->flashcards->lectureId($id);
        if (!$lectureId || !$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $this->flashcards->update($id, $request->body());
        Response::success(null, 'Flashcard updated');
    }

    public function deleteFlashcard(Request $request): void
    {
        $id = (int) $request->param('id');
        $lectureId = $this->flashcards->lectureId($id);
        if (!$lectureId || !$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $this->flashcards->delete($id);
        Response::success(null, 'Flashcard deleted');
    }

    // ── MCQs ───────────────────────────────────────────────────

    public function addMcq(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $data = $this->validate($request, [
            'question' => 'required|min:5',
            'option_a' => 'required|min:1',
            'option_b' => 'required|min:1',
        ]);
        if (!$data) return;

        $correct = strtoupper((string) $request->input('correct_option', 'A'));
        if (!in_array($correct, ['A', 'B', 'C', 'D', 'E'], true)) {
            $correct = 'A';
        }

        $id = $this->mcqs->insertOne($lectureId, $courseId, $request->userId(), [
            'question'       => $data['question'],
            'option_a'       => $data['option_a'],
            'option_b'       => $data['option_b'],
            'option_c'       => $request->input('option_c'),
            'option_d'       => $request->input('option_d'),
            'option_e'       => $request->input('option_e'),
            'correct_option' => $correct,
            'explanation'    => $request->input('explanation'),
            'topic'          => $request->input('topic'),
            'difficulty'     => $request->input('difficulty', 'moderate'),
        ], 'manual');
        Response::success(['id' => $id], 'MCQ added', 201);
    }

    public function updateMcq(Request $request): void
    {
        $id = (int) $request->param('id');
        $lectureId = $this->mcqs->lectureId($id);
        if (!$lectureId || !$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $this->mcqs->update($id, $request->body());
        Response::success(null, 'MCQ updated');
    }

    public function deleteMcq(Request $request): void
    {
        $id = (int) $request->param('id');
        $lectureId = $this->mcqs->lectureId($id);
        if (!$lectureId || !$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $this->mcqs->delete($id);
        Response::success(null, 'MCQ deleted');
    }

    // ── Manual file import (no AI) ─────────────────────────────

    /** Upload Word file → summary + notes + MCQs (replaces existing). */
    public function importWordPack(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }

        $file = $request->file('file');
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Response::error('Please upload a Word (.doc or .docx) file', 422);
            return;
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, ['doc', 'docx', 'txt'], true)) {
            Response::error('Upload a .docx, .doc, or .txt file', 422);
            return;
        }

        try {
            $result = $this->manualImport->importWordPack(
                $lectureId,
                $courseId,
                $request->userId(),
                $file['tmp_name'],
                $file['name']
            );
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 422);
            return;
        }

        $this->notifyStudyPublished($lectureId, $courseId);

        Response::success($result, sprintf(
            'Imported and published to students: summary, notes, and %d MCQ(s).',
            $result['mcqs_imported']
        ));
    }

    /** Upload Excel file → flashcards (replaces existing). Topic = file name. */
    public function importFlashcards(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }

        $file = $request->file('file');
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Response::error('Please upload an Excel file (.xlsx or .xls)', 422);
            return;
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            Response::error('Upload an .xlsx or .xls file with column A = Front and column B = Back.', 422);
            return;
        }

        try {
            $result = $this->manualImport->importFlashcards(
                $lectureId,
                $courseId,
                $request->userId(),
                $file['tmp_name'],
                $file['name']
            );
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 422);
            return;
        }

        $this->notifyStudyPublished($lectureId, $courseId);

        Response::success($result, sprintf(
            '%d flashcard(s) imported under topic "%s" and published to students.',
            $result['flashcards_imported'],
            $result['topic']
        ));
    }

    /** Upload quiz file → MCQs only (replaces existing MCQs). */
    public function importMcqs(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }

        $file = $request->file('file');
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Response::error('Please upload a quiz file (.docx, .doc, .txt, or .html)', 422);
            return;
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, ['doc', 'docx', 'txt', 'html', 'htm'], true)) {
            Response::error('Upload a .docx, .doc, .txt, or .html quiz file', 422);
            return;
        }

        try {
            $result = $this->manualImport->importMcqs(
                $lectureId,
                $courseId,
                $request->userId(),
                $file['tmp_name'],
                $file['name']
            );
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 422);
            return;
        }

        $this->notifyStudyPublished($lectureId, $courseId);

        Response::success($result, sprintf(
            '%d MCQ(s) imported and published to students.',
            $result['mcqs_imported']
        ));
    }

    // ── Approve / Publish ──────────────────────────────────────

    public function approve(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $this->content->setStatus($lectureId, 'approved', $request->userId());
        $this->flashcards->approveAllByLecture($lectureId);
        $this->mcqs->approveAllByLecture($lectureId);
        Response::success(null, 'Content approved');
    }

    public function publish(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $this->manualImport->publishForStudents($lectureId);
        $this->notifyStudyPublished($lectureId, $courseId);
        Response::success(null, 'Content published to students');
    }

    // ── Daily challenge config ─────────────────────────────────

    public function saveChallenge(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $this->challenges->upsert($lectureId, $courseId, $request->userId(), [
            'enabled'      => (int) (bool) $request->input('enabled', false),
            'mcqs_per_day' => (int) $request->input('mcqs_per_day', 10),
            'start_date'   => $request->input('start_date') ?: date('Y-m-d'),
        ]);
        Response::success($this->challenges->findByLecture($lectureId), 'Daily challenge updated');
    }

    // ── helpers ────────────────────────────────────────────────

    /**
     * Ensure the current user is an admin, or a teacher who can access the
     * lecture's course. Sets $courseId by reference for downstream use.
     */
    private function authorizeLecture(Request $request, int $lectureId, ?int &$courseId): bool
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) {
            return false;
        }
        $courseId = $this->lectures->getLectureCourseId($lectureId);
        if (!$courseId) {
            Response::error('Lecture not found', 404);
            return false;
        }
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return false;
        }
        return true;
    }

    private function notifyStudyPublished(int $lectureId, ?int $courseId): void
    {
        if (!$courseId) {
            return;
        }
        $lecture = $this->lectures->getLecture($lectureId);
        $studentIds = array_column($this->courses->getEnrolledStudents($courseId), 'id');
        if (!$studentIds) {
            return;
        }
        $this->notifications->notifyMany(
            $studentIds,
            'ai_content_published',
            'New revision material available',
            'New notes, flashcards and MCQs are ready for "' . ($lecture['title'] ?? 'a lecture') . '".',
            ['lecture_id' => $lectureId, 'course_id' => $courseId]
        );
    }

    private static function setupHint(array $ai): string
    {
        if (!($ai['enabled'] ?? true)) {
            return 'Study resource generation is disabled. Set AI_ENABLED=true in backend/.env';
        }
        $base = $ai['base_url'] ?? '';
        $isLocal = str_contains($base, '127.0.0.1') || str_contains($base, 'localhost');
        if ($isLocal) {
            return 'Local AI endpoint not reachable. Start Ollama/LM Studio or set a cloud AI_API_KEY in backend/.env';
        }
        return 'Add your AI API key in backend/.env (AI_API_KEY=sk-...). See backend/docs/AI_SETUP.md. OpenRouter and Groq offer low-cost keys.';
    }
}
