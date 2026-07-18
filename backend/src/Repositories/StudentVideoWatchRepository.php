<?php
namespace App\Repositories;

/**
 * Student-marked watch status for lecture video resources.
 */
class StudentVideoWatchRepository extends BaseRepository
{
    /** @return array{total:int,watched:int,unwatched:int} */
    public function summaryForStudent(int $studentId, array $courseIds): array
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return ['total' => 0, 'watched' => 0, 'unwatched' => 0];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN w.watched = 1 THEN 1 ELSE 0 END) AS watched
             FROM lecture_resources lr
             JOIN lectures l ON l.id = lr.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules mo ON mo.id = ch.module_id
             LEFT JOIN student_video_watches w
               ON w.resource_id = lr.id AND w.student_id = ?
             WHERE lr.type = 'video' AND mo.course_id IN ({$in})"
        );
        $stmt->execute(array_merge([$studentId], $courseIds));
        $row = $stmt->fetch() ?: ['total' => 0, 'watched' => 0];
        $total = (int) $row['total'];
        $watched = (int) ($row['watched'] ?? 0);
        return [
            'total'     => $total,
            'watched'   => $watched,
            'unwatched' => max(0, $total - $watched),
        ];
    }

    /**
     * @return list<array>
     */
    public function listVideos(int $studentId, array $courseIds, array $filters = []): array
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $where = ["lr.type = 'video'", "mo.course_id IN ({$in})"];
        $params = array_merge([$studentId], $courseIds);

        if (!empty($filters['course_id'])) {
            $where[] = 'mo.course_id = ?';
            $params[] = (int) $filters['course_id'];
        }
        if (!empty($filters['topic'])) {
            $where[] = 'l.title = ?';
            $params[] = $filters['topic'];
        }
        if (!empty($filters['lecture_id'])) {
            $where[] = 'l.id = ?';
            $params[] = (int) $filters['lecture_id'];
        }
        if (($filters['watch_status'] ?? '') === 'watched') {
            $where[] = 'w.watched = 1';
        } elseif (($filters['watch_status'] ?? '') === 'unwatched') {
            $where[] = '(w.id IS NULL OR w.watched = 0)';
        }

        $sqlWhere = implode(' AND ', $where);
        $stmt = $this->db->prepare(
            "SELECT lr.id, lr.title, lr.file_path, lr.external_url, lr.mime_type, lr.file_size,
                    lr.lecture_id, lr.type, lr.created_at,
                    l.title AS topic, l.title AS lecture_title,
                    ch.title AS chapter_title, mo.title AS module_title,
                    c.id AS course_id, c.title AS course_title,
                    CASE WHEN w.watched = 1 THEN 1 ELSE 0 END AS watched,
                    w.watched_at
             FROM lecture_resources lr
             JOIN lectures l ON l.id = lr.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules mo ON mo.id = ch.module_id
             JOIN courses c ON c.id = mo.course_id
             LEFT JOIN student_video_watches w
               ON w.resource_id = lr.id AND w.student_id = ?
             WHERE {$sqlWhere}
             ORDER BY c.title ASC, mo.sort_order ASC, ch.sort_order ASC, l.sort_order ASC, lr.sort_order ASC, lr.id ASC"
        );
        $stmt->execute($params);
        return array_map(static function ($r) {
            $r['id'] = (int) $r['id'];
            $r['lecture_id'] = (int) $r['lecture_id'];
            $r['course_id'] = (int) $r['course_id'];
            $r['watched'] = (int) $r['watched'] === 1;
            return $r;
        }, $stmt->fetchAll());
    }

    /** Distinct lecture titles (topics) that have videos. */
    public function topicsForStudent(array $courseIds): array
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT DISTINCT l.title AS topic
             FROM lecture_resources lr
             JOIN lectures l ON l.id = lr.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules mo ON mo.id = ch.module_id
             WHERE lr.type = 'video' AND mo.course_id IN ({$in})
             ORDER BY l.title ASC"
        );
        $stmt->execute($courseIds);
        return array_column($stmt->fetchAll(), 'topic');
    }

    /** @return list<int> watched resource ids for student among given ids */
    public function watchedIds(int $studentId, array $resourceIds = []): array
    {
        if ($resourceIds) {
            $resourceIds = array_values(array_filter(array_map('intval', $resourceIds)));
            if (!$resourceIds) {
                return [];
            }
            $in = implode(',', array_fill(0, count($resourceIds), '?'));
            $stmt = $this->db->prepare(
                "SELECT resource_id FROM student_video_watches
                 WHERE student_id = ? AND watched = 1 AND resource_id IN ({$in})"
            );
            $stmt->execute(array_merge([$studentId], $resourceIds));
        } else {
            $stmt = $this->db->prepare(
                'SELECT resource_id FROM student_video_watches WHERE student_id = ? AND watched = 1'
            );
            $stmt->execute([$studentId]);
        }
        return array_map('intval', array_column($stmt->fetchAll(), 'resource_id'));
    }

    public function setWatched(int $studentId, int $resourceId, bool $watched): void
    {
        if ($watched) {
            $stmt = $this->db->prepare(
                'INSERT INTO student_video_watches (student_id, resource_id, watched, watched_at)
                 VALUES (?,?,1,NOW())
                 ON DUPLICATE KEY UPDATE watched = 1, watched_at = NOW()'
            );
            $stmt->execute([$studentId, $resourceId]);
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO student_video_watches (student_id, resource_id, watched, watched_at)
                 VALUES (?,?,0,NULL)
                 ON DUPLICATE KEY UPDATE watched = 0, watched_at = NULL'
            );
            $stmt->execute([$studentId, $resourceId]);
        }
    }

    public function resourceBelongsToStudentCourses(int $resourceId, array $courseIds): bool
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return false;
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM lecture_resources lr
             JOIN lectures l ON l.id = lr.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules mo ON mo.id = ch.module_id
             WHERE lr.id = ? AND lr.type = 'video' AND mo.course_id IN ({$in})"
        );
        $stmt->execute(array_merge([$resourceId], $courseIds));
        return (int) $stmt->fetchColumn() > 0;
    }
}
