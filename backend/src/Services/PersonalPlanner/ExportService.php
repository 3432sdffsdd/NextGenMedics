<?php
namespace App\Services\PersonalPlanner;

use App\Repositories\PersonalStudyPlannerRepository;

class ExportService
{
    public function __construct(private PersonalStudyPlannerRepository $repo) {}

    public function export(int $studentId, int $planId): array
    {
        $plan = $this->repo->getPlan($planId, $studentId);
        if (!$plan) {
            throw new \RuntimeException('Plan not found');
        }
        $days = [];
        foreach ($this->repo->daysForPlan($planId) as $day) {
            $days[] = [
                'day_number' => (int) $day['day_number'],
                'plan_date' => $day['plan_date'],
                'day_status' => $day['day_status'],
                'completed_pct' => (float) $day['completed_pct'],
                'tasks' => $this->repo->tasksForDate($planId, $day['plan_date']),
            ];
        }
        return [
            'plan' => $plan,
            'progress' => $this->repo->getProgress($planId),
            'calendar' => $days,
            'exported_at' => date('c'),
        ];
    }
}
