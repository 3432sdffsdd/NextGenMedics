<?php
namespace App\Services\GoalPlanner;

/**
 * Distributes selected LMS content items across available study days (PHP only).
 */
class ScheduleGeneratorService
{
    private const DAY_MAP = [
        'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
        'thursday' => 4, 'friday' => 5, 'saturday' => 6,
    ];

    public function studyDates(array $input): array
    {
        $start = new \DateTimeImmutable($input['start_date']);
        $end = new \DateTimeImmutable($input['target_date']);
        if ($end < $start) {
            throw new \InvalidArgumentException('Target date must be on or after start date.');
        }
        $preferred = [];
        foreach (array_map('strtolower', $input['preferred_days'] ?? []) as $d) {
            if (isset(self::DAY_MAP[$d])) {
                $preferred[] = self::DAY_MAP[$d];
            }
        }
        if (!$preferred) {
            $preferred = [1, 2, 3, 4, 5, 6];
        }
        $today = new \DateTimeImmutable('today');
        $cursor = $start < $today ? $today : $start;
        $dates = [];
        for ($d = $cursor; $d <= $end; $d = $d->modify('+1 day')) {
            if (in_array((int) $d->format('w'), $preferred, true)) {
                $dates[] = $d->format('Y-m-d');
            }
        }
        return $dates;
    }

    /**
     * @param list<array> $items from ContentCatalogService::resolveItems
     * @return array{days:list<array>,tasks:list<array>}
     */
    public function build(array $items, array $input, array $studyDates): array
    {
        if (!$studyDates) {
            throw new \InvalidArgumentException('No available study days in the selected range.');
        }
        if (!$items) {
            throw new \InvalidArgumentException('No content selected for this goal.');
        }

        // Prefer lectures/videos earlier; quizzes mid; flashcards/revision interleaved
        usort($items, static function ($a, $b) {
            $w = ['lecture' => 1, 'video' => 2, 'note' => 3, 'quiz' => 4, 'flashcard_set' => 5, 'revision' => 6];
            return ($w[$a['item_type']] ?? 9) <=> ($w[$b['item_type']] ?? 9);
        });

        $sessions = max(1, min(3, (int) ($input['sessions_per_day'] ?? 2)));
        $hours = (float) ($input['hours_per_day'] ?? 3);
        $dayBudgetMin = (int) max(30, round($hours * 60));
        $mcqTarget = (int) ($input['daily_mcq_target'] ?? 40);
        $fcTarget = (int) ($input['daily_flashcard_target'] ?? 30);
        $revPref = $input['revision_preference'] ?? 'every_7_days';

        $nDays = count($studyDates);
        $chunks = array_fill(0, $nDays, []);
        $loads = array_fill(0, $nDays, 0);

        foreach ($items as $item) {
            $mins = (int) ($item['estimated_minutes'] ?? 40);
            // pick least-loaded day that won't exceed budget too badly
            $best = 0;
            $bestLoad = PHP_INT_MAX;
            for ($i = 0; $i < $nDays; $i++) {
                if ($loads[$i] < $bestLoad && (count($chunks[$i]) < $sessions + 2 || $loads[$i] + $mins <= $dayBudgetMin + 30)) {
                    $bestLoad = $loads[$i];
                    $best = $i;
                }
            }
            $chunks[$best][] = $item;
            $loads[$best] += $mins;
        }

        $days = [];
        $tasks = [];
        foreach ($studyDates as $i => $date) {
            $dow = (int) (new \DateTimeImmutable($date))->format('w');
            $days[] = [
                'plan_date' => $date,
                'is_study_day' => 1,
                'day_status' => 'upcoming',
                'completed_pct' => 0,
            ];
            $sort = 0;
            foreach ($chunks[$i] as $item) {
                $taskType = match ($item['item_type']) {
                    'flashcard_set' => 'flashcard',
                    default => $item['item_type'],
                };
                $tasks[] = [
                    'plan_date'     => $date,
                    'plan_item_key' => $item['item_type'] . ':' . $item['ref_id'],
                    'task_type'     => $taskType,
                    'ref_id'        => $item['ref_id'],
                    'course_id'     => $item['course_id'],
                    'lecture_id'    => $item['lecture_id'],
                    'subject_title' => $item['subject_title'],
                    'title'         => $item['title'],
                    'target_count'  => $taskType === 'flashcard' ? $fcTarget : ($taskType === 'quiz' ? null : null),
                    'status'        => 'pending',
                    'sort_order'    => ++$sort,
                    '_item'         => $item,
                ];
            }
            // Daily practice targets
            if ($mcqTarget > 0) {
                $tasks[] = [
                    'plan_date' => $date, 'plan_item_key' => null, 'task_type' => 'mcq_practice',
                    'ref_id' => null, 'course_id' => null, 'lecture_id' => null,
                    'subject_title' => null, 'title' => "Practice {$mcqTarget} MCQs",
                    'target_count' => $mcqTarget, 'status' => 'pending', 'sort_order' => 90, '_item' => null,
                ];
            }
            if ($this->needsRevisionSlot($i, $dow, $revPref) && !empty($chunks[$i])) {
                $subj = $chunks[$i][0]['subject_title'] ?? 'General';
                $tasks[] = [
                    'plan_date' => $date, 'plan_item_key' => null, 'task_type' => 'revision',
                    'ref_id' => null, 'course_id' => null, 'lecture_id' => null,
                    'subject_title' => $subj, 'title' => "Spaced revision: {$subj}",
                    'target_count' => null, 'status' => 'pending', 'sort_order' => 100, '_item' => null,
                ];
            }
        }

        return ['days' => $days, 'tasks' => $tasks, 'study_dates' => $studyDates];
    }

    public function redistribute(array $missedTasks, array $upcomingDates, int $maxExtra = 2): array
    {
        if (!$missedTasks || !$upcomingDates) {
            return [];
        }
        $loads = array_fill_keys($upcomingDates, 0);
        $moved = [];
        foreach ($missedTasks as $task) {
            asort($loads);
            $target = null;
            foreach ($loads as $d => $load) {
                if ($load < $maxExtra) {
                    $target = $d;
                    break;
                }
            }
            $target = $target ?? array_key_first($loads);
            $loads[$target]++;
            $task['plan_date'] = $target;
            $task['status'] = 'pending';
            $moved[] = $task;
        }
        return $moved;
    }

    private function needsRevisionSlot(int $index, int $dow, string $pref): bool
    {
        return match ($pref) {
            'every_3_days' => $index > 0 && $index % 3 === 0,
            'every_5_days' => $index > 0 && $index % 5 === 0,
            'every_7_days' => $index > 0 && $index % 7 === 0,
            'every_sunday' => $dow === 0,
            'after_each_subject' => $index > 0 && $index % 2 === 0,
            default => $index > 0 && $index % 7 === 0,
        };
    }
}
