<?php
namespace App\Repositories;

class FlashcardRepository extends BaseRepository
{
    /**
     * Bulk-insert generated/manual flashcards.
     * @return int Number inserted.
     */
    public function insertMany(int $lectureId, ?int $courseId, ?int $userId, array $cards, string $source = 'ai'): int
    {
        if (!$cards) {
            return 0;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO flashcards (lecture_id, course_id, front, back, topic, difficulty, source, status, sort_order, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $order = $this->maxSort($lectureId);
        $n = 0;
        foreach ($cards as $c) {
            $order++;
            $stmt->execute([
                $lectureId, $courseId,
                $c['front'], $c['back'],
                $c['topic'] ?? null,
                $c['difficulty'] ?? 'moderate',
                $source,
                'draft',
                $order,
                $userId,
            ]);
            $n++;
        }
        return $n;
    }

    public function listByLecture(int $lectureId, ?string $status = null): array
    {
        $sql = 'SELECT * FROM flashcards WHERE lecture_id = ?';
        $params = [$lectureId];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByLecture(int $lectureId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM flashcards WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn();
    }

    public function existingFronts(int $lectureId): array
    {
        $stmt = $this->db->prepare('SELECT front FROM flashcards WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return array_column($stmt->fetchAll(), 'front');
    }

    public function update(int $id, array $fields): void
    {
        $allowed = ['front', 'back', 'topic', 'difficulty', 'status'];
        $sets = [];
        $vals = [];
        foreach ($fields as $col => $val) {
            if (in_array($col, $allowed, true)) {
                $sets[] = "{$col} = ?";
                $vals[] = $val;
            }
        }
        if (!$sets) {
            return;
        }
        $vals[] = $id;
        $stmt = $this->db->prepare('UPDATE flashcards SET ' . implode(', ', $sets) . ' WHERE id = ?');
        $stmt->execute($vals);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM flashcards WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function deleteAllByLecture(int $lectureId): void
    {
        $this->db->prepare('DELETE FROM flashcards WHERE lecture_id = ?')->execute([$lectureId]);
    }

    public function approveAllByLecture(int $lectureId): void
    {
        $stmt = $this->db->prepare("UPDATE flashcards SET status = 'approved' WHERE lecture_id = ?");
        $stmt->execute([$lectureId]);
    }

    public function lectureId(int $flashcardId): ?int
    {
        $stmt = $this->db->prepare('SELECT lecture_id FROM flashcards WHERE id = ?');
        $stmt->execute([$flashcardId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int) $val : null;
    }

    private function maxSort(int $lectureId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM flashcards WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn();
    }

    // ── Student-facing (Flashcard Center) ──────────────────────

    /**
     * Approved flashcards from published lectures the student is enrolled in,
     * joined with the student's per-card progress. Supports search + filters.
     */
    public function listForStudent(int $studentId, array $filters = []): array
    {
        $sql = "SELECT f.id, f.lecture_id, f.course_id, f.front, f.back, f.topic, f.difficulty,
                       l.title AS lecture_title, c.title AS course_title,
                       COALESCE(p.status,'new') AS progress_status,
                       COALESCE(p.is_favorite,0) AS is_favorite,
                       COALESCE(p.is_difficult,0) AS is_difficult,
                       COALESCE(p.review_count,0) AS review_count,
                       p.last_reviewed_at
                FROM flashcards f
                JOIN lectures l ON l.id = f.lecture_id
                JOIN ai_lecture_content ac ON ac.lecture_id = f.lecture_id AND ac.status = 'published'
                JOIN course_enrollments ce ON ce.course_id = f.course_id AND ce.student_id = :sid AND ce.status = 'active'
                LEFT JOIN courses c ON c.id = f.course_id
                LEFT JOIN student_flashcard_progress p ON p.flashcard_id = f.id AND p.student_id = :sid2
                WHERE f.status = 'approved'";
        $params = [':sid' => $studentId, ':sid2' => $studentId];

        if (!empty($filters['course_id'])) {
            $sql .= ' AND f.course_id = :course';
            $params[':course'] = (int) $filters['course_id'];
        }
        if (!empty($filters['lecture_id'])) {
            $sql .= ' AND f.lecture_id = :lecture';
            $params[':lecture'] = (int) $filters['lecture_id'];
        }
        if (!empty($filters['favorite'])) {
            $sql .= ' AND p.is_favorite = 1';
        }
        if (!empty($filters['difficult'])) {
            $sql .= ' AND p.is_difficult = 1';
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (f.front LIKE :q OR f.back LIKE :q OR f.topic LIKE :q)';
            $params[':q'] = '%' . $filters['search'] . '%';
        }
        $sql .= ' ORDER BY f.lecture_id, f.sort_order, f.id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Lectures/courses that have published flashcards available to a student. */
    public function studentFilters(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT f.lecture_id, l.title AS lecture_title, f.course_id, c.title AS course_title, COUNT(*) AS count
             FROM flashcards f
             JOIN lectures l ON l.id = f.lecture_id
             JOIN ai_lecture_content ac ON ac.lecture_id = f.lecture_id AND ac.status = 'published'
             JOIN course_enrollments ce ON ce.course_id = f.course_id AND ce.student_id = ? AND ce.status = 'active'
             LEFT JOIN courses c ON c.id = f.course_id
             WHERE f.status = 'approved'
             GROUP BY f.lecture_id, l.title, f.course_id, c.title
             ORDER BY c.title, l.title"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /** Verify a flashcard is visible to the student (enrolled + published). */
    public function isVisibleToStudent(int $flashcardId, int $studentId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM flashcards f
             JOIN ai_lecture_content ac ON ac.lecture_id = f.lecture_id AND ac.status = 'published'
             JOIN course_enrollments ce ON ce.course_id = f.course_id AND ce.student_id = ? AND ce.status = 'active'
             WHERE f.id = ? AND f.status = 'approved'"
        );
        $stmt->execute([$studentId, $flashcardId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function setProgress(int $studentId, int $flashcardId, array $changes): void
    {
        $ins = $this->db->prepare(
            'INSERT IGNORE INTO student_flashcard_progress (student_id, flashcard_id) VALUES (?,?)'
        );
        $ins->execute([$studentId, $flashcardId]);

        $sets = [];
        $vals = [];
        if (array_key_exists('status', $changes) && in_array($changes['status'], ['new', 'learning', 'mastered'], true)) {
            $sets[] = 'status = ?';
            $vals[] = $changes['status'];
        }
        if (array_key_exists('is_favorite', $changes)) {
            $sets[] = 'is_favorite = ?';
            $vals[] = (int) (bool) $changes['is_favorite'];
        }
        if (array_key_exists('is_difficult', $changes)) {
            $sets[] = 'is_difficult = ?';
            $vals[] = (int) (bool) $changes['is_difficult'];
        }
        if (!empty($changes['reviewed'])) {
            $sets[] = 'review_count = review_count + 1';
            $sets[] = 'last_reviewed_at = NOW()';
        }
        if (!$sets) {
            return;
        }
        $vals[] = $studentId;
        $vals[] = $flashcardId;
        $stmt = $this->db->prepare(
            'UPDATE student_flashcard_progress SET ' . implode(', ', $sets) . ' WHERE student_id = ? AND flashcard_id = ?'
        );
        $stmt->execute($vals);
    }
}
