<?php
namespace App\Services\PersonalPlanner;

use App\Repositories\PersonalStudyPlannerRepository;

class CalendarService
{
    public function __construct(private PersonalStudyPlannerRepository $repo) {}

    public function month(int $planId, string $month): array
    {
        $from = $month . '-01';
        $to = date('Y-m-t', strtotime($from));
        $out = [];
        foreach ($this->repo->daysBetween($planId, $from, $to) as $day) {
            $tasks = $this->repo->tasksForDate($planId, $day['plan_date']);
            $counts = [
                'video' => 0, 'quiz' => 0, 'flashcard' => 0, 'manual' => 0, 'revision' => 0, 'note' => 0,
                'completed' => 0, 'pending' => 0, 'skipped' => 0,
            ];
            foreach ($tasks as $t) {
                $type = $t['task_type'] === 'lecture' ? 'video' : $t['task_type'];
                if (isset($counts[$type])) {
                    $counts[$type]++;
                }
                if (isset($counts[$t['status']])) {
                    $counts[$t['status']]++;
                }
            }
            $out[] = [
                'plan_date' => $day['plan_date'],
                'day_number' => (int) $day['day_number'],
                'day_status' => $day['day_status'],
                'completed_pct' => (float) $day['completed_pct'],
                'counts' => $counts,
                'task_count' => count($tasks),
            ];
        }
        return ['month' => $month, 'days' => $out];
    }

    public function dayDetails(int $planId, string $date): array
    {
        return [
            'date' => $date,
            'day' => $this->repo->getDay($planId, $date),
            'tasks' => $this->repo->tasksForDate($planId, $date),
        ];
    }
}
