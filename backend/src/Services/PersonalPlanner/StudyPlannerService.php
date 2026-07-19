<?php
namespace App\Services\PersonalPlanner;

use App\Repositories\PersonalStudyPlannerRepository;
use App\Services\GoalPlanner\ContentCatalogService;
use App\Services\NotificationService;

class StudyPlannerService
{
    public function __construct(
        private PersonalStudyPlannerRepository $repo,
        private ContentCatalogService $catalog,
        private ScheduleGeneratorService $scheduler,
        private ProgressService $progress,
        private CalendarService $calendarService,
        private StatisticsService $statistics,
        private HistoryService $historyService,
        private ExportService $exportService,
        private NotificationService $notifications
    ) {}

    public function bootstrap(int $studentId): array
    {
        $settings = $this->repo->getSettings($studentId);
        if (!$settings || !(int) ($settings['setup_completed'] ?? 0)) {
            return [
                'setup_required' => true,
                'settings' => $settings,
                'catalog' => $this->catalog->catalogForStudent($studentId),
            ];
        }
        return $this->dashboard($studentId);
    }

    public function saveSetup(int $studentId, array $input): array
    {
        $exam = trim((string) ($input['exam_date'] ?? ''));
        if ($exam === '') {
            throw new \InvalidArgumentException('Exam date is required');
        }
        $examDate = date('Y-m-d', strtotime($exam));
        if ($examDate < date('Y-m-d')) {
            throw new \InvalidArgumentException('Exam date must be today or later');
        }
        $days = $input['preferred_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        if (!is_array($days) || !$days) {
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        }
        $this->repo->saveSettings($studentId, [
            'exam_date' => $examDate,
            'hours_per_day' => max(0.5, min(16, (float) ($input['hours_per_day'] ?? 3))),
            'preferred_days' => array_values($days),
        ]);
        return $this->dashboard($studentId);
    }

    public function catalog(int $studentId): array
    {
        return $this->catalog->catalogForStudent($studentId);
    }

    public function dashboard(int $studentId): array
    {
        $settings = $this->repo->getSettings($studentId);
        if (!$settings || !(int) ($settings['setup_completed'] ?? 0)) {
            return [
                'setup_required' => true,
                'settings' => $settings,
                'catalog' => $this->catalog($studentId),
            ];
        }

        $active = $this->repo->getActivePlan($studentId);
        $today = date('Y-m-d');
        $examCountdown = null;
        if (!empty($settings['exam_date'])) {
            $examCountdown = $settings['exam_date'] < $today
                ? 0
                : (int) (new \DateTimeImmutable($today))->diff(new \DateTimeImmutable($settings['exam_date']))->days;
        }

        $todayTasks = $active ? $this->repo->tasksForDate((int) $active['id'], $today) : [];
        $byType = [
            'video' => [], 'quiz' => [], 'flashcard' => [], 'manual' => [], 'revision' => [], 'note' => [], 'mcq' => [],
        ];
        foreach ($todayTasks as $t) {
            $type = $t['task_type'] === 'lecture' ? 'video' : $t['task_type'];
            if (!isset($byType[$type])) {
                $byType[$type] = [];
            }
            $byType[$type][] = $t;
        }

        $completedToday = count(array_filter($todayTasks, static fn($t) => $t['status'] === 'completed'));
        $remainingToday = count(array_filter($todayTasks, static fn($t) => $t['status'] === 'pending'));
        $todayPct = $todayTasks ? round(($completedToday / count($todayTasks)) * 100, 1) : 0;

        $progress = $active ? ($this->repo->getProgress((int) $active['id']) ?: $this->progress->rebuild((int) $active['id'], $studentId)) : null;
        $stats = $this->statistics->overview($studentId, $active ? (int) $active['id'] : null);

        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $notifications = $this->buildNotifications($studentId, $settings, $active, $todayTasks, $examCountdown);

        return [
            'setup_required' => false,
            'settings' => $settings,
            'exam_countdown_days' => $examCountdown,
            'total_plans' => $stats['total_plans'],
            'completed_plans' => $stats['completed_plans'],
            'active_plan' => $active ? $this->publicPlan($active) : null,
            'today' => [
                'date' => $today,
                'all_tasks' => $todayTasks,
                'videos' => $byType['video'],
                'quizzes' => $byType['quiz'],
                'flashcards' => $byType['flashcard'],
                'manual' => $byType['manual'],
                'revision' => $byType['revision'],
                'notes' => $byType['note'],
                'completed_today' => $completedToday,
                'remaining_today' => $remainingToday,
                'completion_pct' => $todayPct,
            ],
            'progress' => $progress,
            'subject_progress' => $active ? $this->repo->subjectProgress((int) $active['id']) : [],
            'statistics' => $stats,
            'history' => $this->historyService->list($studentId),
            'weekly_pct' => $active ? $this->progress->periodPct((int) $active['id'], $weekStart, $weekEnd) : 0,
            'monthly_pct' => $active ? $this->progress->periodPct((int) $active['id'], $monthStart, $monthEnd) : 0,
            'streak_days' => (int) ($progress['streak_days'] ?? 0),
            'notifications' => $notifications,
            'catalog' => $this->catalog($studentId),
        ];
    }

    public function createPlan(int $studentId, array $input): array
    {
        $settings = $this->requireSetup($studentId);
        $mode = in_array($input['plan_mode'] ?? '', ['lms', 'manual', 'mixed'], true)
            ? $input['plan_mode'] : 'lms';
        $duration = $this->resolveDuration($input);
        $start = !empty($input['start_date'])
            ? date('Y-m-d', strtotime((string) $input['start_date']))
            : date('Y-m-d');
        $name = trim((string) ($input['plan_name'] ?? '')) ?: $this->defaultName($mode, $duration);

        $selection = is_array($input['selection'] ?? null) ? $input['selection'] : [];
        $manualTasks = is_array($input['manual_tasks'] ?? null) ? $input['manual_tasks'] : [];

        $lmsItems = [];
        if ($mode === 'lms' || $mode === 'mixed') {
            $lmsItems = $this->catalog->resolveItems($studentId, 'custom', $selection);
            if ($mode === 'lms' && !$lmsItems) {
                throw new \InvalidArgumentException('Select at least one LMS content item.');
            }
        }
        if ($mode === 'manual' && !$manualTasks) {
            throw new \InvalidArgumentException('Add at least one manual task.');
        }
        if ($mode === 'mixed' && !$lmsItems && !$manualTasks) {
            throw new \InvalidArgumentException('Add LMS content and/or manual tasks.');
        }

        $preferred = $settings['preferred_days'] ?? [];
        if (is_string($preferred)) {
            $preferred = json_decode($preferred, true) ?: [];
        }
        $schedule = $this->scheduler->build($lmsItems, $manualTasks, $duration, $start, $preferred);
        $endDate = $schedule['days'][count($schedule['days']) - 1]['plan_date'];

        // Keep previous active as archived when creating a new active plan
        $existingActive = $this->repo->getActivePlan($studentId);
        if ($existingActive) {
            $this->repo->updatePlan((int) $existingActive['id'], ['status' => 'archived']);
        }

        $planId = $this->repo->createPlan($studentId, [
            'plan_name' => $name,
            'plan_mode' => $mode,
            'duration_days' => $duration,
            'start_date' => $start,
            'end_date' => $endDate,
            'status' => 'active',
        ]);

        $dayIds = [];
        foreach ($schedule['days'] as $day) {
            $dayIds[$day['plan_date']] = $this->repo->insertDay($planId, $day['day_number'], $day['plan_date']);
        }
        foreach ($schedule['tasks'] as $task) {
            $dayId = $dayIds[$task['plan_date']] ?? null;
            if (!$dayId) {
                continue;
            }
            $this->repo->insertTask($planId, $dayId, $task);
        }

        $this->progress->rebuild($planId, $studentId);
        $this->progress->syncDayStat($planId, $studentId, $start);

        try {
            $this->notifications->notify(
                $studentId,
                'study_plan',
                'Personal Study Plan Ready',
                "\"{$name}\" is ready — check today's tasks.",
                ['href' => '/student/personal-planner'],
                false
            );
        } catch (\Throwable $e) { /* ignore */ }

        return $this->dashboard($studentId);
    }

    public function setTaskStatus(int $studentId, int $taskId, string $status): array
    {
        if (!in_array($status, ['pending', 'completed', 'skipped'], true)) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $task = $this->repo->getTask($taskId, $studentId);
        if (!$task) {
            throw new \RuntimeException('Task not found');
        }
        $this->repo->setTaskStatus($taskId, $status);
        $this->repo->refreshDay((int) $task['day_id']);
        $this->progress->rebuild((int) $task['plan_id'], $studentId);
        $this->progress->syncDayStat((int) $task['plan_id'], $studentId, $task['plan_date']);
        return $this->calendarService->dayDetails((int) $task['plan_id'], $task['plan_date']);
    }

    public function moveTask(int $studentId, int $taskId, string $action): array
    {
        $task = $this->repo->getTask($taskId, $studentId);
        if (!$task) {
            throw new \RuntimeException('Task not found');
        }
        $planId = (int) $task['plan_id'];
        $oldDayId = (int) $task['day_id'];

        if ($action === 'keep_pending') {
            $this->repo->setTaskStatus($taskId, 'pending');
            $this->repo->refreshDay($oldDayId);
            return $this->calendarService->dayDetails($planId, $task['plan_date']);
        }

        if ($action === 'tomorrow') {
            $targetDate = (new \DateTimeImmutable($task['plan_date']))->modify('+1 day')->format('Y-m-d');
            // Prefer next study day in plan if tomorrow is not a plan day
            $days = $this->repo->daysForPlan($planId);
            $next = null;
            foreach ($days as $d) {
                if ($d['plan_date'] > $task['plan_date']) {
                    $next = $d;
                    break;
                }
            }
            if ($next) {
                $targetDate = $next['plan_date'];
                $dayId = (int) $next['id'];
                $dayNumber = (int) $next['day_number'];
            } else {
                $dayId = $this->repo->ensureDay($planId, $targetDate);
                $day = $this->repo->getDay($planId, $targetDate);
                $dayNumber = (int) ($day['day_number'] ?? 1);
            }
        } elseif ($action === 'end') {
            $end = $this->repo->lastPlanDate($planId) ?: $task['plan_date'];
            $day = $this->repo->getDay($planId, $end);
            if (!$day) {
                throw new \RuntimeException('End of plan day not found');
            }
            $dayId = (int) $day['id'];
            $dayNumber = (int) $day['day_number'];
            $targetDate = $end;
        } else {
            throw new \InvalidArgumentException('Invalid move action');
        }

        $this->repo->moveTask($taskId, $dayId, $targetDate, $dayNumber);
        $this->repo->refreshDay($oldDayId);
        $this->repo->refreshDay($dayId);
        $this->progress->rebuild($planId, $studentId);
        return $this->calendarService->dayDetails($planId, $targetDate);
    }

    public function calendar(int $studentId, ?string $month = null): array
    {
        $plan = $this->requireActive($studentId);
        return $this->calendarService->month((int) $plan['id'], $month ?: date('Y-m'));
    }

    public function day(int $studentId, string $date): array
    {
        $plan = $this->requireActive($studentId);
        return $this->calendarService->dayDetails((int) $plan['id'], $date);
    }

    public function history(int $studentId): array
    {
        return ['plans' => $this->historyService->list($studentId)];
    }

    public function resume(int $studentId, int $planId): array
    {
        $this->historyService->resume($studentId, $planId);
        return $this->dashboard($studentId);
    }

    public function archive(int $studentId, int $planId): array
    {
        $this->historyService->archive($studentId, $planId);
        return $this->dashboard($studentId);
    }

    public function delete(int $studentId, int $planId): array
    {
        $this->historyService->delete($studentId, $planId);
        return $this->dashboard($studentId);
    }

    public function duplicate(int $studentId, int $planId): array
    {
        $plan = $this->repo->getPlan($planId, $studentId);
        if (!$plan) {
            throw new \RuntimeException('Plan not found');
        }
        $tasks = $this->repo->allTasks($planId);
        $manual = [];
        $selection = [];
        foreach ($tasks as $t) {
            if ($t['source'] === 'manual') {
                $manual[] = [
                    'day_number' => (int) $t['day_number'],
                    'title' => $t['title'],
                    'description' => $t['description'],
                    'subject_title' => $t['subject_title'],
                ];
            } else {
                $type = $t['task_type'] === 'flashcard' ? 'flashcard_set' : $t['task_type'];
                if ($type === 'video' && empty($t['ref_id'])) {
                    $type = 'lecture';
                }
                if (!empty($t['ref_id'])) {
                    $selection[] = [
                        'type' => $type === 'lecture' ? 'lecture' : $type,
                        'ref_id' => (int) $t['ref_id'],
                        'course_id' => (int) ($t['course_id'] ?? 0),
                        'lecture_id' => (int) ($t['lecture_id'] ?? 0),
                        'title' => $t['title'],
                    ];
                }
            }
        }

        return $this->createPlan($studentId, [
            'plan_mode' => $plan['plan_mode'],
            'plan_name' => $plan['plan_name'] . ' (Copy)',
            'duration_days' => (int) $plan['duration_days'],
            'start_date' => date('Y-m-d'),
            'selection' => $selection,
            'manual_tasks' => $manual,
        ]);
    }

    public function resetActive(int $studentId): array
    {
        $active = $this->repo->getActivePlan($studentId);
        if ($active) {
            $this->repo->updatePlan((int) $active['id'], ['status' => 'archived']);
        }
        return $this->dashboard($studentId);
    }

    public function export(int $studentId, ?int $planId = null): array
    {
        if (!$planId) {
            $active = $this->requireActive($studentId);
            $planId = (int) $active['id'];
        }
        return $this->exportService->export($studentId, $planId);
    }

    public function viewPlan(int $studentId, int $planId): array
    {
        $plan = $this->repo->getPlan($planId, $studentId);
        if (!$plan) {
            throw new \RuntimeException('Plan not found');
        }
        $days = [];
        foreach ($this->repo->daysForPlan($planId) as $day) {
            $days[] = [
                'day' => $day,
                'tasks' => $this->repo->tasksForDate($planId, $day['plan_date']),
            ];
        }
        return [
            'plan' => $this->publicPlan($plan),
            'progress' => $this->repo->getProgress($planId),
            'days' => $days,
            'subject_progress' => $this->repo->subjectProgress($planId),
        ];
    }

    private function resolveDuration(array $input): int
    {
        if (!empty($input['duration_days'])) {
            return max(1, min(90, (int) $input['duration_days']));
        }
        $preset = (string) ($input['duration'] ?? '7');
        if ($preset === 'today') {
            return 1;
        }
        if ($preset === 'custom') {
            return max(1, min(90, (int) ($input['custom_days'] ?? 7)));
        }
        return max(1, min(90, (int) $preset));
    }

    private function defaultName(string $mode, int $days): string
    {
        $label = ['lms' => 'LMS Content', 'manual' => 'Manual', 'mixed' => 'Mixed'][$mode] ?? 'Study';
        return "{$days} Day {$label} Plan";
    }

    private function requireSetup(int $studentId): array
    {
        $settings = $this->repo->getSettings($studentId);
        if (!$settings || !(int) ($settings['setup_completed'] ?? 0)) {
            throw new \RuntimeException('Complete first-time setup first');
        }
        return $settings;
    }

    private function requireActive(int $studentId): array
    {
        $plan = $this->repo->getActivePlan($studentId);
        if (!$plan) {
            throw new \RuntimeException('No active plan');
        }
        return $plan;
    }

    private function publicPlan(array $plan): array
    {
        return [
            'id' => (int) $plan['id'],
            'plan_name' => $plan['plan_name'],
            'plan_mode' => $plan['plan_mode'],
            'duration_days' => (int) $plan['duration_days'],
            'start_date' => $plan['start_date'],
            'end_date' => $plan['end_date'],
            'status' => $plan['status'],
            'completion_pct' => (float) $plan['completion_pct'],
        ];
    }

    private function buildNotifications(int $studentId, array $settings, ?array $active, array $todayTasks, ?int $examCountdown): array
    {
        $notes = [];
        $pendingToday = count(array_filter($todayTasks, static fn($t) => $t['status'] === 'pending'));
        if ($pendingToday > 0) {
            $notes[] = ['type' => 'today', 'title' => "Today's Tasks", 'message' => "{$pendingToday} task(s) remaining today"];
        }
        if ($active && $active['end_date'] === date('Y-m-d', strtotime('+1 day'))) {
            $notes[] = ['type' => 'ending', 'title' => 'Plan Ending Tomorrow', 'message' => $active['plan_name'] . ' ends tomorrow'];
        }
        if ($active) {
            $overdue = 0;
            foreach ($this->repo->tasksBetween((int) $active['id'], $active['start_date'], date('Y-m-d', strtotime('-1 day'))) as $t) {
                if ($t['status'] === 'pending') {
                    $overdue++;
                }
            }
            if ($overdue > 0) {
                $notes[] = ['type' => 'overdue', 'title' => 'Overdue Tasks', 'message' => "{$overdue} pending task(s) from previous days"];
            }
            $rev = count(array_filter($todayTasks, static fn($t) => $t['task_type'] === 'revision' && $t['status'] === 'pending'));
            if ($rev > 0) {
                $notes[] = ['type' => 'revision', 'title' => 'Revision Due', 'message' => "{$rev} revision task(s) today"];
            }
        }
        if ($examCountdown !== null && $examCountdown <= 7) {
            $notes[] = ['type' => 'exam', 'title' => 'Upcoming Exam', 'message' => "{$examCountdown} day(s) until exam"];
        }
        return $notes;
    }
}
