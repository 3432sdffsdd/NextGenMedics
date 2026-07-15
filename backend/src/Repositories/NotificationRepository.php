<?php
namespace App\Repositories;

class NotificationRepository extends BaseRepository
{
    public function create(int $userId, string $type, string $title, string $message, ?array $data = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO notifications (user_id, type, title, message, data) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([$userId, $type, $title, $message, $data ? json_encode($data) : null]);
        return (int) $this->db->lastInsertId();
    }

    public function listForUser(int $userId, int $page, int $perPage, ?bool $unreadOnly = null): array
    {
        $params = [$userId];
        $where = 'user_id = ?';
        if ($unreadOnly) {
            $where .= ' AND is_read = 0';
        }
        $sql = "SELECT * FROM notifications WHERE {$where} ORDER BY created_at DESC";
        return $this->paginate($sql, $params, $page, $perPage);
    }

    public function markRead(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    public function markAllRead(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0'
        );
        return $stmt->execute([$userId]);
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /** Tab → notification types, by role. */
    public static function tabTypesForRole(string $role): array
    {
        if (in_array($role, ['teacher', 'admin'], true)) {
            return [
                'assignments' => ['assignment_submitted'],
                'quizzes'     => ['quiz_submitted'],
                'discussions' => ['discussion_question', 'discussion_reply'],
            ];
        }
        return [
            'learn'       => ['new_content', 'ai_content_published', 'material_uploaded'],
            'assignments' => ['new_assignment', 'assignment_graded'],
            'quizzes'     => ['new_quiz'],
            'discussions' => ['new_discussion', 'discussion_reply'],
        ];
    }

    /** Unread counts for course hub tabs (student or teacher). */
    public function unreadCountsForCourseTabs(int $userId, int $courseId, string $role): array
    {
        $result = [];
        foreach (self::tabTypesForRole($role) as $tab => $types) {
            $result[$tab] = $this->unreadCountForTab($userId, $courseId, $types);
        }
        return $result;
    }

    public function markReadForCourseTab(int $userId, int $courseId, string $tab, string $role): int
    {
        $map = self::tabTypesForRole($role);
        if (!isset($map[$tab])) {
            return 0;
        }
        return $this->executeMarkRead($userId, $courseId, $map[$tab]);
    }

    private function unreadCountForTab(int $userId, int $courseId, array $types): int
    {
        if (!$types) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($types), '?'));
        $params = array_merge([$userId], $types, [$courseId, $courseId, $courseId]);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notifications
             WHERE user_id = ? AND is_read = 0 AND type IN ($placeholders)
             AND (
                 CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.course_id')) AS UNSIGNED) = ?
                 OR (
                     type IN ('new_assignment', 'assignment_graded', 'assignment_submitted')
                     AND CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.assignment_id')) AS UNSIGNED) IN (
                         SELECT id FROM assignments WHERE course_id = ?
                     )
                 )
                 OR (
                     type IN ('new_quiz', 'quiz_submitted')
                     AND CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.quiz_id')) AS UNSIGNED) IN (
                         SELECT id FROM quizzes WHERE course_id = ?
                     )
                 )
             )"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function executeMarkRead(int $userId, int $courseId, array $types): int
    {
        if (!$types) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($types), '?'));
        $params = array_merge($types, [$userId, $courseId, $courseId, $courseId]);
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1, read_at = NOW()
             WHERE is_read = 0 AND type IN ($placeholders) AND user_id = ?
             AND (
                 CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.course_id')) AS UNSIGNED) = ?
                 OR (
                     type IN ('new_assignment', 'assignment_graded', 'assignment_submitted')
                     AND CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.assignment_id')) AS UNSIGNED) IN (
                         SELECT id FROM assignments WHERE course_id = ?
                     )
                 )
                 OR (
                     type IN ('new_quiz', 'quiz_submitted')
                     AND CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.quiz_id')) AS UNSIGNED) IN (
                         SELECT id FROM quizzes WHERE course_id = ?
                     )
                 )
             )"
        );
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function markEmailSent(int $id): void
    {
        $this->db->prepare('UPDATE notifications SET email_sent = 1 WHERE id = ?')->execute([$id]);
    }
}
