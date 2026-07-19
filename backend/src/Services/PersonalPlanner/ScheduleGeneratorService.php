<?php
namespace App\Services\PersonalPlanner;

/**
 * Evenly distributes LMS items + manual tasks across study days (no AI).
 */
class ScheduleGeneratorService
{
    /**
     * @param list<array> $lmsItems
     * @param list<array{day_number?:int,title:string,description?:string}> $manualByDay
     * @return array{days:list<array>,tasks:list<array>}
     */
    public function build(array $lmsItems, array $manualByDay, int $durationDays, string $startDate, array $preferredDays = []): array
    {
        $dates = $this->studyDates($startDate, $durationDays, $preferredDays);
        if (!$dates) {
            throw new \InvalidArgumentException('No study days available for the selected duration.');
        }

        $dayMap = [];
        foreach ($dates as $i => $date) {
            $dayMap[] = [
                'day_number' => $i + 1,
                'plan_date' => $date,
            ];
        }

        $tasks = [];
        $sort = 0;

        // Round-robin LMS content to avoid overload
        $buckets = array_fill(0, count($dayMap), []);
        foreach (array_values($lmsItems) as $idx => $item) {
            $buckets[$idx % count($dayMap)][] = $item;
        }

        foreach ($dayMap as $i => $day) {
            foreach ($buckets[$i] as $item) {
                $type = $item['item_type'] === 'flashcard_set' ? 'flashcard' : $item['item_type'];
                if ($type === 'lecture') {
                    $type = 'video'; // treat lecture watch as video in UI buckets
                }
                $tasks[] = [
                    'plan_date' => $day['plan_date'],
                    'day_number' => $day['day_number'],
                    'source' => 'lms',
                    'task_type' => $type,
                    'ref_id' => $item['ref_id'] ?? null,
                    'course_id' => $item['course_id'] ?? null,
                    'lecture_id' => $item['lecture_id'] ?? null,
                    'subject_title' => $item['subject_title'] ?? null,
                    'title' => $item['title'],
                    'estimated_minutes' => (int) ($item['estimated_minutes'] ?? 30),
                    'sort_order' => $sort++,
                ];
            }
        }

        // Manual tasks: either assigned to day_number or spread evenly
        $unassigned = [];
        foreach ($manualByDay as $m) {
            $dn = (int) ($m['day_number'] ?? 0);
            if ($dn >= 1 && $dn <= count($dayMap)) {
                $day = $dayMap[$dn - 1];
                $tasks[] = $this->manualTask($day, $m, $sort++);
            } else {
                $unassigned[] = $m;
            }
        }
        foreach ($unassigned as $idx => $m) {
            $day = $dayMap[$idx % count($dayMap)];
            $tasks[] = $this->manualTask($day, $m, $sort++);
        }

        return ['days' => $dayMap, 'tasks' => $tasks];
    }

    private function manualTask(array $day, array $m, int $sort): array
    {
        return [
            'plan_date' => $day['plan_date'],
            'day_number' => $day['day_number'],
            'source' => 'manual',
            'task_type' => 'manual',
            'ref_id' => null,
            'course_id' => null,
            'lecture_id' => null,
            'subject_title' => $m['subject_title'] ?? 'Personal',
            'title' => trim((string) ($m['title'] ?? 'Study task')),
            'description' => $m['description'] ?? null,
            'estimated_minutes' => (int) ($m['estimated_minutes'] ?? 45),
            'sort_order' => $sort,
        ];
    }

    /** @return list<string> */
    public function studyDates(string $startDate, int $durationDays, array $preferredDays = []): array
    {
        $preferred = array_map('strtolower', $preferredDays ?: [
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
        ]);
        $map = [
            'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
            'thursday' => 4, 'friday' => 5, 'saturday' => 6,
        ];
        $allowed = [];
        foreach ($preferred as $d) {
            if (isset($map[$d])) {
                $allowed[$map[$d]] = true;
            }
        }
        if (!$allowed) {
            $allowed = array_fill(0, 7, true);
        }

        $dates = [];
        $cursor = new \DateTimeImmutable($startDate);
        $guard = 0;
        while (count($dates) < $durationDays && $guard < 400) {
            $dow = (int) $cursor->format('w');
            if (isset($allowed[$dow])) {
                $dates[] = $cursor->format('Y-m-d');
            }
            $cursor = $cursor->modify('+1 day');
            $guard++;
        }
        return $dates;
    }
}
