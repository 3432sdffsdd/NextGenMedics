<?php
namespace App\Services\FcpsPlanner;

use App\Repositories\FcpsStudyPlannerRepository;
use App\Services\NotificationService;

/**
 * Premium FCPS Study Planner — PHP algorithms only (no AI).
 */
class StudyPlannerService
{
    public function __construct(
        private FcpsStudyPlannerRepository $repo,
        private CalendarService $calendar,
        private StatisticsService $stats,
        private ProgressService $progress,
        private ExportService $export,
        private NotificationService $notifications
    ) {}

    public function getPlan(int $studentId): ?array
    {
        $plan = $this->repo->getActivePlan($studentId);
        if (!$plan) {
            return null;
        }
        return [
            'plan'      => $this->publicPlan($plan),
            'dashboard' => $this->stats->dashboard((int) $plan['id'], $studentId),
        ];
    }

    public function generate(int $studentId, array $input): array
    {
        $input = $this->normalize($input);
        $calc = $this->calendar->calculate($input);
        if ($calc['available_study_days'] < 1) {
            throw new \InvalidArgumentException('No available study days between start and exam with your preferred days.');
        }
        $schedule = $this->calendar->buildSchedule($input, $calc);

        $this->repo->archiveActive($studentId);
        $planId = $this->repo->createPlan($studentId, [
            ...$input,
            'subject_order'  => $schedule['subject_order'],
            'weekly_goals'   => $schedule['weekly_goals'],
            'monthly_goals'  => $schedule['monthly_goals'],
            'strategy_notes' => $schedule['strategy_notes'],
        ]);

        $dayIds = [];
        foreach ($schedule['days'] as $day) {
            $dayIds[$day['plan_date']] = $this->repo->insertDay($planId, $day);
        }
        foreach ($schedule['tasks'] as $task) {
            $dayId = $dayIds[$task['plan_date']] ?? null;
            if ($dayId) {
                $this->repo->insertTask($planId, $dayId, $task);
            }
        }
        foreach ($schedule['sessions'] as $session) {
            $dayId = $dayIds[$session['plan_date']] ?? null;
            if ($dayId) {
                $this->repo->insertSession($planId, $dayId, $session);
            }
        }

        $this->repo->rebuildSubjectProgress($planId, $studentId);
        $this->repo->addHistory($planId, $studentId, 'generate', 'Study plan generated with PHP scheduler.', [
            'available_study_days' => $calc['available_study_days'],
            'total_hours' => $calc['total_study_hours'],
        ]);
        $this->repo->awardBadge($studentId, $planId, 'plan_created', 'Plan Builder', 'Created your FCPS study plan.');

        try {
            $this->notifications->notify(
                $studentId,
                'study_plan',
                'FCPS Study Plan Ready',
                'Your personalized study plan has been generated. Open FCPS Study Planner to start today.',
                ['href' => '/student/fcps-planner'],
                false
            );
        } catch (\Throwable $e) {
            // non-fatal
        }

        $plan = $this->repo->getPlanById($planId, $studentId);
        return [
            'plan'      => $this->publicPlan($plan),
            'dashboard' => $this->stats->dashboard($planId, $studentId),
            'calc'      => [
                'remaining_calendar_days' => $calc['remaining_calendar_days'],
                'available_study_days'    => $calc['available_study_days'],
                'total_study_hours'       => $calc['total_study_hours'],
            ],
        ];
    }

    public function dashboard(int $studentId): array
    {
        $plan = $this->repo->getActivePlan($studentId);
        if (!$plan) {
            return ['has_plan' => false];
        }
        $this->autoReschedule($studentId, (int) $plan['id'], false);
        return [
            'has_plan'  => true,
            'plan'      => $this->publicPlan($plan),
            'dashboard' => $this->stats->dashboard((int) $plan['id'], $studentId),
        ];
    }

    public function calendarMonth(int $studentId, ?string $month = null): array
    {
        $plan = $this->requirePlan($studentId);
        $month = $month ?: date('Y-m');
        $from = $month . '-01';
        $to = date('Y-m-t', strtotime($from));
        $days = $this->repo->daysBetween((int) $plan['id'], $from, $to);
        $out = [];
        foreach ($days as $day) {
            $tasks = $this->repo->tasksForDate((int) $plan['id'], $day['plan_date']);
            $out[] = $this->dayPayload($day, $tasks);
        }
        return ['month' => $month, 'days' => $out, 'plan' => $this->publicPlan($plan)];
    }

    public function dayDetail(int $studentId, string $date): array
    {
        $plan = $this->requirePlan($studentId);
        $day = $this->repo->getDay((int) $plan['id'], $date);
        if (!$day) {
            return ['date' => $date, 'day' => null, 'tasks' => [], 'sessions' => []];
        }
        return [
            'date'     => $date,
            'day'      => $this->dayPayload($day, $this->repo->tasksForDate((int) $plan['id'], $date)),
            'tasks'    => $this->repo->tasksForDate((int) $plan['id'], $date),
            'sessions' => $this->repo->sessionsForDay((int) $day['id']),
        ];
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
        $this->progress->syncAfterTaskChange((int) $task['plan_id'], $studentId, $task['plan_date'], (int) $task['day_id']);
        $this->repo->addHistory((int) $task['plan_id'], $studentId, 'task_' . $status, $task['title'] . ' → ' . $status);
        return $this->dayDetail($studentId, $task['plan_date']);
    }

    public function rescheduleTask(int $studentId, int $taskId, string $newDate): array
    {
        $task = $this->repo->getTask($taskId, $studentId);
        if (!$task) {
            throw new \RuntimeException('Task not found');
        }
        $planId = (int) $task['plan_id'];
        $dayId = $this->repo->ensureDay($planId, $newDate);
        $this->repo->moveTaskToDate($taskId, $dayId, $newDate);
        $this->repo->refreshDayCompletion((int) $task['day_id']);
        $this->progress->syncAfterTaskChange($planId, $studentId, $newDate, $dayId);
        $this->repo->addHistory($planId, $studentId, 'reschedule', "Moved \"{$task['title']}\" to {$newDate}");
        return $this->dayDetail($studentId, $newDate);
    }

    public function resetToday(int $studentId): array
    {
        $plan = $this->requirePlan($studentId);
        $today = date('Y-m-d');
        $this->repo->resetDayTasks((int) $plan['id'], $today);
        $this->repo->rebuildSubjectProgress((int) $plan['id'], $studentId);
        $this->repo->addHistory((int) $plan['id'], $studentId, 'reset_today', 'Reset today\'s checklist.');
        return $this->dayDetail($studentId, $today);
    }

    public function handleMissed(int $studentId): array
    {
        $plan = $this->requirePlan($studentId);
        $result = $this->autoReschedule($studentId, (int) $plan['id'], true);
        $this->repo->addHistory((int) $plan['id'], $studentId, 'missed_reschedule', 'Missed tasks redistributed.', $result);
        return [
            'reschedule'  => $result,
            'explanation' => $this->explainReschedule($result),
            'dashboard'   => $this->stats->dashboard((int) $plan['id'], $studentId),
        ];
    }

    public function search(int $studentId, string $q, ?string $status = null, ?string $type = null): array
    {
        $plan = $this->requirePlan($studentId);
        return ['items' => $this->repo->searchTasks((int) $plan['id'], $q, $status, $type)];
    }

    public function reset(int $studentId): void
    {
        $plan = $this->repo->getActivePlan($studentId);
        if ($plan) {
            $this->repo->deletePlan((int) $plan['id'], $studentId);
            $this->repo->addHistory(null, $studentId, 'reset', 'Study plan reset.');
        }
    }

    public function export(int $studentId): array
    {
        $plan = $this->requirePlan($studentId);
        return $this->export->payload((int) $plan['id'], $studentId);
    }

    public function exportCsv(int $studentId): string
    {
        $plan = $this->requirePlan($studentId);
        return $this->export->csv((int) $plan['id'], $studentId);
    }

    public function exportPrintHtml(int $studentId): string
    {
        return $this->export->printHtml($this->export($studentId));
    }

    /** @return array{moved_count:int,from_dates:list<string>,to_dates:list<string>,missed_found:int} */
    private function autoReschedule(int $studentId, int $planId, bool $redistribute): array
    {
        $today = date('Y-m-d');
        $missed = $this->repo->markMissedBefore($planId, $today);
        $from = array_values(array_unique(array_column($missed, 'plan_date')));
        $to = [];
        $movedCount = 0;
        if ($redistribute && $missed) {
            $upcoming = $this->repo->upcomingStudyDates($planId, $today, 45);
            $moved = $this->calendar->redistribute($missed, $upcoming, 2);
            foreach ($moved as $task) {
                $dayId = $this->repo->ensureDay($planId, $task['plan_date']);
                $this->repo->moveTaskToDate((int) $task['id'], $dayId, $task['plan_date']);
                $this->repo->refreshDayCompletion($dayId);
                $to[] = $task['plan_date'];
                $movedCount++;
            }
            $this->repo->rebuildSubjectProgress($planId, $studentId);
        }
        return [
            'moved_count'  => $movedCount,
            'from_dates'   => $from,
            'to_dates'     => array_values(array_unique($to)),
            'missed_found' => count($missed),
        ];
    }

    private function explainReschedule(array $result): string
    {
        if (($result['moved_count'] ?? 0) === 0) {
            return 'No missed pending tasks needed redistribution.';
        }
        return sprintf(
            'Moved %d missed task(s) onto upcoming study days (max 2 extras per day) so no day is overloaded. Revision cadence and subject order stay intact.',
            $result['moved_count']
        );
    }

    private function requirePlan(int $studentId): array
    {
        $plan = $this->repo->getActivePlan($studentId);
        if (!$plan) {
            throw new \RuntimeException('No active study plan. Generate one first.');
        }
        return $plan;
    }

    private function normalize(array $input): array
    {
        $list = static function ($v): array {
            if (is_string($v)) {
                return array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $v) ?: [])));
            }
            if (!is_array($v)) {
                return [];
            }
            return array_values(array_filter(array_map(static fn($x) => trim((string) $x), $v)));
        };

        $exam = (string) ($input['exam_date'] ?? '');
        $start = (string) ($input['start_date'] ?? date('Y-m-d'));
        if ($exam === '') {
            throw new \InvalidArgumentException('Exam date is required');
        }
        $remaining = $list($input['subjects_remaining'] ?? []);
        if (!$remaining) {
            throw new \InvalidArgumentException('At least one remaining subject is required');
        }

        $rev = $input['revision_preference'] ?? 'every_7_days';
        // Accept legacy alias
        if ($rev === 'weekends_only') {
            $rev = 'every_sunday';
        }
        if (!in_array($rev, ['every_3_days', 'every_5_days', 'every_7_days', 'every_sunday', 'after_each_subject'], true)) {
            $rev = 'every_7_days';
        }

        $days = array_map('strtolower', $list($input['preferred_days'] ?? [
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday',
        ]));

        return [
            'exam_date'              => $exam,
            'start_date'             => $start,
            'hours_per_day'          => max(0.5, min(16, (float) ($input['hours_per_day'] ?? 3))),
            'preferred_days'         => $days,
            'sessions_per_day'       => max(1, min(3, (int) ($input['sessions_per_day'] ?? 2))),
            'preferred_time'         => in_array($input['preferred_time'] ?? '', ['morning', 'afternoon', 'evening', 'night'], true)
                ? $input['preferred_time'] : 'evening',
            'subjects_completed'     => $list($input['subjects_completed'] ?? []),
            'subjects_remaining'     => $remaining,
            'subjects_weak'          => $list($input['subjects_weak'] ?? []),
            'subjects_strong'        => $list($input['subjects_strong'] ?? []),
            'daily_mcq_target'       => max(5, min(300, (int) ($input['daily_mcq_target'] ?? 40))),
            'daily_flashcard_target' => max(5, min(300, (int) ($input['daily_flashcard_target'] ?? 30))),
            'revision_preference'    => $rev,
        ];
    }

    private function publicPlan(array $plan): array
    {
        return [
            'id'                     => (int) $plan['id'],
            'exam_date'              => $plan['exam_date'],
            'start_date'             => $plan['start_date'],
            'hours_per_day'          => (float) $plan['hours_per_day'],
            'preferred_days'         => json_decode($plan['preferred_days'] ?? '[]', true) ?: [],
            'sessions_per_day'       => (int) $plan['sessions_per_day'],
            'preferred_time'         => $plan['preferred_time'],
            'subjects_completed'     => json_decode($plan['subjects_completed'] ?? '[]', true) ?: [],
            'subjects_remaining'     => json_decode($plan['subjects_remaining'] ?? '[]', true) ?: [],
            'subjects_weak'          => json_decode($plan['subjects_weak'] ?? '[]', true) ?: [],
            'subjects_strong'        => json_decode($plan['subjects_strong'] ?? '[]', true) ?: [],
            'subject_order'          => json_decode($plan['subject_order'] ?? '[]', true) ?: [],
            'daily_mcq_target'       => (int) $plan['daily_mcq_target'],
            'daily_flashcard_target' => (int) $plan['daily_flashcard_target'],
            'revision_preference'    => $plan['revision_preference'],
            'weekly_goals'           => json_decode($plan['weekly_goals'] ?? '[]', true) ?: [],
            'monthly_goals'          => json_decode($plan['monthly_goals'] ?? '[]', true) ?: [],
            'strategy_notes'         => $plan['strategy_notes'],
            'completion_pct'         => (float) $plan['completion_pct'],
            'streak_days'            => (int) $plan['streak_days'],
            'status'                 => $plan['status'],
        ];
    }

    private function dayPayload(array $day, array $tasks): array
    {
        $counts = ['study' => 0, 'mcq' => 0, 'flashcard' => 0, 'revision' => 0, 'completed' => 0, 'pending' => 0, 'missed' => 0, 'skipped' => 0];
        foreach ($tasks as $t) {
            $counts[$t['task_type']] = ($counts[$t['task_type']] ?? 0) + 1;
            $counts[$t['status']] = ($counts[$t['status']] ?? 0) + 1;
        }
        return [
            'id'               => (int) $day['id'],
            'plan_date'        => $day['plan_date'],
            'is_study_day'     => (int) $day['is_study_day'] === 1,
            'day_status'       => $day['day_status'],
            'topics'           => json_decode($day['topics'] ?? '[]', true) ?: [],
            'mcq_target'       => (int) $day['mcq_target'],
            'flashcard_target' => (int) $day['flashcard_target'],
            'revision_subject' => $day['revision_subject'],
            'weekly_goal'      => $day['weekly_goal'] ?? null,
            'completed_pct'    => (float) $day['completed_pct'],
            'counts'           => $counts,
            'task_count'       => count($tasks),
        ];
    }
}
