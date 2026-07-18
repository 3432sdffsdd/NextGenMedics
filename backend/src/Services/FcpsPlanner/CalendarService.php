<?php
namespace App\Services\FcpsPlanner;

/**
 * Pure PHP calendar + workload balancer for FCPS Part 1 study plans.
 */
class CalendarService
{
    private const DAY_MAP = [
        'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
        'thursday' => 4, 'friday' => 5, 'saturday' => 6,
    ];

    private const TIME_LABELS = [
        'morning'   => ['06:00–08:00', '08:00–10:00', '10:00–12:00'],
        'afternoon' => ['13:00–15:00', '15:00–17:00', '17:00–19:00'],
        'evening'   => ['18:00–20:00', '20:00–22:00', '16:00–18:00'],
        'night'     => ['21:00–23:00', '22:00–00:00', '20:00–22:00'],
    ];

    public function calculate(array $input): array
    {
        $start = new \DateTimeImmutable($input['start_date']);
        $exam  = new \DateTimeImmutable($input['exam_date']);
        if ($exam < $start) {
            throw new \InvalidArgumentException('Exam date must be on or after the study start date.');
        }

        $preferredNums = [];
        foreach (array_map('strtolower', $input['preferred_days'] ?? []) as $d) {
            if (isset(self::DAY_MAP[$d])) {
                $preferredNums[] = self::DAY_MAP[$d];
            }
        }
        if (!$preferredNums) {
            $preferredNums = [1, 2, 3, 4, 5, 6];
        }

        $today = new \DateTimeImmutable('today');
        $cursor = $start < $today ? $today : $start;
        $remainingCalendar = $exam < $today ? 0 : (int) $today->diff($exam)->days;

        $studyDates = [];
        for ($d = $cursor; $d <= $exam; $d = $d->modify('+1 day')) {
            if (in_array((int) $d->format('w'), $preferredNums, true)) {
                $studyDates[] = $d->format('Y-m-d');
            }
        }

        $hours = (float) $input['hours_per_day'];
        $sessions = max(1, min(3, (int) $input['sessions_per_day']));
        $available = count($studyDates);

        return [
            'remaining_calendar_days' => $remainingCalendar,
            'available_study_days'    => $available,
            'total_study_hours'       => round($available * $hours, 1),
            'hours_per_session'       => round($hours / max(1, $sessions), 2),
            'study_dates'             => $studyDates,
            'sessions_per_day'        => $sessions,
            'preferred_time'          => $input['preferred_time'] ?? 'evening',
            'exam_date'               => $exam->format('Y-m-d'),
            'start_date'              => $start->format('Y-m-d'),
        ];
    }

    /** Weak first, then neutral remaining, strong last. */
    public function subjectOrder(array $input): array
    {
        $remaining = array_values(array_unique($input['subjects_remaining'] ?? []));
        $weak = array_values(array_intersect($input['subjects_weak'] ?? [], $remaining));
        $strong = array_values(array_intersect($input['subjects_strong'] ?? [], $remaining));
        $mid = [];
        foreach ($remaining as $s) {
            if (!in_array($s, $weak, true) && !in_array($s, $strong, true)) {
                $mid[] = $s;
            }
        }
        $order = array_values(array_unique(array_merge($weak, $mid, $strong)));
        return $order ?: ['General Revision'];
    }

    /**
     * @return array{days:list<array>,tasks:list<array>,sessions:list<array>,weekly_goals:list<array>,monthly_goals:list<array>,strategy_notes:string}
     */
    public function buildSchedule(array $input, array $calc): array
    {
        $order = $this->subjectOrder($input);
        $weak = $input['subjects_weak'] ?? [];
        $strong = $input['subjects_strong'] ?? [];
        $dates = $calc['study_dates'];
        $sessionsPerDay = (int) $calc['sessions_per_day'];
        $minutes = (int) max(30, round(($calc['hours_per_session'] ?? 1) * 60));
        $mcq = (int) $input['daily_mcq_target'];
        $fc = (int) $input['daily_flashcard_target'];
        $revPref = $input['revision_preference'] ?? 'every_7_days';
        $labels = self::TIME_LABELS[$calc['preferred_time'] ?? 'evening'] ?? self::TIME_LABELS['evening'];
        $n = max(1, count($dates));

        $days = [];
        $tasks = [];
        $sessions = [];
        $lastNew = null;
        $prevPrimary = null;

        foreach ($dates as $i => $date) {
            $dow = (int) (new \DateTimeImmutable($date))->format('w');
            $primaryIdx = (int) floor($i * count($order) / $n);
            $primary = $order[min(count($order) - 1, $primaryIdx)];

            // Spread difficult (weak) subjects — avoid stacking two weak on same day when possible
            $daySubjects = [$primary];
            if ($sessionsPerDay >= 2) {
                $second = $order[min(count($order) - 1, $primaryIdx + 1)] ?? $primary;
                if (in_array($primary, $weak, true) && in_array($second, $weak, true) && $strong) {
                    $second = $strong[$i % count($strong)];
                } elseif ($primary === $second && $strong) {
                    $second = $strong[$i % count($strong)];
                }
                $daySubjects[] = $second;
            }
            if ($sessionsPerDay >= 3) {
                $third = $strong[$i % max(1, count($strong))] ?? ($order[0] ?? $primary);
                if ($third === $primary && count($order) > 1) {
                    $third = $order[($primaryIdx + 2) % count($order)];
                }
                $daySubjects[] = $third;
            }

            if ($primary !== $prevPrimary) {
                $lastNew = $primary;
                $prevPrimary = $primary;
            }

            $revision = $this->revisionForDay($date, $i, $dow, $revPref, $order, $lastNew, $primary);
            $topics = array_values(array_unique($daySubjects));
            $weekNum = (int) ceil(($i + 1) / 7);

            $day = [
                'plan_date'        => $date,
                'is_study_day'     => 1,
                'day_status'       => 'upcoming',
                'topics'           => $topics,
                'mcq_target'       => $mcq,
                'flashcard_target' => $fc,
                'revision_subject' => $revision,
                'weekly_goal'      => "Week {$weekNum}: progress " . implode(', ', array_slice($topics, 0, 2)),
                'completed_pct'    => 0,
            ];

            $daySessions = [];
            $dayTasks = [];
            foreach ($daySubjects as $sn => $subj) {
                $isWeak = in_array($subj, $weak, true);
                $daySessions[] = [
                    'session_number'   => $sn + 1,
                    'time_label'       => $labels[$sn] ?? ('Session ' . ($sn + 1)),
                    'subject'          => $subj,
                    'focus'            => $isWeak ? 'Weak-area deep study' : 'High-yield concepts',
                    'duration_minutes' => $minutes,
                    'status'           => 'pending',
                    'plan_date'        => $date,
                ];
                $dayTasks[] = [
                    'plan_date' => $date, 'task_type' => 'study', 'subject' => $subj,
                    'title' => 'Study: ' . $subj, 'session_number' => $sn + 1,
                    'target_count' => null, 'status' => 'pending', 'sort_order' => ($sn + 1) * 10,
                ];
            }
            $dayTasks[] = [
                'plan_date' => $date, 'task_type' => 'mcq', 'subject' => $primary,
                'title' => "MCQs: {$mcq} ({$primary})", 'session_number' => 1,
                'target_count' => $mcq, 'status' => 'pending', 'sort_order' => 80,
            ];
            $dayTasks[] = [
                'plan_date' => $date, 'task_type' => 'flashcard', 'subject' => $primary,
                'title' => "Flashcards: {$fc}", 'session_number' => 1,
                'target_count' => $fc, 'status' => 'pending', 'sort_order' => 90,
            ];
            if ($revision) {
                $dayTasks[] = [
                    'plan_date' => $date, 'task_type' => 'revision', 'subject' => $revision,
                    'title' => 'Revision: ' . $revision, 'session_number' => 1,
                    'target_count' => null, 'status' => 'pending', 'sort_order' => 100,
                ];
            }

            $days[] = $day;
            foreach ($dayTasks as $t) {
                $tasks[] = $t;
            }
            foreach ($daySessions as $s) {
                $sessions[] = $s;
            }
        }

        $weekly = [];
        $chunks = array_chunk($dates, 7);
        foreach ($chunks as $wi => $chunk) {
            $subjSlice = array_slice($order, (int) floor($wi * count($order) / max(1, count($chunks))), max(1, (int) ceil(count($order) / max(1, count($chunks)))));
            $weekly[] = [
                'week' => $wi + 1,
                'from' => $chunk[0],
                'to' => $chunk[count($chunk) - 1],
                'focus' => implode(', ', $subjSlice),
                'milestone' => 'Complete scheduled sessions + MCQ/flashcard targets for week ' . ($wi + 1),
            ];
        }

        $monthly = [];
        $byMonth = [];
        foreach ($dates as $date) {
            $byMonth[substr($date, 0, 7)][] = $date;
        }
        $mi = 1;
        foreach ($byMonth as $ym => $mdates) {
            $monthly[] = [
                'month' => $mi,
                'label' => $ym,
                'study_days' => count($mdates),
                'goal' => 'Stay on schedule for ' . count($mdates) . ' study days in ' . $ym,
            ];
            $mi++;
        }

        $notes = 'Weak subjects scheduled earlier; strong subjects later. Difficult topics are interleaved so no single day is overloaded. Revision follows your selected cadence.';

        return [
            'days'           => $days,
            'tasks'          => $tasks,
            'sessions'       => $sessions,
            'weekly_goals'   => $weekly,
            'monthly_goals'  => $monthly,
            'strategy_notes' => $notes,
            'subject_order'  => $order,
        ];
    }

    /**
     * @param list<array> $missedTasks
     * @param list<string> $upcomingDates
     * @return list<array>
     */
    public function redistribute(array $missedTasks, array $upcomingDates, int $maxExtraPerDay = 2): array
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
                if ($load < $maxExtraPerDay) {
                    $target = $d;
                    break;
                }
            }
            if ($target === null) {
                $target = array_key_first($loads) ?: $upcomingDates[0];
            }
            $loads[$target] = ($loads[$target] ?? 0) + 1;
            $task['plan_date'] = $target;
            $task['status'] = 'pending';
            $moved[] = $task;
        }
        return $moved;
    }

    private function revisionForDay(
        string $date,
        int $index,
        int $dow,
        string $pref,
        array $order,
        ?string $lastNew,
        string $primary
    ): ?string {
        $revOf = static function (int $back) use ($order, $index, $primary): string {
            $idx = max(0, min(count($order) - 1, (int) floor($index * count($order) / max(1, count($order))) - $back));
            return $order[$idx] ?? $primary;
        };

        return match ($pref) {
            'every_3_days' => $index > 0 && $index % 3 === 0 ? $revOf(1) : null,
            'every_5_days' => $index > 0 && $index % 5 === 0 ? $revOf(1) : null,
            'every_7_days' => $index > 0 && $index % 7 === 0 ? $revOf(2) : null,
            'every_sunday' => $dow === 0 ? $revOf(1) : null,
            'after_each_subject' => ($index > 0 && $lastNew) ? $lastNew : null,
            default => $index > 0 && $index % 7 === 0 ? $revOf(1) : null,
        };
    }
}
