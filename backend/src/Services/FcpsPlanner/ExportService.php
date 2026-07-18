<?php
namespace App\Services\FcpsPlanner;

use App\Repositories\FcpsStudyPlannerRepository;

class ExportService
{
    public function __construct(
        private FcpsStudyPlannerRepository $repo,
        private StatisticsService $stats
    ) {}

    public function payload(int $planId, int $studentId): array
    {
        $plan = $this->repo->getPlanById($planId, $studentId);
        if (!$plan) {
            throw new \RuntimeException('Plan not found');
        }
        $days = $this->repo->allDays($planId);
        $tasks = $this->repo->allTasks($planId);
        $byDate = [];
        foreach ($tasks as $t) {
            $byDate[$t['plan_date']][] = $t;
        }
        $calendar = [];
        foreach ($days as $d) {
            $dayTasks = $byDate[$d['plan_date']] ?? [];
            $calendar[] = [
                'plan_date'        => $d['plan_date'],
                'day_status'       => $d['day_status'],
                'topics'           => json_decode($d['topics'] ?? '[]', true) ?: [],
                'mcq_target'       => (int) $d['mcq_target'],
                'flashcard_target' => (int) $d['flashcard_target'],
                'revision_subject' => $d['revision_subject'],
                'completed_pct'    => (float) $d['completed_pct'],
                'tasks'            => $dayTasks,
            ];
        }
        return [
            'plan'        => $this->publicPlan($plan),
            'calendar'    => $calendar,
            'dashboard'   => $this->stats->dashboard($planId, $studentId),
            'exported_at' => date('c'),
        ];
    }

    public function csv(int $planId, int $studentId): string
    {
        $data = $this->payload($planId, $studentId);
        $lines = ['Date,Status,Topics,MCQs,Flashcards,Revision,Completion%'];
        foreach ($data['calendar'] as $d) {
            $lines[] = sprintf(
                '%s,%s,"%s",%d,%d,"%s",%s',
                $d['plan_date'],
                $d['day_status'],
                implode('; ', $d['topics']),
                $d['mcq_target'],
                $d['flashcard_target'],
                $d['revision_subject'] ?? '',
                $d['completed_pct']
            );
        }
        return implode("\n", $lines);
    }

    public function printHtml(array $payload): string
    {
        $plan = $payload['plan'];
        $rows = '';
        foreach ($payload['calendar'] as $d) {
            $rows .= '<tr><td>' . htmlspecialchars($d['plan_date']) . '</td><td>'
                . htmlspecialchars(implode(', ', $d['topics'])) . '</td><td>'
                . (int) $d['mcq_target'] . '</td><td>' . (int) $d['flashcard_target'] . '</td><td>'
                . htmlspecialchars((string) ($d['revision_subject'] ?? '—')) . '</td></tr>';
        }
        $exam = htmlspecialchars($plan['exam_date']);
        $notes = htmlspecialchars((string) ($plan['strategy_notes'] ?? ''));
        return <<<HTML
<!DOCTYPE html><html><head><meta charset="utf-8"><title>FCPS Study Plan</title>
<style>body{font-family:Segoe UI,sans-serif;padding:24px;color:#0f172a}table{width:100%;border-collapse:collapse;margin-top:16px}
th,td{border:1px solid #e2e8f0;padding:8px;font-size:12px}th{background:#f8fafc}@media print{button{display:none}}</style>
</head><body>
<button onclick="window.print()">Print / Save as PDF</button>
<h1>FCPS Part 1 Study Plan</h1>
<p><strong>Exam:</strong> {$exam} · <strong>Hours/day:</strong> {$plan['hours_per_day']}</p>
<p>{$notes}</p>
<table><thead><tr><th>Date</th><th>Topics</th><th>MCQs</th><th>Flashcards</th><th>Revision</th></tr></thead>
<tbody>{$rows}</tbody></table>
</body></html>
HTML;
    }

    private function publicPlan(array $plan): array
    {
        return [
            'id' => (int) $plan['id'],
            'exam_date' => $plan['exam_date'],
            'start_date' => $plan['start_date'],
            'hours_per_day' => (float) $plan['hours_per_day'],
            'preferred_days' => json_decode($plan['preferred_days'] ?? '[]', true) ?: [],
            'sessions_per_day' => (int) $plan['sessions_per_day'],
            'preferred_time' => $plan['preferred_time'],
            'subjects_completed' => json_decode($plan['subjects_completed'] ?? '[]', true) ?: [],
            'subjects_remaining' => json_decode($plan['subjects_remaining'] ?? '[]', true) ?: [],
            'subjects_weak' => json_decode($plan['subjects_weak'] ?? '[]', true) ?: [],
            'subjects_strong' => json_decode($plan['subjects_strong'] ?? '[]', true) ?: [],
            'subject_order' => json_decode($plan['subject_order'] ?? '[]', true) ?: [],
            'daily_mcq_target' => (int) $plan['daily_mcq_target'],
            'daily_flashcard_target' => (int) $plan['daily_flashcard_target'],
            'revision_preference' => $plan['revision_preference'],
            'weekly_goals' => json_decode($plan['weekly_goals'] ?? '[]', true) ?: [],
            'monthly_goals' => json_decode($plan['monthly_goals'] ?? '[]', true) ?: [],
            'strategy_notes' => $plan['strategy_notes'],
            'completion_pct' => (float) $plan['completion_pct'],
            'streak_days' => (int) $plan['streak_days'],
        ];
    }
}
