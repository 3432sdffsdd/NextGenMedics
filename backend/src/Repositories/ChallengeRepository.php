<?php
namespace App\Repositories;

class ChallengeRepository extends BaseRepository
{
    public function findByLecture(int $lectureId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM daily_challenges WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Enabled, started challenges for a set of courses (student's enrolled courses). */
    public function enabledForCourses(array $courseIds): array
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT dc.*, l.title AS lecture_title, c.title AS course_title
             FROM daily_challenges dc
             JOIN lectures l ON l.id = dc.lecture_id
             JOIN ai_lecture_content ac ON ac.lecture_id = dc.lecture_id AND ac.status = 'published'
             LEFT JOIN courses c ON c.id = dc.course_id
             WHERE dc.enabled = 1 AND dc.course_id IN ({$in})
               AND (dc.start_date IS NULL OR dc.start_date <= CURDATE())
             ORDER BY dc.start_date DESC"
        );
        $stmt->execute($courseIds);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM daily_challenges WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function upsert(int $lectureId, ?int $courseId, ?int $userId, array $data): void
    {
        $existing = $this->findByLecture($lectureId);
        if ($existing) {
            $stmt = $this->db->prepare(
                'UPDATE daily_challenges SET enabled = ?, mcqs_per_day = ?, start_date = ? WHERE lecture_id = ?'
            );
            $stmt->execute([
                (int) ($data['enabled'] ?? 0),
                max(1, (int) ($data['mcqs_per_day'] ?? 10)),
                $data['start_date'] ?? null,
                $lectureId,
            ]);
            return;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO daily_challenges (lecture_id, course_id, enabled, mcqs_per_day, start_date, created_by)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([
            $lectureId, $courseId,
            (int) ($data['enabled'] ?? 0),
            max(1, (int) ($data['mcqs_per_day'] ?? 10)),
            $data['start_date'] ?? date('Y-m-d'),
            $userId,
        ]);
    }
}
