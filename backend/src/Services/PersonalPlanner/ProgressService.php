<?php
namespace App\Services\PersonalPlanner;

use App\Repositories\PersonalStudyPlannerRepository;

class ProgressService
{
    public function __construct(private PersonalStudyPlannerRepository $repo) {}

    public function rebuild(int $planId, int $studentId): array
    {
        $types = [
            'video' => ['videos_completed', 'videos_total'],
            'lecture' => null, // folded into video counts below
            'quiz' => ['quizzes_completed', 'quizzes_total'],
            'flashcard' => ['flashcards_completed', 'flashcards_total'],
            'note' => ['notes_completed', 'notes_total'],
            'revision' => ['revision_completed', 'revision_total'],
            'manual' => ['manual_completed', 'manual_total'],
            'mcq' => null,
        ];

        $p = [
            'videos_completed' => $this->repo->countTypeStatus($planId, 'video', 'completed')
                + $this->repo->countTypeStatus($planId, 'lecture', 'completed'),
            'videos_total' => $this->repo->countType($planId, 'video') + $this->repo->countType($planId, 'lecture'),
            'quizzes_completed' => $this->repo->countTypeStatus($planId, 'quiz', 'completed'),
            'quizzes_total' => $this->repo->countType($planId, 'quiz'),
            'flashcards_completed' => $this->repo->countTypeStatus($planId, 'flashcard', 'completed'),
            'flashcards_total' => $this->repo->countType($planId, 'flashcard'),
            'notes_completed' => $this->repo->countTypeStatus($planId, 'note', 'completed'),
            'notes_total' => $this->repo->countType($planId, 'note'),
            'revision_completed' => $this->repo->countTypeStatus($planId, 'revision', 'completed'),
            'revision_total' => $this->repo->countType($planId, 'revision'),
            'manual_completed' => $this->repo->countTypeStatus($planId, 'manual', 'completed'),
            'manual_total' => $this->repo->countType($planId, 'manual'),
            'overall_pct' => 0,
            'streak_days' => $this->streak($planId),
        ];

        $all = $this->repo->allTasks($planId);
        $total = count($all);
        $done = count(array_filter($all, static fn($t) => ($t['status'] ?? '') === 'completed'));
        $p['overall_pct'] = $total > 0 ? round(($done / $total) * 100, 2) : 0;

        $this->repo->upsertProgress($planId, $studentId, $p);
        $this->repo->updatePlan($planId, ['completion_pct' => $p['overall_pct']]);

        if ($total > 0 && $done === $total) {
            $this->repo->updatePlan($planId, ['status' => 'completed']);
        }

        return $p;
    }

    public function syncDayStat(int $planId, int $studentId, string $date): void
    {
        $tasks = $this->repo->tasksForDate($planId, $date);
        $completed = 0;
        $pending = 0;
        $skipped = 0;
        $minutes = 0;
        $videos = 0;
        $quizzes = 0;
        $flash = 0;
        $manual = 0;
        foreach ($tasks as $t) {
            if ($t['status'] === 'completed') {
                $completed++;
                $minutes += (int) ($t['estimated_minutes'] ?? 0);
                if (in_array($t['task_type'], ['video', 'lecture'], true)) {
                    $videos++;
                }
                if ($t['task_type'] === 'quiz') {
                    $quizzes++;
                }
                if ($t['task_type'] === 'flashcard') {
                    $flash++;
                }
                if ($t['task_type'] === 'manual') {
                    $manual++;
                }
            } elseif ($t['status'] === 'skipped') {
                $skipped++;
            } else {
                $pending++;
            }
        }
        $this->repo->upsertStat($studentId, $date, [
            'tasks_completed' => $completed,
            'tasks_pending' => $pending,
            'tasks_skipped' => $skipped,
            'study_minutes' => $minutes,
            'videos_watched' => $videos,
            'quizzes_attempted' => $quizzes,
            'flashcards_reviewed' => $flash,
            'manual_completed' => $manual,
        ]);
    }

    public function periodPct(int $planId, string $from, string $to): float
    {
        $tasks = $this->repo->tasksBetween($planId, $from, $to);
        if (!$tasks) {
            return 0.0;
        }
        $done = count(array_filter($tasks, static fn($t) => $t['status'] === 'completed'));
        return round(($done / count($tasks)) * 100, 1);
    }

    private function streak(int $planId): int
    {
        $today = new \DateTimeImmutable('today');
        $streak = 0;
        for ($i = 0; $i < 60; $i++) {
            $date = $today->modify("-{$i} days")->format('Y-m-d');
            $tasks = $this->repo->tasksForDate($planId, $date);
            if (!$tasks) {
                if ($i === 0) {
                    continue;
                }
                break;
            }
            $anyDone = false;
            foreach ($tasks as $t) {
                if ($t['status'] === 'completed') {
                    $anyDone = true;
                    break;
                }
            }
            if (!$anyDone) {
                if ($i === 0) {
                    continue;
                }
                break;
            }
            $streak++;
        }
        return $streak;
    }
}
