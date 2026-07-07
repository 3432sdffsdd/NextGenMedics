<?php
namespace App\Repositories;

class DashboardRepository extends BaseRepository
{
    public function getAdminStats(): array
    {
        return [
            'total_students'        => $this->scalar('SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = "student" AND u.deleted_at IS NULL'),
            'total_teachers'        => $this->scalar('SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = "teacher" AND u.deleted_at IS NULL'),
            'total_courses'         => $this->scalar('SELECT COUNT(*) FROM courses WHERE deleted_at IS NULL'),
            'active_courses'        => $this->scalar("SELECT COUNT(*) FROM courses WHERE status = 'published' AND deleted_at IS NULL"),
            'pending_assignments'   => $this->scalar("SELECT COUNT(*) FROM assignment_submissions WHERE status = 'submitted'"),
            'pending_quiz_reviews'  => $this->scalar("SELECT COUNT(*) FROM quiz_attempts WHERE status = 'submitted' AND score IS NULL"),
            'total_enrollments'     => $this->scalar('SELECT COUNT(*) FROM course_enrollments WHERE status = "active"'),
        ];
    }

    public function getRecentActivities(int $limit = 15): array
    {
        $stmt = $this->db->prepare(
            'SELECT al.*, CONCAT(u.first_name, " ", u.last_name) AS user_name
             FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getTeacherStats(int $teacherId): array
    {
        $courses = $this->scalar(
            'SELECT COUNT(*) FROM courses c
             WHERE c.deleted_at IS NULL
               AND (c.teacher_id = ? OR EXISTS (
                    SELECT 1 FROM course_teachers ct WHERE ct.course_id = c.id AND ct.teacher_id = ?
               ))',
            [$teacherId, $teacherId]
        );

        $students = $this->scalar(
            'SELECT COUNT(DISTINCT ce.student_id) FROM course_enrollments ce
             JOIN courses c ON c.id = ce.course_id
             LEFT JOIN course_teachers ct ON ct.course_id = c.id
             WHERE (c.teacher_id = ? OR ct.teacher_id = ?) AND ce.status = "active"',
            [$teacherId, $teacherId]
        );

        return [
            'assigned_courses'     => $courses,
            'total_students'       => $students,
            'pending_assignments'  => $this->scalar(
                "SELECT COUNT(*) FROM assignment_submissions s
                 JOIN assignments a ON a.id = s.assignment_id
                 WHERE a.teacher_id = ? AND s.status = 'submitted'",
                [$teacherId]
            ),
            'pending_quiz_reviews' => $this->scalar(
                "SELECT COUNT(*) FROM quiz_attempts qa
                 JOIN quizzes q ON q.id = qa.quiz_id
                 WHERE q.teacher_id = ? AND qa.status = 'submitted' AND qa.score IS NULL",
                [$teacherId]
            ),
        ];
    }

    public function getStudentStats(int $studentId): array
    {
        return [
            'enrolled_courses' => $this->scalar(
                'SELECT COUNT(*) FROM course_enrollments WHERE student_id = ? AND status = "active"',
                [$studentId]
            ),
            'completed_courses' => $this->scalar(
                'SELECT COUNT(*) FROM course_enrollments WHERE student_id = ? AND status = "completed"',
                [$studentId]
            ),
            'pending_assignments' => $this->scalar(
                "SELECT COUNT(*) FROM assignments a
                 JOIN course_enrollments ce ON ce.course_id = a.course_id AND ce.student_id = ?
                 LEFT JOIN assignment_submissions s ON s.assignment_id = a.id AND s.student_id = ?
                 WHERE a.status = 'published' AND s.id IS NULL AND a.due_date > NOW()",
                [$studentId, $studentId]
            ),
            'upcoming_quizzes' => $this->scalar(
                "SELECT COUNT(*) FROM quizzes q
                 JOIN course_enrollments ce ON ce.course_id = q.course_id AND ce.student_id = ?
                 WHERE q.status = 'published' AND (q.available_until IS NULL OR q.available_until > NOW())",
                [$studentId]
            ),
            'certificates' => $this->scalar(
                'SELECT COUNT(*) FROM certificates WHERE student_id = ?',
                [$studentId]
            ),
            'unread_notifications' => $this->scalar(
                'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0',
                [$studentId]
            ),
        ];
    }

    private function scalar(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
