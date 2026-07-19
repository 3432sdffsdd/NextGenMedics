<?php
namespace App\Repositories;

class VideoTrackingRepository extends BaseRepository
{
    public function resourceContext(int $resourceId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT lr.id AS resource_id, lr.lecture_id, lr.title AS video_title, lr.type, lr.file_size,
                    l.title AS lecture_title, c.id AS course_id, c.title AS course_title
             FROM lecture_resources lr
             JOIN lectures l ON l.id = lr.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules m ON m.id = ch.module_id
             JOIN courses c ON c.id = m.course_id
             WHERE lr.id = ? AND lr.type = 'video'
             LIMIT 1"
        );
        $stmt->execute([$resourceId]);
        return $stmt->fetch() ?: null;
    }

    public function getProgress(int $studentId, int $resourceId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM vt_video_progress WHERE student_id = ? AND resource_id = ? LIMIT 1'
        );
        $stmt->execute([$studentId, $resourceId]);
        return $stmt->fetch() ?: null;
    }

    public function upsertProgress(int $studentId, int $resourceId, array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO vt_video_progress
             (student_id, resource_id, lecture_id, course_id, duration_seconds, watched_seconds,
              max_position, last_position, completion_pct, status, play_count, replay_count,
              pause_count, seek_forward_count, seek_backward_count, playback_speed,
              device_type, browser, os_name, ip_address, first_watched_at, last_watched_at, completed_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
              lecture_id=VALUES(lecture_id), course_id=VALUES(course_id),
              duration_seconds=VALUES(duration_seconds),
              watched_seconds=VALUES(watched_seconds),
              max_position=VALUES(max_position),
              last_position=VALUES(last_position),
              completion_pct=VALUES(completion_pct),
              status=VALUES(status),
              play_count=VALUES(play_count),
              replay_count=VALUES(replay_count),
              pause_count=VALUES(pause_count),
              seek_forward_count=VALUES(seek_forward_count),
              seek_backward_count=VALUES(seek_backward_count),
              playback_speed=VALUES(playback_speed),
              device_type=COALESCE(VALUES(device_type), device_type),
              browser=COALESCE(VALUES(browser), browser),
              os_name=COALESCE(VALUES(os_name), os_name),
              ip_address=COALESCE(VALUES(ip_address), ip_address),
              first_watched_at=COALESCE(first_watched_at, VALUES(first_watched_at)),
              last_watched_at=VALUES(last_watched_at),
              completed_at=VALUES(completed_at)'
        );
        $stmt->execute([
            $studentId, $resourceId,
            $data['lecture_id'], $data['course_id'],
            $data['duration_seconds'], $data['watched_seconds'],
            $data['max_position'], $data['last_position'],
            $data['completion_pct'], $data['status'],
            $data['play_count'], $data['replay_count'],
            $data['pause_count'], $data['seek_forward_count'], $data['seek_backward_count'],
            $data['playback_speed'],
            $data['device_type'], $data['browser'], $data['os_name'], $data['ip_address'],
            $data['first_watched_at'], $data['last_watched_at'], $data['completed_at'],
        ]);
    }

    public function insertEvent(array $e): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO vt_video_events
             (student_id, resource_id, lecture_id, course_id, event_type, position_seconds,
              duration_seconds, watched_delta, playback_speed, meta_json)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $e['student_id'], $e['resource_id'], $e['lecture_id'], $e['course_id'],
            $e['event_type'], $e['position_seconds'], $e['duration_seconds'],
            $e['watched_delta'], $e['playback_speed'],
            $e['meta_json'] ? json_encode($e['meta_json']) : null,
        ]);
    }

    public function addSegment(int $studentId, int $resourceId, float $start, float $end): void
    {
        if ($end <= $start) {
            return;
        }
        $this->db->prepare(
            'INSERT INTO vt_watch_segments (student_id, resource_id, start_pos, end_pos) VALUES (?,?,?,?)'
        )->execute([$studentId, $resourceId, $start, $end]);
    }

    /** Merge overlapping segments to estimate unique watched seconds. */
    public function uniqueWatchedSeconds(int $studentId, int $resourceId): float
    {
        $stmt = $this->db->prepare(
            'SELECT start_pos, end_pos FROM vt_watch_segments
             WHERE student_id = ? AND resource_id = ? ORDER BY start_pos ASC'
        );
        $stmt->execute([$studentId, $resourceId]);
        $rows = $stmt->fetchAll();
        if (!$rows) {
            return 0.0;
        }
        $merged = [];
        foreach ($rows as $r) {
            $s = (float) $r['start_pos'];
            $e = (float) $r['end_pos'];
            if (!$merged) {
                $merged[] = [$s, $e];
                continue;
            }
            $last = &$merged[count($merged) - 1];
            if ($s <= $last[1] + 0.5) {
                $last[1] = max($last[1], $e);
            } else {
                $merged[] = [$s, $e];
            }
        }
        $total = 0.0;
        foreach ($merged as [$s, $e]) {
            $total += max(0, $e - $s);
        }
        return round($total, 2);
    }

    public function listStudentVideos(int $studentId, array $courseIds, array $filters = []): array
    {
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $where = ["lr.type = 'video'", "c.id IN ({$in})"];
        if (!empty($filters['course_id'])) {
            $where[] = 'c.id = ?';
            $params[] = (int) $filters['course_id'];
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'not_started') {
                $where[] = "(p.id IS NULL OR p.status = 'not_started')";
            } else {
                $where[] = 'p.status = ?';
                $params[] = $filters['status'];
            }
        }
        $sql = "SELECT lr.id AS resource_id, lr.title AS video_title, lr.file_size, lr.file_path,
                       l.id AS lecture_id, l.title AS lecture_title,
                       c.id AS course_id, c.title AS course_title,
                       COALESCE(p.duration_seconds, 0) AS duration_seconds,
                       COALESCE(p.watched_seconds, 0) AS watched_seconds,
                       COALESCE(p.last_position, 0) AS last_position,
                       COALESCE(p.max_position, 0) AS max_position,
                       COALESCE(p.completion_pct, 0) AS completion_pct,
                       COALESCE(p.status, 'not_started') AS status,
                       p.play_count, p.replay_count, p.pause_count,
                       p.seek_forward_count, p.seek_backward_count,
                       p.last_watched_at, p.completed_at, p.first_watched_at
                FROM lecture_resources lr
                JOIN lectures l ON l.id = lr.lecture_id
                JOIN chapters ch ON ch.id = l.chapter_id
                JOIN modules m ON m.id = ch.module_id
                JOIN courses c ON c.id = m.course_id
                LEFT JOIN vt_video_progress p ON p.resource_id = lr.id AND p.student_id = ?
                WHERE " . implode(' AND ', $where) . "
                ORDER BY c.title, m.sort_order, ch.sort_order, l.sort_order, lr.sort_order, lr.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function studentSummary(int $studentId, array $courseIds): array
    {
        $videos = $this->listStudentVideos($studentId, $courseIds);
        $total = count($videos);
        $completed = 0;
        $watching = 0;
        $notStarted = 0;
        $sumPct = 0.0;
        $sumWatch = 0.0;
        foreach ($videos as $v) {
            $sumPct += (float) $v['completion_pct'];
            $sumWatch += (float) $v['watched_seconds'];
            if ($v['status'] === 'completed') {
                $completed++;
            } elseif ($v['status'] === 'watching') {
                $watching++;
            } else {
                $notStarted++;
            }
        }
        return [
            'total_videos' => $total,
            'completed' => $completed,
            'watching' => $watching,
            'not_started' => $notStarted,
            'average_watch_pct' => $total ? round($sumPct / $total, 1) : 0,
            'total_watch_seconds' => round($sumWatch, 1),
            'total_watch_hours' => round($sumWatch / 3600, 2),
        ];
    }

    public function subjectProgress(int $studentId, array $courseIds): array
    {
        if (!$courseIds) {
            return [];
        }
        $videos = $this->listStudentVideos($studentId, $courseIds);
        $by = [];
        foreach ($videos as $v) {
            $cid = (int) $v['course_id'];
            if (!isset($by[$cid])) {
                $by[$cid] = [
                    'course_id' => $cid,
                    'subject_name' => $v['course_title'],
                    'total_videos' => 0,
                    'completed_videos' => 0,
                    'remaining_videos' => 0,
                    'sum_pct' => 0.0,
                ];
            }
            $by[$cid]['total_videos']++;
            $by[$cid]['sum_pct'] += (float) $v['completion_pct'];
            if ($v['status'] === 'completed') {
                $by[$cid]['completed_videos']++;
            }
        }
        $out = [];
        foreach ($by as $row) {
            $t = $row['total_videos'];
            $out[] = [
                'course_id' => $row['course_id'],
                'subject_name' => $row['subject_name'],
                'total_videos' => $t,
                'completed_videos' => $row['completed_videos'],
                'remaining_videos' => max(0, $t - $row['completed_videos']),
                'completion_pct' => $t ? round(($row['completed_videos'] / $t) * 100, 1) : 0,
                'average_watch_pct' => $t ? round($row['sum_pct'] / $t, 1) : 0,
            ];
        }
        return $out;
    }

    public function timeline(int $studentId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT e.event_type, e.position_seconds, e.created_at, e.resource_id,
                    lr.title AS video_title, l.title AS lecture_title
             FROM vt_video_events e
             JOIN lecture_resources lr ON lr.id = e.resource_id
             LEFT JOIN lectures l ON l.id = e.lecture_id
             WHERE e.student_id = ?
             ORDER BY e.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $studentId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function teacherCanAccessCourse(int $teacherId, int $courseId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM courses c
             LEFT JOIN course_teachers ct ON ct.course_id = c.id AND ct.teacher_id = ?
             WHERE c.id = ? AND (c.teacher_id = ? OR ct.teacher_id = ?)
             LIMIT 1'
        );
        $stmt->execute([$teacherId, $courseId, $teacherId, $teacherId]);
        return (bool) $stmt->fetchColumn();
    }

    public function courseStudentIds(int $courseId): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.first_name, u.last_name, u.email
             FROM course_enrollments ce
             JOIN users u ON u.id = ce.student_id
             WHERE ce.course_id = ? AND ce.status = 'active' AND u.deleted_at IS NULL
             ORDER BY u.first_name, u.last_name"
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function classReport(int $courseId): array
    {
        $students = $this->courseStudentIds($courseId);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM lecture_resources lr
             JOIN lectures l ON l.id = lr.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules m ON m.id = ch.module_id
             WHERE m.course_id = ? AND lr.type = 'video'"
        );
        $stmt->execute([$courseId]);
        $totalVideos = (int) $stmt->fetchColumn();

        $rows = [];
        foreach ($students as $s) {
            $sid = (int) $s['id'];
            $sum = $this->studentSummary($sid, [$courseId]);
            $rows[] = [
                'student_id' => $sid,
                'student_name' => trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')),
                'email' => $s['email'],
                'total_videos' => $totalVideos,
                'videos_completed' => $sum['completed'],
                'videos_remaining' => max(0, $totalVideos - $sum['completed']),
                'lecture_completion_pct' => $totalVideos ? round(($sum['completed'] / $totalVideos) * 100, 1) : 0,
                'average_watch_pct' => $sum['average_watch_pct'],
                'study_hours' => $sum['total_watch_hours'],
                'watching' => $sum['watching'],
                'not_started' => $sum['not_started'],
            ];
        }
        usort($rows, static fn($a, $b) => $b['lecture_completion_pct'] <=> $a['lecture_completion_pct']);
        foreach ($rows as $i => &$r) {
            $r['rank'] = $i + 1;
        }
        return $rows;
    }

    public function studentLectureDetails(int $studentId, int $courseId): array
    {
        return $this->listStudentVideos($studentId, [$courseId]);
    }

    public function analyticsForCourse(int $courseId): array
    {
        $videos = [];
        $stmt = $this->db->prepare(
            "SELECT lr.id, lr.title, l.title AS lecture_title
             FROM lecture_resources lr
             JOIN lectures l ON l.id = lr.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules m ON m.id = ch.module_id
             WHERE m.course_id = ? AND lr.type = 'video'"
        );
        $stmt->execute([$courseId]);
        $all = $stmt->fetchAll();
        $students = $this->courseStudentIds($courseId);
        $studentCount = max(1, count($students));

        $mostReplay = null;
        $leastWatch = null;
        $mostPopular = null;
        $neverOpened = 0;
        $partial = 0;
        $fully = 0;
        $sumPct = 0;
        $sumTime = 0;
        $pctCount = 0;

        foreach ($all as $v) {
            $rid = (int) $v['id'];
            $pstmt = $this->db->prepare(
                "SELECT
                    COUNT(*) AS openers,
                    AVG(completion_pct) AS avg_pct,
                    AVG(watched_seconds) AS avg_watch,
                    SUM(replay_count) AS replays,
                    SUM(status='completed') AS completed_n,
                    SUM(status='watching') AS watching_n
                 FROM vt_video_progress WHERE resource_id = ?"
            );
            $pstmt->execute([$rid]);
            $stats = $pstmt->fetch() ?: [];
            $openers = (int) ($stats['openers'] ?? 0);
            $avgPct = round((float) ($stats['avg_pct'] ?? 0), 1);
            $avgWatch = round((float) ($stats['avg_watch'] ?? 0), 1);
            $replays = (int) ($stats['replays'] ?? 0);
            $row = [
                'resource_id' => $rid,
                'title' => $v['title'],
                'lecture_title' => $v['lecture_title'],
                'openers' => $openers,
                'average_watch_pct' => $avgPct,
                'average_watch_seconds' => $avgWatch,
                'replays' => $replays,
                'completed_students' => (int) ($stats['completed_n'] ?? 0),
                'watching_students' => (int) ($stats['watching_n'] ?? 0),
                'never_opened_students' => max(0, count($students) - $openers),
            ];
            $videos[] = $row;
            if ($openers === 0) {
                $neverOpened++;
            }
            $partial += (int) ($stats['watching_n'] ?? 0);
            $fully += (int) ($stats['completed_n'] ?? 0);
            if ($openers > 0) {
                $sumPct += $avgPct;
                $sumTime += $avgWatch;
                $pctCount++;
            }
            if ($mostReplay === null || $replays > $mostReplay['replays']) {
                $mostReplay = $row;
            }
            if ($leastWatch === null || $avgPct < $leastWatch['average_watch_pct']) {
                $leastWatch = $row;
            }
            if ($mostPopular === null || $openers > $mostPopular['openers']) {
                $mostPopular = $row;
            }
        }

        return [
            'total_videos' => count($all),
            'enrolled_students' => count($students),
            'average_watch_percentage' => $pctCount ? round($sumPct / $pctCount, 1) : 0,
            'average_viewing_time_seconds' => $pctCount ? round($sumTime / $pctCount, 1) : 0,
            'most_popular_lecture' => $mostPopular,
            'least_watched_lecture' => $leastWatch,
            'most_replayed_lecture' => $mostReplay,
            'videos_never_opened' => $neverOpened,
            'videos_partially_watched_instances' => $partial,
            'videos_fully_completed_instances' => $fully,
            'videos' => $videos,
        ];
    }

    public function markManualWatchCompat(int $studentId, int $resourceId, bool $watched): void
    {
        if ($watched) {
            $this->db->prepare(
                'INSERT INTO student_video_watches (student_id, resource_id, watched, watched_at)
                 VALUES (?,?,1,NOW())
                 ON DUPLICATE KEY UPDATE watched = 1, watched_at = COALESCE(watched_at, NOW())'
            )->execute([$studentId, $resourceId]);
        }
    }

    public function syncPlannerVideoTasks(int $studentId, int $resourceId): void
    {
        try {
            $this->db->prepare(
                "UPDATE psp_plan_tasks t
                 JOIN psp_plans p ON p.id = t.plan_id
                 SET t.status = 'completed', t.completed_at = NOW()
                 WHERE p.student_id = ? AND p.status = 'active'
                   AND t.task_type IN ('video','lecture')
                   AND t.ref_id = ? AND t.status <> 'completed'"
            )->execute([$studentId, $resourceId]);
        } catch (\Throwable $e) {
            // Planner tables may be absent in some envs — ignore
        }
    }
}
