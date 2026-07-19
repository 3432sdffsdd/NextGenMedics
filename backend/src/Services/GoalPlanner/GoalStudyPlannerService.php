<?php
namespace App\Services\GoalPlanner;

use App\Repositories\GoalStudyPlannerRepository;
use App\Services\NotificationService;

class GoalStudyPlannerService
{
    public function __construct(
        private GoalStudyPlannerRepository $repo,
        private ContentCatalogService $catalog,
        private ScheduleGeneratorService $scheduler,
        private NotificationService $notifications
    ) {}

    public function catalog(int $studentId): array
    {
        return $this->catalog->catalogForStudent($studentId);
    }

    public function generate(int $studentId, array $input): array
    {
        $input = $this->normalize($input);
        $items = $this->catalog->resolveItems($studentId, $input['goal_type'], $input['selection']);
        $dates = $this->scheduler->studyDates($input);
        $schedule = $this->scheduler->build($items, $input, $dates);

        $this->repo->archiveActive($studentId);
        $planId = $this->repo->createPlan($studentId, $input);

        $itemIds = [];
        foreach ($items as $i => $item) {
            $itemIds[$item['item_type'] . ':' . $item['ref_id']] = $this->repo->insertItem($planId, $item, $i);
        }

        $dayIds = [];
        foreach ($schedule['days'] as $day) {
            $dayIds[$day['plan_date']] = $this->repo->insertDay($planId, $day);
        }
        foreach ($schedule['tasks'] as $task) {
            $dayId = $dayIds[$task['plan_date']] ?? null;
            if (!$dayId) {
                continue;
            }
            $itemId = null;
            if (!empty($task['plan_item_key']) && isset($itemIds[$task['plan_item_key']])) {
                $itemId = $itemIds[$task['plan_item_key']];
            }
            $this->repo->insertTask($planId, $dayId, $itemId, $task);
        }

        $this->rebuildProgress($planId, $studentId);
        try {
            $this->notifications->notify(
                $studentId,
                'study_plan',
                'Study Plan Ready',
                'Your goal-based study plan is ready. Check today\'s tasks.',
                ['href' => '/student/study-planner'],
                false
            );
        } catch (\Throwable $e) { /* ignore */ }

        return $this->dashboard($studentId);
    }

    public function dashboard(int $studentId): array
    {
        $plan = $this->repo->getActivePlan($studentId);
        if (!$plan) {
            return ['has_plan' => false, 'catalog' => $this->catalog($studentId)];
        }
        $this->autoMissed($studentId, (int) $plan['id'], false);
        $planId = (int) $plan['id'];
        $today = date('Y-m-d');
        $todayTasks = $this->repo->tasksForDate($planId, $today);
        $all = $this->repo->allTasks($planId);
        $pct = $this->pct($all);
        $streak = $this->streak($planId, $today);
        $this->repo->updatePlanStats($planId, $pct, $streak);

        $byType = ['lecture' => [], 'video' => [], 'note' => [], 'quiz' => [], 'flashcard' => [], 'revision' => [], 'mcq_practice' => []];
        foreach ($todayTasks as $t) {
            $byType[$t['task_type']][] = $t;
        }

        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $target = $plan['target_date'];
        $countdown = $target < $today ? 0 : (int) (new \DateTimeImmutable($today))->diff(new \DateTimeImmutable($target))->days;
        $examCountdown = null;
        if ($plan['exam_date']) {
            $examCountdown = $plan['exam_date'] < $today ? 0 : (int) (new \DateTimeImmutable($today))->diff(new \DateTimeImmutable($plan['exam_date']))->days;
        }

        $challenges = array_map(function ($c) use ($today) {
            $remaining = $c['end_date'] < $today ? 0 : (int) (new \DateTimeImmutable($today))->diff(new \DateTimeImmutable($c['end_date']))->days;
            $progress = $c['target_value'] > 0 ? round(($c['current_value'] / $c['target_value']) * 100, 1) : 0;
            return array_merge($c, [
                'remaining_days' => $remaining,
                'progress_pct' => min(100, $progress),
            ]);
        }, $this->repo->listChallenges($studentId));

        return [
            'has_plan' => true,
            'plan' => $this->publicPlan($plan),
            'dashboard' => [
                'target_countdown_days' => $countdown,
                'exam_countdown_days'   => $examCountdown,
                'today' => [
                    'date' => $today,
                    'lectures' => array_merge($byType['lecture'], $byType['video']),
                    'notes' => $byType['note'],
                    'quizzes' => $byType['quiz'],
                    'flashcards' => $byType['flashcard'],
                    'revision' => $byType['revision'],
                    'mcq_practice' => $byType['mcq_practice'],
                    'all_tasks' => $todayTasks,
                    'completion' => $this->pct($todayTasks),
                ],
                'completed_tasks' => $this->repo->countStatus($planId, 'completed'),
                'pending_tasks' => $this->repo->countStatus($planId, 'pending'),
                'missed_tasks' => $this->repo->countStatus($planId, 'missed'),
                'completion_pct' => $pct,
                'study_streak' => $streak,
                'weekly_progress' => $this->pct($this->repo->tasksBetween($planId, $weekStart, $weekEnd)),
                'monthly_progress' => $this->pct($this->repo->tasksBetween($planId, $monthStart, $monthEnd)),
                'subject_progress' => $this->repo->subjectProgress($planId),
                'completed_lectures' => $this->repo->countTypeStatus($planId, 'lecture', 'completed')
                    + $this->repo->countTypeStatus($planId, 'video', 'completed'),
                'completed_quizzes' => $this->repo->countTypeStatus($planId, 'quiz', 'completed'),
                'completed_notes' => $this->repo->countTypeStatus($planId, 'note', 'completed'),
                'completed_flashcards' => $this->repo->countTypeStatus($planId, 'flashcard', 'completed'),
                'completed_revision' => $this->repo->countTypeStatus($planId, 'revision', 'completed'),
                'challenges' => $challenges,
                'weekly_review' => $this->periodReview($planId, $weekStart, $weekEnd),
                'monthly_report' => $this->periodReview($planId, $monthStart, $monthEnd),
            ],
        ];
    }

    public function calendar(int $studentId, ?string $month = null): array
    {
        $plan = $this->require($studentId);
        $month = $month ?: date('Y-m');
        $from = $month . '-01';
        $to = date('Y-m-t', strtotime($from));
        $out = [];
        foreach ($this->repo->daysBetween((int) $plan['id'], $from, $to) as $day) {
            $tasks = $this->repo->tasksForDate((int) $plan['id'], $day['plan_date']);
            $counts = ['lecture' => 0, 'quiz' => 0, 'flashcard' => 0, 'revision' => 0, 'completed' => 0, 'pending' => 0, 'skipped' => 0];
            foreach ($tasks as $t) {
                if (isset($counts[$t['task_type']])) {
                    $counts[$t['task_type']]++;
                }
                if (isset($counts[$t['status']])) {
                    $counts[$t['status']]++;
                }
            }
            $out[] = [
                'plan_date' => $day['plan_date'],
                'day_status' => $day['day_status'],
                'completed_pct' => (float) $day['completed_pct'],
                'counts' => $counts,
                'task_count' => count($tasks),
            ];
        }
        return ['month' => $month, 'days' => $out];
    }

    public function day(int $studentId, string $date): array
    {
        $plan = $this->require($studentId);
        $day = $this->repo->getDay((int) $plan['id'], $date);
        return [
            'date' => $date,
            'day' => $day,
            'tasks' => $this->repo->tasksForDate((int) $plan['id'], $date),
        ];
    }

    public function setTask(int $studentId, int $taskId, string $status): array
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
        $this->rebuildProgress((int) $task['plan_id'], $studentId);
        $this->syncStat((int) $task['plan_id'], $studentId, $task['plan_date']);
        $this->bumpChallenges($studentId, (int) $task['plan_id']);
        return $this->day($studentId, $task['plan_date']);
    }

    public function reschedule(int $studentId, int $taskId, string $date): array
    {
        $task = $this->repo->getTask($taskId, $studentId);
        if (!$task) {
            throw new \RuntimeException('Task not found');
        }
        $oldDay = (int) $task['day_id'];
        $dayId = $this->repo->ensureDay((int) $task['plan_id'], $date);
        $this->repo->moveTask($taskId, $dayId, $date);
        $this->repo->refreshDay($oldDay);
        $this->repo->refreshDay($dayId);
        return $this->day($studentId, $date);
    }

    public function resetToday(int $studentId): array
    {
        $plan = $this->require($studentId);
        $today = date('Y-m-d');
        $this->repo->resetDay((int) $plan['id'], $today);
        $this->rebuildProgress((int) $plan['id'], $studentId);
        return $this->day($studentId, $today);
    }

    public function handleMissed(int $studentId): array
    {
        $plan = $this->require($studentId);
        $result = $this->autoMissed($studentId, (int) $plan['id'], true);
        return ['reschedule' => $result, 'dashboard' => $this->dashboard($studentId)['dashboard'] ?? []];
    }

    public function createChallenge(int $studentId, array $data): array
    {
        $plan = $this->repo->getActivePlan($studentId);
        $id = $this->repo->createChallenge($studentId, [
            'plan_id' => $plan ? (int) $plan['id'] : null,
            'title' => trim((string) ($data['title'] ?? 'Challenge')),
            'challenge_type' => in_array($data['challenge_type'] ?? '', ['subject_sprint', 'mcq_count', 'deadline', 'syllabus'], true)
                ? $data['challenge_type'] : 'deadline',
            'target_value' => max(1, (int) ($data['target_value'] ?? 1)),
            'current_value' => 0,
            'start_date' => $data['start_date'] ?? date('Y-m-d'),
            'end_date' => $data['end_date'] ?? date('Y-m-d', strtotime('+15 days')),
        ]);
        return ['id' => $id, 'challenges' => $this->repo->listChallenges($studentId)];
    }

    public function reset(int $studentId): void
    {
        $plan = $this->repo->getActivePlan($studentId);
        if ($plan) {
            $this->repo->deletePlan((int) $plan['id'], $studentId);
        }
    }

    public function export(int $studentId): array
    {
        $plan = $this->require($studentId);
        $planId = (int) $plan['id'];
        $calendar = [];
        foreach ($this->repo->allDays($planId) as $day) {
            $calendar[] = [
                'plan_date' => $day['plan_date'],
                'day_status' => $day['day_status'],
                'tasks' => $this->repo->tasksForDate($planId, $day['plan_date']),
            ];
        }
        return [
            'plan' => $this->publicPlan($plan),
            'items' => $this->repo->listItems($planId),
            'calendar' => $calendar,
            'exported_at' => date('c'),
        ];
    }

    private function autoMissed(int $studentId, int $planId, bool $redistribute): array
    {
        $today = date('Y-m-d');
        $missed = $this->repo->markMissedBefore($planId, $today);
        $moved = 0;
        $to = [];
        if ($redistribute && $missed) {
            foreach ($this->scheduler->redistribute($missed, $this->repo->upcomingDates($planId, $today), 2) as $task) {
                $dayId = $this->repo->ensureDay($planId, $task['plan_date']);
                $this->repo->moveTask((int) $task['id'], $dayId, $task['plan_date']);
                $this->repo->refreshDay($dayId);
                $to[] = $task['plan_date'];
                $moved++;
            }
            $this->rebuildProgress($planId, $studentId);
        }
        return ['moved_count' => $moved, 'missed_found' => count($missed), 'to_dates' => array_values(array_unique($to))];
    }

    private function rebuildProgress(int $planId, int $studentId): void
    {
        $tasks = $this->repo->allTasks($planId);
        $bySub = [];
        foreach ($tasks as $t) {
            $sub = $t['subject_title'] ?: 'General';
            if (!isset($bySub[$sub])) {
                $bySub[$sub] = [
                    'course_id' => $t['course_id'],
                    'total_lectures' => 0, 'completed_lectures' => 0,
                    'total_quizzes' => 0, 'completed_quizzes' => 0,
                    'total_notes' => 0, 'completed_notes' => 0,
                    'total_flashcards' => 0, 'completed_flashcards' => 0,
                    'total_revision' => 0, 'completed_revision' => 0,
                ];
            }
            $map = [
                'lecture' => 'lectures', 'video' => 'lectures', 'quiz' => 'quizzes',
                'note' => 'notes', 'flashcard' => 'flashcards', 'revision' => 'revision',
            ];
            $key = $map[$t['task_type']] ?? null;
            if (!$key) {
                continue;
            }
            $bySub[$sub]['total_' . $key]++;
            if ($t['status'] === 'completed') {
                $bySub[$sub]['completed_' . $key]++;
            }
        }
        foreach ($bySub as $subject => $counts) {
            $this->repo->upsertProgress($planId, $studentId, $subject, $counts);
        }
    }

    private function syncStat(int $planId, int $studentId, string $date): void
    {
        $tasks = $this->repo->tasksForDate($planId, $date);
        $done = array_filter($tasks, static fn($t) => $t['status'] === 'completed');
        $this->repo->upsertStat($planId, $studentId, $date, [
            'tasks_total' => count($tasks),
            'tasks_completed' => count($done),
            'study_minutes' => count($done) * 30,
        ]);
    }

    private function bumpChallenges(int $studentId, int $planId): void
    {
        $completed = $this->repo->countStatus($planId, 'completed');
        foreach ($this->repo->listChallenges($studentId) as $c) {
            $status = null;
            if ($completed >= (int) $c['target_value']) {
                $status = 'completed';
            }
            $this->repo->updateChallengeProgress((int) $c['id'], $completed, $status);
        }
    }

    private function periodReview(int $planId, string $from, string $to): array
    {
        $tasks = $this->repo->tasksBetween($planId, $from, $to);
        $done = array_filter($tasks, static fn($t) => $t['status'] === 'completed');
        $typeDone = static function (string $type) use ($done): int {
            return count(array_filter($done, static fn($t) => $t['task_type'] === $type));
        };
        return [
            'from' => $from,
            'to' => $to,
            'hours_studied' => round(count($done) * 0.5, 1),
            'lectures_completed' => $typeDone('lecture') + $typeDone('video'),
            'quizzes_completed' => $typeDone('quiz'),
            'flashcards_reviewed' => $typeDone('flashcard'),
            'revision_completed' => $typeDone('revision'),
            'topics_remaining' => count(array_filter($tasks, static fn($t) => $t['status'] === 'pending')),
            'completion_pct' => $this->pct($tasks),
        ];
    }

    private function streak(int $planId, string $today): int
    {
        $streak = 0;
        $d = new \DateTimeImmutable($today);
        $tt = $this->repo->tasksForDate($planId, $today);
        $start = ($tt && $this->dayDone($tt)) ? $d : $d->modify('-1 day');
        for ($i = 0; $i < 365; $i++) {
            $date = $start->modify("-{$i} days")->format('Y-m-d');
            $day = $this->repo->getDay($planId, $date);
            if (!$day || !(int) $day['is_study_day']) {
                if ($streak > 0) {
                    break;
                }
                continue;
            }
            $tasks = $this->repo->tasksForDate($planId, $date);
            if (!$tasks || !$this->dayDone($tasks)) {
                break;
            }
            $streak++;
        }
        return $streak;
    }

    private function dayDone(array $tasks): bool
    {
        foreach ($tasks as $t) {
            if (!in_array($t['status'], ['completed', 'skipped'], true)) {
                return false;
            }
        }
        return true;
    }

    private function pct(array $tasks): float
    {
        $n = count($tasks);
        if (!$n) {
            return 0.0;
        }
        $done = count(array_filter($tasks, static fn($t) => $t['status'] === 'completed'));
        return round(($done / $n) * 100, 1);
    }

    private function require(int $studentId): array
    {
        $plan = $this->repo->getActivePlan($studentId);
        if (!$plan) {
            throw new \RuntimeException('No active study plan.');
        }
        return $plan;
    }

    private function publicPlan(array $plan): array
    {
        return [
            'id' => (int) $plan['id'],
            'goal_type' => $plan['goal_type'],
            'goal_title' => $plan['goal_title'],
            'start_date' => $plan['start_date'],
            'target_date' => $plan['target_date'],
            'exam_date' => $plan['exam_date'],
            'hours_per_day' => (float) $plan['hours_per_day'],
            'preferred_days' => json_decode($plan['preferred_days'] ?? '[]', true) ?: [],
            'sessions_per_day' => (int) $plan['sessions_per_day'],
            'preferred_time' => $plan['preferred_time'],
            'daily_mcq_target' => (int) $plan['daily_mcq_target'],
            'daily_flashcard_target' => (int) $plan['daily_flashcard_target'],
            'revision_preference' => $plan['revision_preference'],
            'completion_pct' => (float) $plan['completion_pct'],
            'streak_days' => (int) $plan['streak_days'],
        ];
    }

    private function normalize(array $input): array
    {
        $list = static function ($v): array {
            if (!is_array($v)) {
                return [];
            }
            return array_values(array_filter(array_map('strtolower', array_map('strval', $v))));
        };
        $goal = (string) ($input['goal_type'] ?? 'custom');
        $allowed = ['full_syllabus','selected_subjects','selected_lectures','selected_quizzes','selected_flashcards','selected_notes','mock_exam','revision_only','custom'];
        if (!in_array($goal, $allowed, true)) {
            $goal = 'custom';
        }
        $start = (string) ($input['start_date'] ?? date('Y-m-d'));
        $target = (string) ($input['target_date'] ?? $input['exam_date'] ?? date('Y-m-d', strtotime('+30 days')));
        $rev = $input['revision_preference'] ?? 'every_7_days';
        if ($rev === 'weekends_only') {
            $rev = 'every_sunday';
        }
        return [
            'goal_type' => $goal,
            'goal_title' => trim((string) ($input['goal_title'] ?? 'My Study Plan')) ?: 'My Study Plan',
            'start_date' => $start,
            'target_date' => $target,
            'exam_date' => !empty($input['exam_date']) ? $input['exam_date'] : null,
            'hours_per_day' => max(0.5, min(16, (float) ($input['hours_per_day'] ?? 3))),
            'preferred_days' => $list($input['preferred_days'] ?? ['monday','tuesday','wednesday','thursday','friday','saturday']) ?: ['monday','tuesday','wednesday','thursday','friday'],
            'sessions_per_day' => max(1, min(3, (int) ($input['sessions_per_day'] ?? 2))),
            'preferred_time' => in_array($input['preferred_time'] ?? '', ['morning','afternoon','evening','night'], true) ? $input['preferred_time'] : 'evening',
            'daily_mcq_target' => max(0, min(300, (int) ($input['daily_mcq_target'] ?? 40))),
            'daily_flashcard_target' => max(0, min(300, (int) ($input['daily_flashcard_target'] ?? 30))),
            'revision_preference' => in_array($rev, ['every_3_days','every_5_days','every_7_days','every_sunday','after_each_subject'], true) ? $rev : 'every_7_days',
            'selection' => is_array($input['selection'] ?? null) ? $input['selection'] : [],
        ];
    }
}
