<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AttemptRepository;
use App\Repositories\CourseRepository;
use App\Repositories\McqRepository;
use App\Repositories\MistakeRepository;
use App\Repositories\RevisionSessionRepository;
use App\Repositories\StudyPlanRepository;
use App\Services\PremiumStudyService;

class PremiumStudyController extends BaseController
{
    public function __construct(
        private PremiumStudyService $premium,
        private CourseRepository $courses,
        private McqRepository $mcqs,
        private MistakeRepository $mistakes,
        private StudyPlanRepository $plans,
        private RevisionSessionRepository $revisionSessions,
        private AttemptRepository $attempts
    ) {}

    public function dashboard(Request $request): void
    {
        Response::success($this->premium->dashboardSummary($request->userId()));
    }

    public function dailyChallenge(Request $request): void
    {
        Response::success($this->premium->buildDailyChallenge($request->userId()));
    }

    public function dailyHistory(Request $request): void
    {
        $studentId = $request->userId();
        $stmt = $this->attempts;
        Response::success(array_values(array_filter(
            $stmt->recentByStudent($studentId, 30),
            fn($a) => ($a['source'] ?? '') === 'daily'
        )));
    }

    public function weakAreas(Request $request): void
    {
        Response::success($this->premium->dashboardSummary($request->userId())['weak_areas']);
    }

    public function getStudyPlan(Request $request): void
    {
        $studentId = $request->userId();
        $plan = $this->plans->getPlan($studentId);
        if (!$plan) {
            Response::success(['plan' => null, 'tasks' => []]);
            return;
        }
        Response::success([
            'plan'  => $plan,
            'tasks' => $this->plans->tasksRange((int) $plan['id'], date('Y-m-d'), date('Y-m-d', strtotime('+13 days'))),
        ]);
    }

    public function saveStudyPlan(Request $request): void
    {
        $data = $this->validate($request, [
            'exam_date'     => 'required',
            'hours_per_day' => 'required',
        ]);
        if (!$data) {
            return;
        }
        $hours = max(0.5, min(16, (float) $data['hours_per_day']));
        $exam = date('Y-m-d', strtotime($data['exam_date']));
        if ($exam < date('Y-m-d')) {
            Response::error('Exam date must be in the future', 422);
            return;
        }
        Response::success(
            $this->premium->saveStudyPlan($request->userId(), $exam, $hours),
            'Study plan updated'
        );
    }

    public function completeTask(Request $request): void
    {
        $studentId = $request->userId();
        $plan = $this->plans->getPlan($studentId);
        if (!$plan) {
            Response::error('No study plan found', 404);
            return;
        }
        $taskId = (int) $request->param('id');
        $status = in_array($request->input('status'), ['completed', 'skipped', 'pending'], true)
            ? $request->input('status') : 'completed';
        if (!$this->plans->updateTaskStatus($taskId, (int) $plan['id'], $status)) {
            Response::error('Task not found', 404);
            return;
        }
        Response::success(null, 'Task updated');
    }

    public function questionBankFilters(Request $request): void
    {
        $courseIds = array_column($this->courses->listByStudent($request->userId()), 'id');
        Response::success($this->mcqs->filterOptionsForStudent($request->userId(), $courseIds));
    }

    public function questionBank(Request $request): void
    {
        $studentId = $request->userId();
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(10, (int) $request->query('per_page', 20)));
        $filters = [
            'subject'         => $request->query('subject'),
            'chapter'         => $request->query('chapter'),
            'topic'           => $request->query('topic'),
            'difficulty'      => $request->query('difficulty'),
            'search'          => $request->query('search'),
            'attempt_filter'  => $request->query('attempt_filter'),
            'bookmarked'      => $request->query('bookmarked') ? 1 : null,
            'random'          => $request->query('random') ? 1 : null,
        ];
        Response::success($this->mcqs->searchForStudent($studentId, $courseIds, $filters, $page, $perPage, false));
    }

    public function questionBankPractice(Request $request): void
    {
        $studentId = $request->userId();
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        $limit = min(50, max(5, (int) $request->input('limit', 20)));
        $mode = $request->input('mode', 'random');
        $filters = ['random' => 1];
        if ($mode === 'topic' && $request->input('topic')) {
            $filters['topic'] = $request->input('topic');
        } elseif ($mode === 'subject' && $request->input('subject')) {
            $filters['subject'] = $request->input('subject');
        }
        if ($request->input('attempt_filter')) {
            $filters['attempt_filter'] = $request->input('attempt_filter');
        }
        $result = $this->mcqs->searchForStudent($studentId, $courseIds, $filters, 1, $limit, false);
        Response::success([
            'questions'      => $result['items'] ?? [],
            'mode'           => $mode,
            'timed'          => (bool) $request->input('timed'),
            'time_limit_sec' => $request->input('timed') ? (int) ($request->input('time_limit_minutes', 20) * 60) : null,
        ]);
    }

    public function mistakes(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $filters = [
            'subject'   => $request->query('subject'),
            'chapter'   => $request->query('chapter'),
            'topic'     => $request->query('topic'),
            'date_from' => $request->query('date_from'),
            'date_to'   => $request->query('date_to'),
            'status'    => $request->query('status', 'active'),
        ];
        Response::success($this->mistakes->listForStudent($request->userId(), $filters, $page, 20));
    }

    public function mistakeStats(Request $request): void
    {
        Response::success($this->mistakes->stats($request->userId()));
    }

    public function mistakesPractice(Request $request): void
    {
        $studentId = $request->userId();
        $limit = min(50, max(5, (int) $request->query('limit', 20)));
        $ids = $this->mistakes->practiceIds($studentId, $limit);
        Response::success([
            'questions' => $this->mcqs->findByIds($ids, false),
            'count'     => count($ids),
        ]);
    }

    public function startRevision(Request $request): void
    {
        Response::success($this->premium->startRevisionSession($request->userId()), 'Revision session started', 201);
    }

    public function completeRevision(Request $request): void
    {
        $studentId = $request->userId();
        $sessionId = (int) $request->param('id');
        $session = $this->revisionSessions->find($sessionId);
        if (!$session || (int) $session['student_id'] !== $studentId) {
            Response::error('Session not found', 404);
            return;
        }
        $correct = (int) $request->input('mcqs_correct', 0);
        $solved = (int) $request->input('mcqs_solved', 0);
        $accuracy = $solved > 0 ? round($correct / $solved * 100, 2) : 0;
        $weak = $this->premium->dashboardSummary($studentId)['weak_areas'];
        $summary = [
            'topics_revised'      => $request->input('topics_revised', []),
            'remaining_weak_areas'=> array_slice($weak, 0, 3),
        ];
        $this->revisionSessions->complete($sessionId, [
            'duration_seconds' => (int) $request->input('duration_seconds', 0),
            'topics_revised'   => $summary['topics_revised'],
            'mcqs_solved'      => $solved,
            'mcqs_correct'     => $correct,
            'accuracy'         => $accuracy,
            'summary'          => $summary,
        ]);
        Response::success([
            'topics_revised'       => $summary['topics_revised'],
            'mcqs_solved'          => $solved,
            'mcqs_correct'         => $correct,
            'accuracy'             => $accuracy,
            'duration_seconds'     => (int) $request->input('duration_seconds', 0),
            'remaining_weak_areas' => $summary['remaining_weak_areas'],
        ], 'Revision complete');
    }
}
