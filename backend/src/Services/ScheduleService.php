<?php
namespace App\Services;

use App\Repositories\BatchRepository;
use App\Repositories\ScheduleRepository;

class ScheduleService
{
    public function __construct(
        private ScheduleRepository $schedules,
        private BatchRepository $batches,
        private NotificationService $notifier
    ) {}

    /**
     * Expand a recurrence definition into concrete dates (Y-m-d strings).
     *
     * $recurrence:
     *   mode: single|daily|weekly|monthly
     *   start_date: Y-m-d (required)
     *   until_date: Y-m-d (required for daily/weekly/monthly)
     *   interval: int (every N days/weeks/months, default 1)
     *   days_of_week: int[] 0=Sun..6=Sat (weekly only; defaults to start_date's weekday)
     *
     * @return string[] ordered unique dates
     */
    public function expandDates(array $recurrence): array
    {
        $mode = $recurrence['mode'] ?? 'single';
        $start = $recurrence['start_date'];
        $interval = max(1, (int) ($recurrence['interval'] ?? 1));

        if ($mode === 'single') {
            return [$start];
        }

        $until = $recurrence['until_date'] ?? $start;
        $startTs = strtotime($start);
        $untilTs = strtotime($until);
        if ($untilTs < $startTs) {
            return [$start];
        }

        // Hard cap to avoid runaway generation.
        $maxDays = 366 * 2;
        $dates = [];

        if ($mode === 'daily') {
            for ($ts = $startTs, $i = 0; $ts <= $untilTs && $i < $maxDays; $i++) {
                $dates[] = date('Y-m-d', $ts);
                $ts = strtotime("+{$interval} day", $ts);
            }
        } elseif ($mode === 'weekly') {
            $dows = $recurrence['days_of_week'] ?? [(int) date('w', $startTs)];
            $dows = array_map('intval', (array) $dows);
            // Iterate day by day; include matching weekdays, respecting week interval.
            $startWeek = (int) date('oW', $startTs);
            for ($ts = $startTs, $i = 0; $ts <= $untilTs && $i < $maxDays; $i++, $ts = strtotime('+1 day', $ts)) {
                $dow = (int) date('w', $ts);
                if (!in_array($dow, $dows, true)) continue;
                $weeksSince = intdiv((int) round(($ts - $startTs) / 604800), 1);
                // week-interval check
                if ($interval > 1) {
                    $weekNo = (int) floor(($ts - strtotime('sunday last week', $startTs)) / 604800);
                    if ($weekNo % $interval !== 0) continue;
                }
                $dates[] = date('Y-m-d', $ts);
            }
        } elseif ($mode === 'monthly') {
            $dayOfMonth = (int) ($recurrence['day_of_month'] ?? date('j', $startTs));
            $cursor = strtotime(date('Y-m-01', $startTs));
            for ($i = 0; $i < 60; $i++) {
                $y = (int) date('Y', $cursor);
                $m = (int) date('n', $cursor);
                $dim = (int) date('t', $cursor);
                $day = min($dayOfMonth, $dim);
                $date = sprintf('%04d-%02d-%02d', $y, $m, $day);
                $dts = strtotime($date);
                if ($dts >= $startTs && $dts <= $untilTs) {
                    $dates[] = $date;
                }
                $cursor = strtotime("+{$interval} month", $cursor);
                if ($cursor > strtotime('+1 day', $untilTs)) break;
            }
        }

        $dates = array_values(array_unique($dates));
        sort($dates);
        return $dates;
    }

    public function computeDuration(string $start, string $end): ?int
    {
        $s = strtotime($start);
        $e = strtotime($end);
        if ($s === false || $e === false || $e <= $s) return null;
        return (int) round(($e - $s) / 60);
    }

    /** Notify the relevant students that class(es) were scheduled. */
    public function notifyScheduled(int $courseId, ?int $batchId, string $courseTitle, string $lectureTitle, string $whenLabel, ?int $scheduleId = null, int $count = 1): void
    {
        $studentIds = $batchId ? $this->batches->getStudentIds($batchId) : $this->schedules->enrolledStudentIds($courseId);
        if (!$studentIds) return;

        $title = $count > 1 ? "{$count} classes scheduled" : 'New class scheduled';
        $message = $count > 1
            ? "{$count} new classes have been scheduled for {$courseTitle}."
            : "\"{$lectureTitle}\" for {$courseTitle} is scheduled on {$whenLabel}.";

        $this->notifier->notifyMany(
            $studentIds,
            'class_scheduled',
            $title,
            $message,
            ['course_id' => $courseId, 'schedule_id' => $scheduleId]
        );
    }
}
