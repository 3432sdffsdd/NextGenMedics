<?php
namespace App\Repositories;

class ClassScheduleRepository extends BaseRepository
{
    public function listByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM course_class_schedule WHERE course_id = ? AND is_active = 1 ORDER BY day_of_week, start_time'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, c.title AS course_title, c.teacher_id
             FROM course_class_schedule s
             JOIN courses c ON c.id = s.course_id
             WHERE s.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO course_class_schedule (course_id, day_of_week, start_time, duration_minutes, title, meeting_url)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['course_id'], $data['day_of_week'], $data['start_time'],
            $data['duration_minutes'] ?? 60, $data['title'] ?? null, $data['meeting_url'] ?? null,
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
        $stmt = $this->db->prepare('UPDATE course_class_schedule SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('UPDATE course_class_schedule SET is_active = 0 WHERE id = ?')->execute([$id]);
    }

    public function replaceForCourse(int $courseId, array $slots): void
    {
        $this->db->prepare('UPDATE course_class_schedule SET is_active = 0 WHERE course_id = ?')->execute([$courseId]);
        foreach ($slots as $slot) {
            $this->create(array_merge($slot, ['course_id' => $courseId]));
        }
    }

    public function getAllActiveWithCourse(): array
    {
        return $this->db->query(
            'SELECT s.*, c.title AS course_title, c.teacher_id,
                    CONCAT(t.first_name, " ", t.last_name) AS teacher_name, t.phone AS teacher_phone, t.email AS teacher_email
             FROM course_class_schedule s
             JOIN courses c ON c.id = s.course_id AND c.deleted_at IS NULL
             JOIN users t ON t.id = c.teacher_id
             WHERE s.is_active = 1'
        )->fetchAll();
    }

    public function getEnrolledStudents(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.first_name, u.last_name, u.phone, u.email
             FROM course_enrollments ce
             JOIN users u ON u.id = ce.student_id
             WHERE ce.course_id = ? AND ce.status = "active" AND u.deleted_at IS NULL'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function reminderAlreadySent(int $scheduleId, string $date, int $userId, string $role, string $channel): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM class_reminder_log
             WHERE schedule_id = ? AND occurrence_date = ? AND user_id = ? AND role = ? AND channel = ?'
        );
        $stmt->execute([$scheduleId, $date, $userId, $role, $channel]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function logReminder(int $scheduleId, string $date, int $userId, string $role, string $channel): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO class_reminder_log (schedule_id, occurrence_date, user_id, role, channel) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([$scheduleId, $date, $userId, $role, $channel]);
    }

    public function listForUser(array $user): array
    {
        $role = $user['role'];
        $userId = (int) $user['id'];

        if ($role === 'admin') {
            return $this->db->query(
                'SELECT s.*, c.title AS course_title, CONCAT(t.first_name, " ", t.last_name) AS teacher_name
                 FROM course_class_schedule s
                 JOIN courses c ON c.id = s.course_id AND c.deleted_at IS NULL
                 LEFT JOIN users t ON t.id = c.teacher_id
                 WHERE s.is_active = 1 ORDER BY s.day_of_week, s.start_time'
            )->fetchAll();
        }

        if ($role === 'teacher') {
            $stmt = $this->db->prepare(
                'SELECT s.*, c.title AS course_title
                 FROM course_class_schedule s
                 JOIN courses c ON c.id = s.course_id AND c.deleted_at IS NULL
                 WHERE s.is_active = 1
                   AND (c.teacher_id = ? OR EXISTS (
                        SELECT 1 FROM course_teachers ct WHERE ct.course_id = c.id AND ct.teacher_id = ?
                   ))
                 ORDER BY s.day_of_week, s.start_time'
            );
            $stmt->execute([$userId, $userId]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare(
            'SELECT s.*, c.title AS course_title, CONCAT(t.first_name, " ", t.last_name) AS teacher_name
             FROM course_class_schedule s
             JOIN courses c ON c.id = s.course_id AND c.deleted_at IS NULL
             JOIN course_enrollments ce ON ce.course_id = c.id AND ce.student_id = ? AND ce.status = "active"
             LEFT JOIN users t ON t.id = c.teacher_id
             WHERE s.is_active = 1 ORDER BY s.day_of_week, s.start_time'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
