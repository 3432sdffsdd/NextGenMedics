<?php
namespace App\Services\FcpsPlanner;

use App\Repositories\FcpsStudyPlannerRepository;

class StatisticsService
{
    public function __construct(
        private FcpsStudyPlannerRepository $repo,
        private ProgressService $progress
    ) {}

    public function dashboard(int $planId, int $studentId): array
    {
        $plan = $this->repo->getPlanById($planId, $studentId);
        if (!$plan) {
            return [];
        }
        $today = date('Y-m-d');
        $exam = $plan['exam_date'];
        $countdown = $exam < $today ? 0 : (int) (new \DateTimeImmutable($today))->diff(new \DateTimeImmutable($exam))->days;

        $todayTasks = $this->repo->tasksForDate($planId, $today);
        $byType = ['study' => [], 'mcq' => [], 'flashcard' => [], 'revision' => []];
        foreach ($todayTasks as $t) {
            $byType[$t['task_type']][] = $t;
        }

        $pct = static function (array $tasks): float {
            $n = count($tasks);
            if (!$n) {
                return 0.0;
            }
            $done = count(array_filter($tasks, static fn($t) => $t['status'] === 'completed'));
            return round(($done / $n) * 100, 1);
        };

        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $weekTasks = $this->repo->tasksBetween($planId, $weekStart, $weekEnd);
        $monthTasks = $this->repo->tasksBetween($planId, $monthStart, $monthEnd);
        $all = $this->repo->allTasks($planId);
        $overall = $pct($all);
        $streak = $this->progress->computeStreak($planId, $today);
        $this->repo->updatePlanStats($planId, $overall, $streak);

        $hoursPerDay = (float) $plan['hours_per_day'];
        $studyDaysTotal = count(array_unique(array_column($all, 'plan_date')));
        $studyDaysDone = 0;
        foreach ($this->repo->allDays($planId) as $day) {
            if ($day['day_status'] === 'completed') {
                $studyDaysDone++;
            }
        }
        $hoursCompleted = round($studyDaysDone * $hoursPerDay, 1);
        $hoursRemaining = max(0, round(($studyDaysTotal - $studyDaysDone) * $hoursPerDay, 1));

        $pendingTopics = $this->repo->countByTypeStatus($planId, 'study', 'pending')
            + $this->repo->countByTypeStatus($planId, 'study', 'missed');

        return [
            'exam_countdown_days' => $countdown,
            'exam_date'           => $exam,
            'today' => [
                'date'       => $today,
                'day'        => $this->repo->getDay($planId, $today),
                'study'      => $byType['study'],
                'mcqs'       => $byType['mcq'],
                'flashcards' => $byType['flashcard'],
                'revision'   => $byType['revision'],
                'all_tasks'  => $todayTasks,
                'completion' => $pct($todayTasks),
            ],
            'completed_tasks'     => $this->repo->countByStatus($planId, 'completed'),
            'pending_tasks'       => $this->repo->countByStatus($planId, 'pending'),
            'missed_tasks'        => $this->repo->countByStatus($planId, 'missed'),
            'study_streak'        => $streak,
            'completion_pct'      => $overall,
            'weekly_progress'     => $pct($weekTasks),
            'monthly_progress'    => $pct($monthTasks),
            'subject_progress'    => $this->repo->subjectProgress($planId),
            'weekly_goals'        => json_decode($plan['weekly_goals'] ?? '[]', true) ?: [],
            'monthly_goals'       => json_decode($plan['monthly_goals'] ?? '[]', true) ?: [],
            'strategy_notes'      => $plan['strategy_notes'],
            'days_remaining'      => $countdown,
            'topics_remaining'    => $pendingTopics,
            'lectures_remaining'  => $pendingTopics,
            'revision_completed'  => $this->repo->countByTypeStatus($planId, 'revision', 'completed'),
            'mcqs_completed'      => $this->repo->sumTargetCompleted($planId, 'mcq'),
            'flashcards_completed'=> $this->repo->sumTargetCompleted($planId, 'flashcard'),
            'study_hours_completed' => $hoursCompleted,
            'study_hours_remaining' => $hoursRemaining,
            'badges'              => $this->repo->badges($studentId, $planId),
            'history'             => $this->repo->recentHistory($studentId, 15),
        ];
    }
}
