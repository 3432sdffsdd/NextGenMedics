<?php
namespace App\Repositories;

class AttendanceRepository extends BaseRepository
{
    public function createSession(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO attendance_sessions (course_id, teacher_id, session_date, title, notes) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $data['course_id'], $data['teacher_id'], $data['session_date'],
            $data['title'] ?? null, $data['notes'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findSession(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM attendance_sessions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Prevent duplicate sessions for the same course on the same date. */
    public function findSessionByCourseDate(int $courseId, string $date): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM attendance_sessions WHERE course_id = ? AND session_date = ? LIMIT 1');
        $stmt->execute([$courseId, $date]);
        return $stmt->fetch() ?: null;
    }

    public function updateSession(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE attendance_sessions SET title = ?, notes = ? WHERE id = ?');
        return $stmt->execute([$data['title'] ?? null, $data['notes'] ?? null, $id]);
    }

    public function deleteSession(int $id): bool
    {
        $this->db->prepare('DELETE FROM attendance_records WHERE session_id = ?')->execute([$id]);
        return $this->db->prepare('DELETE FROM attendance_sessions WHERE id = ?')->execute([$id]);
    }

    public function markAttendance(array $records): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO attendance_records (session_id, student_id, status, remarks, marked_by)
             VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks), updated_by = VALUES(marked_by), updated_at = NOW()'
        );
        foreach ($records as $r) {
            $stmt->execute([
                $r['session_id'], $r['student_id'], $r['status'],
                $r['remarks'] ?? null, $r['marked_by'],
            ]);
        }
    }

    public function getByCourse(int $courseId, ?string $from = null, ?string $to = null): array
    {
        $params = [$courseId];
        $where = 's.course_id = ?';
        if ($from) {
            $where .= ' AND s.session_date >= ?';
            $params[] = $from;
        }
        if ($to) {
            $where .= ' AND s.session_date <= ?';
            $params[] = $to;
        }

        $stmt = $this->db->prepare(
            "SELECT s.*, (SELECT COUNT(*) FROM attendance_records r WHERE r.session_id = s.id AND r.status = 'present') AS present_count,
                    (SELECT COUNT(*) FROM attendance_records r WHERE r.session_id = s.id) AS total_marked
             FROM attendance_sessions s WHERE {$where} ORDER BY s.session_date DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStudentAttendance(int $studentId, ?int $courseId = null): array
    {
        $params = [$studentId];
        $where = 'r.student_id = ?';
        if ($courseId) {
            $where .= ' AND s.course_id = ?';
            $params[] = $courseId;
        }

        $stmt = $this->db->prepare(
            "SELECT r.*, s.session_date, s.title AS session_title, c.title AS course_title
             FROM attendance_records r
             JOIN attendance_sessions s ON s.id = r.session_id
             JOIN courses c ON c.id = s.course_id
             WHERE {$where} ORDER BY s.session_date DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Per-course attendance summary for one student.
     * Returns [course_id => ['total' => int sessions, 'present' => int attended]].
     * "Present" counts both present and late as attended.
     */
    public function getStudentCourseAttendance(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.course_id,
                    COUNT(DISTINCT s.id) AS total_sessions,
                    COUNT(DISTINCT CASE WHEN r.status IN ('present', 'late') THEN s.id END) AS present_sessions
             FROM attendance_sessions s
             LEFT JOIN attendance_records r ON r.session_id = s.id AND r.student_id = ?
             GROUP BY s.course_id"
        );
        $stmt->execute([$studentId]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int) $row['course_id']] = [
                'total'   => (int) $row['total_sessions'],
                'present' => (int) $row['present_sessions'],
            ];
        }
        return $out;
    }

    public function getStatistics(int $courseId): array
    {
        $stmt = $this->db->prepare(
            "SELECT r.status, COUNT(*) AS count
             FROM attendance_records r
             JOIN attendance_sessions s ON s.id = r.session_id
             WHERE s.course_id = ? GROUP BY r.status"
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function getSessionRecords(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, CONCAT(u.first_name, " ", u.last_name) AS student_name
             FROM attendance_records r
             JOIN users u ON u.id = r.student_id
             WHERE r.session_id = ? ORDER BY u.last_name'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }
}
