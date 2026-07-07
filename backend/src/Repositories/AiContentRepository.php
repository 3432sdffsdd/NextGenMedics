<?php
namespace App\Repositories;

class AiContentRepository extends BaseRepository
{
    private const JSON_FIELDS = [
        'high_yield_points', 'clinical_pearls', 'common_mistakes',
        'key_definitions', 'memory_tricks', 'key_takeaways',
    ];

    public function findByLecture(int $lectureId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ai_lecture_content WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /** Create the row for a lecture if absent; returns its id. */
    public function ensure(int $lectureId, ?int $courseId, ?int $userId): int
    {
        $existing = $this->findByLecture($lectureId);
        if ($existing) {
            return (int) $existing['id'];
        }
        $stmt = $this->db->prepare(
            'INSERT INTO ai_lecture_content (lecture_id, course_id, generated_by) VALUES (?,?,?)'
        );
        $stmt->execute([$lectureId, $courseId, $userId]);
        return (int) $this->db->lastInsertId();
    }

    /** Update arbitrary content fields for a lecture (JSON fields auto-encoded). */
    public function updateByLecture(int $lectureId, array $fields): void
    {
        if (!$fields) {
            return;
        }
        $sets = [];
        $vals = [];
        foreach ($fields as $col => $val) {
            if (in_array($col, self::JSON_FIELDS, true)) {
                $val = json_encode(array_values((array) $val), JSON_UNESCAPED_UNICODE);
            }
            $sets[] = "{$col} = ?";
            $vals[] = $val;
        }
        $vals[] = $lectureId;
        $stmt = $this->db->prepare('UPDATE ai_lecture_content SET ' . implode(', ', $sets) . ' WHERE lecture_id = ?');
        $stmt->execute($vals);
    }

    public function setStatus(int $lectureId, string $status, ?int $userId = null): void
    {
        if ($status === 'approved') {
            $stmt = $this->db->prepare(
                'UPDATE ai_lecture_content SET status = ?, approved_by = ?, approved_at = NOW() WHERE lecture_id = ?'
            );
            $stmt->execute([$status, $userId, $lectureId]);
        } elseif ($status === 'published') {
            $stmt = $this->db->prepare(
                'UPDATE ai_lecture_content SET status = ?, published_at = NOW() WHERE lecture_id = ?'
            );
            $stmt->execute([$status, $lectureId]);
        } else {
            $stmt = $this->db->prepare('UPDATE ai_lecture_content SET status = ? WHERE lecture_id = ?');
            $stmt->execute([$status, $lectureId]);
        }
    }

    /** Lectures in a course that have published AI content (for the Revision Center). */
    public function publishedLecturesByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ac.lecture_id, l.title AS lecture_title, ac.published_at,
                    (SELECT COUNT(*) FROM flashcards f WHERE f.lecture_id = ac.lecture_id AND f.status = 'approved') AS flashcard_count,
                    (SELECT COUNT(*) FROM mcqs m WHERE m.lecture_id = ac.lecture_id AND m.status = 'published') AS mcq_count
             FROM ai_lecture_content ac
             JOIN lectures l ON l.id = ac.lecture_id
             WHERE ac.course_id = ? AND ac.status = 'published'
             ORDER BY ac.published_at DESC"
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    /** All published lectures across a set of course ids (student's enrolled courses). */
    public function publishedLecturesForCourses(array $courseIds): array
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT ac.lecture_id, ac.course_id, l.title AS lecture_title, c.title AS course_title, ac.published_at
             FROM ai_lecture_content ac
             JOIN lectures l ON l.id = ac.lecture_id
             LEFT JOIN courses c ON c.id = ac.course_id
             WHERE ac.status = 'published' AND ac.course_id IN ({$in})
             ORDER BY ac.published_at DESC"
        );
        $stmt->execute($courseIds);
        return $stmt->fetchAll();
    }

    public function isPublished(int $lectureId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ai_lecture_content WHERE lecture_id = ? AND status = 'published'");
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function hydrate(array $row): array
    {
        foreach (self::JSON_FIELDS as $f) {
            $row[$f] = !empty($row[$f]) ? (json_decode($row[$f], true) ?: []) : [];
        }
        return $row;
    }
}
