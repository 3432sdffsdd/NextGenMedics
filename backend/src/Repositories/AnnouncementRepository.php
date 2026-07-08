<?php
namespace App\Repositories;

class AnnouncementRepository extends BaseRepository
{
    public function listPublic(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, CONCAT(u.first_name, " ", u.last_name) AS author_name, c.title AS course_title
             FROM announcements a JOIN users u ON u.id = a.author_id
             LEFT JOIN courses c ON c.id = a.course_id
             WHERE a.published_at IS NOT NULL AND (a.expires_at IS NULL OR a.expires_at > NOW())
             ORDER BY a.is_pinned DESC, a.published_at DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function listForUser(int $userId, string $role, int $page, int $perPage): array
    {
        if ($role === 'admin') {
            $sql = 'SELECT a.*, CONCAT(u.first_name, " ", u.last_name) AS author_name, c.title AS course_title
                    FROM announcements a JOIN users u ON u.id = a.author_id
                    LEFT JOIN courses c ON c.id = a.course_id ORDER BY a.created_at DESC';
            return $this->paginate($sql, [], $page, $perPage);
        }

        if ($role === 'teacher') {
            $sql = 'SELECT a.*, c.title AS course_title FROM announcements a
                    LEFT JOIN courses c ON c.id = a.course_id
                    WHERE a.author_id = ? OR c.teacher_id = ?
                    ORDER BY a.created_at DESC';
            return $this->paginate($sql, [$userId, $userId], $page, $perPage);
        }

        $sql = 'SELECT a.*, CONCAT(u.first_name, " ", u.last_name) AS author_name, c.title AS course_title
                FROM announcements a
                JOIN users u ON u.id = a.author_id
                LEFT JOIN courses c ON c.id = a.course_id
                WHERE (a.course_id IS NULL OR a.course_id IN (
                    SELECT course_id FROM course_enrollments WHERE student_id = ? AND status = "active"
                ))
                AND a.published_at IS NOT NULL
                AND a.published_at <= NOW()
                AND (a.expires_at IS NULL OR a.expires_at > NOW())
                ORDER BY a.is_pinned DESC, a.published_at DESC';
        return $this->paginate($sql, [$userId], $page, $perPage);
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO announcements (course_id, author_id, title, content, priority, is_pinned, published_at, expires_at)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['course_id'] ?? null, $data['author_id'], $data['title'], $data['content'],
            $data['priority'] ?? 'normal', $data['is_pinned'] ?? 0,
            $data['published_at'] ?? date('Y-m-d H:i:s'), $data['expires_at'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare('UPDATE announcements SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
    }
}
