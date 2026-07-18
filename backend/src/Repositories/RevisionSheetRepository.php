<?php
namespace App\Repositories;

class RevisionSheetRepository extends BaseRepository
{
    public function upsert(int $lectureId, ?int $courseId, ?int $userId, string $content): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO revision_sheets (lecture_id, course_id, content, status, source, created_by)
             VALUES (?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE content = VALUES(content), status = \'draft\', updated_at = NOW()'
        );
        $stmt->execute([$lectureId, $courseId, $content, 'draft', 'ai', $userId]);
    }

    public function findByLecture(int $lectureId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM revision_sheets WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function setStatusForLecture(int $lectureId, string $status): void
    {
        $this->db->prepare('UPDATE revision_sheets SET status = ? WHERE lecture_id = ?')
            ->execute([$status, $lectureId]);
    }
}
