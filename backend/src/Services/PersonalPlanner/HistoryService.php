<?php
namespace App\Services\PersonalPlanner;

use App\Repositories\PersonalStudyPlannerRepository;

class HistoryService
{
    public function __construct(private PersonalStudyPlannerRepository $repo) {}

    public function list(int $studentId): array
    {
        return array_map(function ($p) {
            return [
                'id' => (int) $p['id'],
                'plan_name' => $p['plan_name'],
                'plan_mode' => $p['plan_mode'],
                'duration_days' => (int) $p['duration_days'],
                'start_date' => $p['start_date'],
                'end_date' => $p['end_date'],
                'status' => $p['status'],
                'completion_pct' => (float) $p['completion_pct'],
                'created_at' => $p['created_at'],
            ];
        }, $this->repo->listPlans($studentId));
    }

    public function archive(int $studentId, int $planId): void
    {
        $plan = $this->repo->getPlan($planId, $studentId);
        if (!$plan) {
            throw new \RuntimeException('Plan not found');
        }
        $this->repo->updatePlan($planId, ['status' => 'archived']);
    }

    public function resume(int $studentId, int $planId): array
    {
        $plan = $this->repo->getPlan($planId, $studentId);
        if (!$plan) {
            throw new \RuntimeException('Plan not found');
        }
        $this->repo->archiveOtherActive($studentId, $planId);
        $this->repo->updatePlan($planId, ['status' => 'active']);
        return $this->repo->getPlan($planId, $studentId);
    }

    public function delete(int $studentId, int $planId): void
    {
        if (!$this->repo->deletePlan($planId, $studentId)) {
            throw new \RuntimeException('Plan not found');
        }
    }
}
