<?php
namespace App\Services\PersonalPlanner;

use App\Repositories\PersonalStudyPlannerRepository;

class StatisticsService
{
    public function __construct(
        private PersonalStudyPlannerRepository $repo,
        private ProgressService $progress
    ) {}

    public function overview(int $studentId, ?int $activePlanId = null): array
    {
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $stats = $this->repo->statsBetween($studentId, $monthStart, $monthEnd);

        $sum = static function (array $rows, string $key): int {
            return (int) array_sum(array_column($rows, $key));
        };

        $weekStats = array_values(array_filter(
            $stats,
            static fn($s) => $s['stat_date'] >= $weekStart && $s['stat_date'] <= $weekEnd
        ));

        $dailyAvg = 0.0;
        if ($stats) {
            $daysWith = count(array_filter($stats, static fn($s) => (int) $s['tasks_completed'] > 0));
            $dailyAvg = $daysWith > 0 ? round($sum($stats, 'tasks_completed') / $daysWith, 1) : 0;
        }

        $activePct = 0.0;
        $weeklyPct = 0.0;
        $monthlyPct = 0.0;
        if ($activePlanId) {
            $activePct = (float) ($this->repo->getProgress($activePlanId)['overall_pct'] ?? 0);
            $weeklyPct = $this->progress->periodPct($activePlanId, $weekStart, $weekEnd);
            $monthlyPct = $this->progress->periodPct($activePlanId, $monthStart, $monthEnd);
        }

        return [
            'study_hours' => round($sum($stats, 'study_minutes') / 60, 1),
            'completed_plans' => $this->repo->countPlans($studentId, 'completed'),
            'active_plans' => $this->repo->countPlans($studentId, 'active'),
            'total_plans' => $this->repo->countPlans($studentId),
            'average_daily_progress' => $dailyAvg,
            'weekly_progress' => $weeklyPct,
            'monthly_progress' => $monthlyPct,
            'current_plan_pct' => $activePct,
            'videos_watched' => $sum($stats, 'videos_watched'),
            'quizzes_attempted' => $sum($stats, 'quizzes_attempted'),
            'flashcards_reviewed' => $sum($stats, 'flashcards_reviewed'),
            'manual_tasks_completed' => $sum($stats, 'manual_completed'),
            'week_tasks_completed' => $sum($weekStats, 'tasks_completed'),
        ];
    }
}
