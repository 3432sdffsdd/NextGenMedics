<?php
namespace App\Repositories;

class LiveSessionRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, c.title AS course_title,
                    CONCAT(t.first_name, " ", t.last_name) AS teacher_name
             FROM live_sessions s
             JOIN courses c ON c.id = s.course_id
             JOIN users t ON t.id = s.teacher_id
             WHERE s.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, CONCAT(t.first_name, " ", t.last_name) AS teacher_name
             FROM live_sessions s
             JOIN users t ON t.id = s.teacher_id
             WHERE s.course_id = ? ORDER BY s.scheduled_at ASC'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function listForUser(array $user, ?string $from = null, ?string $to = null): array
    {
        $params = [];
        $where = 's.status != "cancelled"';

        if ($from) {
            $where .= ' AND s.scheduled_at >= ?';
            $params[] = $from;
        }
        if ($to) {
            $where .= ' AND s.scheduled_at <= ?';
            $params[] = $to;
        }

        $role = $user['role'];
        $userId = (int) $user['id'];

        if ($role === 'teacher') {
            $where .= ' AND (s.teacher_id = ? OR c.teacher_id = ? OR ct.teacher_id = ?)';
            $params = array_merge($params, [$userId, $userId, $userId]);
        } elseif ($role === 'student') {
            $where .= ' AND ce.student_id = ? AND ce.status = "active"';
            $params[] = $userId;
        }

        $joins = 'FROM live_sessions s
                  JOIN courses c ON c.id = s.course_id
                  JOIN users t ON t.id = s.teacher_id';

        if ($role === 'teacher') {
            $joins .= ' LEFT JOIN course_teachers ct ON ct.course_id = c.id';
        } elseif ($role === 'student') {
            $joins .= ' JOIN course_enrollments ce ON ce.course_id = c.id';
        }

        $sql = "SELECT s.*, c.title AS course_title,
                       CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
                {$joins}
                WHERE {$where}
                ORDER BY s.scheduled_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO live_sessions (course_id, teacher_id, title, description, meeting_url, scheduled_at, duration_minutes, status)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['course_id'], $data['teacher_id'], $data['title'],
            $data['description'] ?? null, $data['meeting_url'] ?? null,
            $data['scheduled_at'], $data['duration_minutes'] ?? 60,
            $data['status'] ?? 'scheduled',
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
        $stmt = $this->db->prepare('UPDATE live_sessions SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM live_sessions WHERE id = ?')->execute([$id]);
    }

    public function getEnrolledStudentIds(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT student_id FROM course_enrollments WHERE course_id = ? AND status = "active"'
        );
        $stmt->execute([$courseId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }
}
