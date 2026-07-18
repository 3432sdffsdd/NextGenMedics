<?php
namespace App\Services\FcpsPlanner;

use App\Repositories\FcpsStudyPlannerRepository;

class ProgressService
{
    public function __construct(private FcpsStudyPlannerRepository $repo) {}

    public function syncAfterTaskChange(int $planId, int $studentId, string $date, int $dayId): void
    {
        $this->repo->refreshDayCompletion($dayId);
        $this->repo->rebuildSubjectProgress($planId, $studentId);
        $tasks = $this->repo->tasksForDate($planId, $date);
        $done = array_filter($tasks, static fn($t) => $t['status'] === 'completed');
        $mcq = 0;
        $fc = 0;
        foreach ($done as $t) {
            if ($t['task_type'] === 'mcq') {
                $mcq += (int) ($t['target_count'] ?? 0);
            }
            if ($t['task_type'] === 'flashcard') {
                $fc += (int) ($t['target_count'] ?? 0);
            }
        }
        $this->repo->upsertDailyStat($planId, $studentId, $date, [
            'tasks_total'     => count($tasks),
            'tasks_completed' => count($done),
            'mcqs_done'       => $mcq,
            'flashcards_done' => $fc,
            'study_minutes'   => count(array_filter($done, static fn($t) => $t['task_type'] === 'study')) * 60,
        ]);
        $this->evaluateBadges($planId, $studentId);
    }

    public function evaluateBadges(int $planId, int $studentId): void
    {
        $all = $this->repo->allTasks($planId);
        $done = count(array_filter($all, static fn($t) => $t['status'] === 'completed'));
        if ($done >= 1) {
            $this->repo->awardBadge($studentId, $planId, 'first_task', 'First Step', 'Completed your first study task.');
        }
        if ($done >= 25) {
            $this->repo->awardBadge($studentId, $planId, 'task_25', 'Consistent Learner', 'Completed 25 tasks.');
        }
        if ($done >= 100) {
            $this->repo->awardBadge($studentId, $planId, 'task_100', 'Century Club', 'Completed 100 tasks.');
        }
        $streak = $this->computeStreak($planId, date('Y-m-d'));
        if ($streak >= 3) {
            $this->repo->awardBadge($studentId, $planId, 'streak_3', '3-Day Streak', 'Studied 3 days in a row.');
        }
        if ($streak >= 7) {
            $this->repo->awardBadge($studentId, $planId, 'streak_7', 'Week Warrior', 'Maintained a 7-day study streak.');
        }
    }

    public function computeStreak(int $planId, string $today): int
    {
        $streak = 0;
        $d = new \DateTimeImmutable($today);
        $todayTasks = $this->repo->tasksForDate($planId, $today);
        $start = ($todayTasks && $this->dayDone($todayTasks)) ? $d : $d->modify('-1 day');
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
}
