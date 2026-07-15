<?php
namespace App\Repositories;

class DiscussionRepository extends BaseRepository
{
    public function listByCourse(int $courseId, int $page, int $perPage): array
    {
        $assignmentFilter = $this->columnExists('discussion_threads', 'assignment_id')
            ? ' AND t.assignment_id IS NULL'
            : '';
        $sql = 'SELECT t.*, CONCAT(u.first_name, " ", u.last_name) AS author_name, ro.slug AS author_role,
                       (SELECT COUNT(*) FROM discussion_replies r WHERE r.thread_id = t.id) AS reply_count
                FROM discussion_threads t
                JOIN users u ON u.id = t.author_id
                JOIN roles ro ON ro.id = u.role_id
                WHERE t.course_id = ? AND t.status != "hidden"' . $assignmentFilter . '
                ORDER BY t.is_pinned DESC, t.created_at DESC';
        return $this->paginate($sql, [$courseId], $page, $perPage);
    }

    /** Threads attached to a specific lecture. */
    public function listByLecture(int $lectureId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, CONCAT(u.first_name, " ", u.last_name) AS author_name, ro.slug AS author_role,
                    (SELECT COUNT(*) FROM discussion_replies r WHERE r.thread_id = t.id) AS reply_count
             FROM discussion_threads t
             JOIN users u ON u.id = t.author_id
             JOIN roles ro ON ro.id = u.role_id
             WHERE t.lecture_id = ? AND t.status != "hidden"
             ORDER BY t.is_pinned DESC, t.created_at DESC'
        );
        $stmt->execute([$lectureId]);
        return $stmt->fetchAll();
    }

    /** Threads attached to a specific assignment. */
    public function listByAssignment(int $assignmentId): array
    {
        if (!$this->columnExists('discussion_threads', 'assignment_id')) {
            return [];
        }
        $stmt = $this->db->prepare(
            'SELECT t.*, CONCAT(u.first_name, " ", u.last_name) AS author_name, ro.slug AS author_role,
                    (SELECT COUNT(*) FROM discussion_replies r WHERE r.thread_id = t.id) AS reply_count
             FROM discussion_threads t
             JOIN users u ON u.id = t.author_id
             JOIN roles ro ON ro.id = u.role_id
             WHERE t.assignment_id = ? AND t.status != "hidden"
             ORDER BY t.is_pinned DESC, t.created_at DESC'
        );
        $stmt->execute([$assignmentId]);
        return $stmt->fetchAll();
    }

    public function findThread(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, CONCAT(u.first_name, " ", u.last_name) AS author_name, ro.slug AS author_role
             FROM discussion_threads t
             JOIN users u ON u.id = t.author_id
             JOIN roles ro ON ro.id = u.role_id
             WHERE t.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Replies with author role, like count, flags, and whether the viewer liked. */
    public function getReplies(int $threadId, ?int $viewerId = null): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, CONCAT(u.first_name, " ", u.last_name) AS author_name, ro.slug AS author_role,
                    EXISTS(SELECT 1 FROM discussion_reply_likes l WHERE l.reply_id = r.id AND l.user_id = ?) AS liked_by_me
             FROM discussion_replies r
             JOIN users u ON u.id = r.author_id
             JOIN roles ro ON ro.id = u.role_id
             WHERE r.thread_id = ?
             ORDER BY r.is_pinned DESC, r.created_at ASC'
        );
        $stmt->execute([$viewerId ?? 0, $threadId]);
        return $stmt->fetchAll();
    }

    public function createThread(array $data): int
    {
        $hasLecture = $this->columnExists('discussion_threads', 'lecture_id');
        $hasAssignment = $this->columnExists('discussion_threads', 'assignment_id');

        if ($hasLecture && $hasAssignment) {
            $stmt = $this->db->prepare(
                'INSERT INTO discussion_threads (course_id, lecture_id, assignment_id, author_id, title, content)
                 VALUES (?,?,?,?,?,?)'
            );
            $stmt->execute([
                $data['course_id'],
                $data['lecture_id'] ?? null,
                $data['assignment_id'] ?? null,
                $data['author_id'],
                $data['title'],
                $data['content'],
            ]);
        } elseif ($hasLecture) {
            $stmt = $this->db->prepare(
                'INSERT INTO discussion_threads (course_id, lecture_id, author_id, title, content) VALUES (?,?,?,?,?)'
            );
            $stmt->execute([
                $data['course_id'], $data['lecture_id'] ?? null,
                $data['author_id'], $data['title'], $data['content'],
            ]);
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO discussion_threads (course_id, author_id, title, content) VALUES (?,?,?,?)'
            );
            $stmt->execute([
                $data['course_id'], $data['author_id'], $data['title'], $data['content'],
            ]);
        }
        return (int) $this->db->lastInsertId();
    }

    public function createReply(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO discussion_replies (thread_id, parent_id, author_id, content, is_answer) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $data['thread_id'], $data['parent_id'] ?? null,
            $data['author_id'], $data['content'], $data['is_answer'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findReply(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM discussion_replies WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Toggle a like; returns ['liked'=>bool, 'count'=>int]. */
    public function toggleLike(int $replyId, int $userId): array
    {
        $check = $this->db->prepare('SELECT 1 FROM discussion_reply_likes WHERE reply_id = ? AND user_id = ?');
        $check->execute([$replyId, $userId]);
        $liked = (bool) $check->fetchColumn();

        if ($liked) {
            $this->db->prepare('DELETE FROM discussion_reply_likes WHERE reply_id = ? AND user_id = ?')
                     ->execute([$replyId, $userId]);
            $this->db->prepare('UPDATE discussion_replies SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?')
                     ->execute([$replyId]);
        } else {
            $this->db->prepare('INSERT IGNORE INTO discussion_reply_likes (reply_id, user_id) VALUES (?,?)')
                     ->execute([$replyId, $userId]);
            $this->db->prepare('UPDATE discussion_replies SET likes_count = likes_count + 1 WHERE id = ?')
                     ->execute([$replyId]);
        }

        $count = $this->db->prepare('SELECT likes_count FROM discussion_replies WHERE id = ?');
        $count->execute([$replyId]);
        return ['liked' => !$liked, 'count' => (int) $count->fetchColumn()];
    }

    public function updateReplyFlags(int $replyId, array $flags): void
    {
        $allowed = ['is_pinned', 'is_teacher_approved', 'is_answer'];
        $sets = [];
        $vals = [];
        foreach ($flags as $k => $v) {
            if (in_array($k, $allowed, true)) {
                $sets[] = "{$k} = ?";
                $vals[] = (int) (bool) $v;
            }
        }
        if (!$sets) {
            return;
        }
        $vals[] = $replyId;
        $stmt = $this->db->prepare('UPDATE discussion_replies SET ' . implode(', ', $sets) . ' WHERE id = ?');
        $stmt->execute($vals);
    }

    public function updateReplyContent(int $id, string $content): void
    {
        $this->db->prepare('UPDATE discussion_replies SET content = ? WHERE id = ?')->execute([$content, $id]);
    }

    public function deleteReply(int $id): void
    {
        $this->db->prepare('DELETE FROM discussion_replies WHERE id = ?')->execute([$id]);
    }

    public function report(int $reporterId, ?int $threadId, ?int $replyId, ?string $reason): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO discussion_reports (thread_id, reply_id, reporter_id, reason) VALUES (?,?,?,?)'
        );
        $stmt->execute([$threadId, $replyId, $reporterId, $reason]);
        return (int) $this->db->lastInsertId();
    }

    public function updateThread(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare('UPDATE discussion_threads SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function updateThreadContent(int $id, string $title, string $content): void
    {
        $stmt = $this->db->prepare('UPDATE discussion_threads SET title = ?, content = ? WHERE id = ?');
        $stmt->execute([$title, $content, $id]);
    }

    public function deleteThread(int $id): void
    {
        $this->db->prepare('DELETE FROM discussion_threads WHERE id = ?')->execute([$id]);
    }
}
