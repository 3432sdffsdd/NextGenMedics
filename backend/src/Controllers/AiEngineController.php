<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AiContentRepository;
use App\Repositories\AiJobRepository;
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
use App\Services\AiEngineService;
use App\Services\CourseService;

/**
 * Teacher-facing Gemini AI Generation Engine (staged pipeline + review pack).
 */
class AiEngineController extends BaseController
{
    public function __construct(
        private AiEngineService $engine,
        private AiJobRepository $jobs,
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
        private CourseService $courseService
    ) {}

    public function status(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) {
            return;
        }
        $config = require __DIR__ . '/../../config/config.php';
        $g = $config['gemini'] ?? [];
        $ready = $this->engine->isReady();
        Response::success([
            'ready'          => $ready,
            'provider'       => $this->engine->providerName(),
            'model'          => $this->engine->model(),
            'key_configured' => trim((string) ($g['api_key'] ?? '')) !== '',
            'hint'           => $ready
                ? null
                : 'Set GEMINI_API_KEY in backend/.env (get a key at https://aistudio.google.com/apikey).',
        ]);
    }

    public function generate(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        if (!$this->engine->isReady()) {
            Response::error('Gemini is not configured. Set GEMINI_API_KEY in backend/.env.', 503);
            return;
        }
        if ($this->jobs->hasActive($lectureId)) {
            Response::error('A generation job is already running for this lecture.', 409);
            return;
        }

        $text = isset($request->body()['text']) ? trim((string) $request->body()['text']) : null;
        $jobId = $this->engine->enqueue($lectureId, $courseId, $request->userId(), $text ?: null);
        $snap = $this->engine->snapshot($jobId);
        unset($snap['source_text']);
        Response::success(['job' => $snap], 'AI engine generation started', 201);
    }

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
        @set_time_limit(180);
        $snap = $this->engine->step($jobId);
        unset($snap['source_text']);
        Response::success(['job' => $snap]);
    }

    public function jobStatus(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $job = $this->jobs->latestEngineForLecture($lectureId);
        $snap = $job ? $this->engine->snapshot((int) $job['id']) : null;
        if ($snap) {
            unset($snap['source_text']);
        }
        Response::success([
            'job' => $snap,
        ]);
    }

    public function resume(Request $request): void
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
        try {
            $snap = $this->engine->resume($jobId);
            unset($snap['source_text']);
            Response::success(['job' => $snap], 'Resumed from last successful stage');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function cancel(Request $request): void
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
        $snap = $this->engine->cancel($jobId);
        unset($snap['source_text']);
        Response::success(['job' => $snap], 'Job cancelled');
    }

    /** Full review pack for all engine content types. */
    public function review(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        Response::success($this->pack($lectureId, false));
    }

    public function approve(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $this->content->setStatus($lectureId, 'approved', $request->userId());
        $this->flashcards->approveAllByLecture($lectureId);
        $this->mcqs->approveAllByLecture($lectureId);
        $this->engine->publishEngineContent($lectureId, 'approved');
        Response::success(null, 'Engine content approved');
    }

    public function publish(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        if (!$this->authorizeLecture($request, $lectureId, $courseId)) {
            return;
        }
        $this->content->setStatus($lectureId, 'published', $request->userId());
        $this->flashcards->approveAllByLecture($lectureId);
        $this->mcqs->publishAllByLecture($lectureId);
        $this->engine->publishEngineContent($lectureId, 'published');
        Response::success(null, 'Engine content published to students');
    }

    private function pack(int $lectureId, bool $publishedOnly): array
    {
        $status = $publishedOnly ? 'published' : null;
        $approved = $publishedOnly ? 'approved' : null;
        return [
            'lecture'             => $this->lectures->getLecture($lectureId),
            'content'             => $this->content->findByLecture($lectureId),
            'drugs'               => $this->drugs->listByLecture($lectureId, $status),
            'disease_comparisons' => $this->comparisons->listByLecture($lectureId, $status),
            'mnemonics'           => $this->mnemonics->listByLecture($lectureId, $status),
            'flashcards'          => $this->flashcards->listByLecture($lectureId, $approved),
            'viva_questions'      => $this->viva->listByLecture($lectureId, $status),
            'mcqs'                => $this->mcqs->listByLecture($lectureId, $publishedOnly ? 'published' : null, true),
            'clinical_cases'      => $this->cases->listByLecture($lectureId, $status),
            'revision_sheet'      => $this->revisionSheets->findByLecture($lectureId),
            'video_simulation'    => $this->videos->findByLecture($lectureId),
            'job'                 => ($j = $this->jobs->latestEngineForLecture($lectureId))
                ? $this->engine->snapshot((int) $j['id'])
                : null,
        ];
    }

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
}
