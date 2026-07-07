<?php
namespace App\Repositories;

class McqRepository extends BaseRepository
{
    public function insertMany(int $lectureId, ?int $courseId, ?int $userId, array $mcqs, string $source = 'ai'): int
    {
        if (!$mcqs) {
            return 0;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO mcqs
                (lecture_id, course_id, question, option_a, option_b, option_c, option_d, option_e,
                 correct_option, explanation, option_explanations, topic, difficulty, source, status, sort_order, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $order = $this->maxSort($lectureId);
        $n = 0;
        foreach ($mcqs as $q) {
            $order++;
            $stmt->execute([
                $lectureId, $courseId,
                $q['question'],
                $q['option_a'], $q['option_b'],
                $q['option_c'] ?: null, $q['option_d'] ?: null, $q['option_e'] ?: null,
                $q['correct_option'],
                $q['explanation'] ?? null,
                isset($q['option_explanations']) && $q['option_explanations']
                    ? json_encode($q['option_explanations'], JSON_UNESCAPED_UNICODE) : null,
                $q['topic'] ?? null,
                $q['difficulty'] ?? 'moderate',
                $source,
                'draft',
                $order,
                $userId,
            ]);
            $n++;
        }
        return $n;
    }

    /** Insert a single MCQ (manual add). Returns new id. */
    public function insertOne(int $lectureId, ?int $courseId, ?int $userId, array $q, string $source = 'manual'): int
    {
        $this->insertMany($lectureId, $courseId, $userId, [$q], $source);
        return (int) $this->db->lastInsertId();
    }

    public function listByLecture(int $lectureId, ?string $status = null, bool $withAnswers = true): array
    {
        $sql = 'SELECT * FROM mcqs WHERE lecture_id = ?';
        $params = [$lectureId];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return array_map(fn($r) => $this->hydrate($r, $withAnswers), $rows);
    }

    public function findByIds(array $ids, bool $withAnswers = true): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT * FROM mcqs WHERE id IN ({$in}) ORDER BY sort_order ASC, id ASC");
        $stmt->execute($ids);
        return array_map(fn($r) => $this->hydrate($r, $withAnswers), $stmt->fetchAll());
    }

    public function publishedIdsByLecture(int $lectureId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM mcqs WHERE lecture_id = ? AND status = 'published' ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([$lectureId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }

    public function countByLecture(int $lectureId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM mcqs WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn();
    }

    public function existingQuestions(int $lectureId): array
    {
        $stmt = $this->db->prepare('SELECT question FROM mcqs WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return array_column($stmt->fetchAll(), 'question');
    }

    public function update(int $id, array $fields): void
    {
        $allowed = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e',
                    'correct_option', 'explanation', 'topic', 'difficulty', 'status'];
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
        $stmt = $this->db->prepare('UPDATE mcqs SET ' . implode(', ', $sets) . ' WHERE id = ?');
        $stmt->execute($vals);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM mcqs WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function deleteAllByLecture(int $lectureId): void
    {
        $this->db->prepare('DELETE FROM mcqs WHERE lecture_id = ?')->execute([$lectureId]);
    }

    /** Approve = becomes visible for challenge/practice once content published. */
    public function approveAllByLecture(int $lectureId): void
    {
        $stmt = $this->db->prepare("UPDATE mcqs SET status = 'approved' WHERE lecture_id = ? AND status = 'draft'");
        $stmt->execute([$lectureId]);
    }

    public function publishAllByLecture(int $lectureId): void
    {
        $stmt = $this->db->prepare("UPDATE mcqs SET status = 'published' WHERE lecture_id = ?");
        $stmt->execute([$lectureId]);
    }

    public function lectureId(int $mcqId): ?int
    {
        $stmt = $this->db->prepare('SELECT lecture_id FROM mcqs WHERE id = ?');
        $stmt->execute([$mcqId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int) $val : null;
    }

    /** Random published MCQs from completed lectures in enrolled courses. */
    public function randomFromCompletedLectures(int $studentId, array $courseIds, int $limit = 10): array
    {
        return $this->pickForDailyChallenge($studentId, $courseIds, $limit, [], true);
    }

    /** Random published MCQs from enrolled courses (fallback when no completed lectures). */
    public function randomPublishedByCourses(array $courseIds, int $limit = 10, array $excludeIds = []): array
    {
        return $this->pickForDailyChallenge(0, $courseIds, $limit, $excludeIds, false);
    }

    /**
     * Pick MCQs for the daily challenge.
     * Uses Study Tools MCQs (not course quizzes). Prefers lectures the student has practiced.
     * Does not require ai_lecture_content — only published/approved MCQs in enrolled courses.
     */
    public function pickForDailyChallenge(
        int $studentId,
        array $courseIds,
        int $limit = 10,
        array $excludeIds = [],
        bool $attemptedOnly = false
    ): array {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds || $limit <= 0) {
            return [];
        }

        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = $courseIds;
        $excludeSql = '';
        if ($excludeIds) {
            $excludeIds = array_values(array_filter(array_map('intval', $excludeIds)));
            if ($excludeIds) {
                $excludeIn = implode(',', array_fill(0, count($excludeIds), '?'));
                $excludeSql = " AND m.id NOT IN ({$excludeIn})";
                $params = array_merge($params, $excludeIds);
            }
        }

        $activitySql = '';
        if ($attemptedOnly && $studentId > 0) {
            $activitySql = ' AND (
                EXISTS (
                    SELECT 1 FROM mcq_attempt_answers aa
                    JOIN mcq_attempts a ON a.id = aa.attempt_id
                    JOIN mcqs m2 ON m2.id = aa.mcq_id
                    WHERE a.student_id = ? AND m2.lecture_id = l.id
                )
                OR EXISTS (
                    SELECT 1 FROM lecture_progress lp
                    WHERE lp.student_id = ? AND lp.lecture_id = l.id AND lp.completed = 1
                )
                OR EXISTS (
                    SELECT 1 FROM quiz_attempts qa
                    JOIN quizzes q ON q.id = qa.quiz_id
                    WHERE qa.student_id = ? AND qa.submitted_at IS NOT NULL AND q.course_id = mo.course_id
                )
            )';
            $params[] = $studentId;
            $params[] = $studentId;
            $params[] = $studentId;
        }

        $params[] = $limit;
        $stmt = $this->db->prepare(
            "SELECT m.id FROM mcqs m
             JOIN lectures l ON l.id = m.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules mo ON mo.id = ch.module_id
             WHERE m.status IN ('published', 'approved')
             AND mo.course_id IN ({$in}){$excludeSql}{$activitySql}
             ORDER BY RAND() LIMIT ?"
        );
        $stmt->execute($params);
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }

    /** Count published Study Tools MCQs available to a student across enrolled courses. */
    public function countAvailableForStudent(array $courseIds): int
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM mcqs m
             JOIN lectures l ON l.id = m.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules mo ON mo.id = ch.module_id
             WHERE m.status IN ('published', 'approved') AND mo.course_id IN ({$in})"
        );
        $stmt->execute($courseIds);
        return (int) $stmt->fetchColumn();
    }

    /** Paginated question bank search for a student. */
    public function searchForStudent(int $studentId, array $courseIds, array $filters, int $page, int $perPage, bool $withAnswers = false): array
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return ['items' => [], 'total' => 0];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $where = ["m.status = 'published'", "mo.course_id IN ({$in})"];
        $params = $courseIds;

        foreach (['topic', 'difficulty'] as $col) {
            if (!empty($filters[$col])) {
                $where[] = "m.{$col} = ?";
                $params[] = $filters[$col];
            }
        }
        if (!empty($filters['subject'])) {
            $where[] = 'mo.title LIKE ?';
            $params[] = '%' . $filters['subject'] . '%';
        }
        if (!empty($filters['chapter'])) {
            $where[] = 'ch.title LIKE ?';
            $params[] = '%' . $filters['chapter'] . '%';
        }
        if (!empty($filters['search'])) {
            $where[] = 'm.question LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['bookmarked'])) {
            $where[] = 'EXISTS (SELECT 1 FROM content_bookmarks b WHERE b.student_id = ? AND b.content_type = "mcq" AND b.content_id = m.id)';
            $params[] = $studentId;
        }
        if (($filters['attempt_filter'] ?? '') === 'correct') {
            $where[] = 'EXISTS (SELECT 1 FROM mcq_attempt_answers aa JOIN mcq_attempts a ON a.id = aa.attempt_id
                         WHERE a.student_id = ? AND aa.mcq_id = m.id AND aa.is_correct = 1)';
            $params[] = $studentId;
        } elseif (($filters['attempt_filter'] ?? '') === 'incorrect') {
            $where[] = 'EXISTS (SELECT 1 FROM mcq_attempt_answers aa JOIN mcq_attempts a ON a.id = aa.attempt_id
                         WHERE a.student_id = ? AND aa.mcq_id = m.id AND aa.is_correct = 0)';
            $params[] = $studentId;
        } elseif (($filters['attempt_filter'] ?? '') === 'unattempted') {
            $where[] = 'NOT EXISTS (SELECT 1 FROM mcq_attempt_answers aa JOIN mcq_attempts a ON a.id = aa.attempt_id
                         WHERE a.student_id = ? AND aa.mcq_id = m.id)';
            $params[] = $studentId;
        }

        $joinPublished = 'JOIN ai_lecture_content ac ON ac.lecture_id = l.id AND ac.status = "published"';
        $sql = "SELECT m.*, mo.title AS subject, ch.title AS chapter, l.title AS lecture_title
                FROM mcqs m
                JOIN lectures l ON l.id = m.lecture_id
                JOIN chapters ch ON ch.id = l.chapter_id
                JOIN modules mo ON mo.id = ch.module_id
                {$joinPublished}
                WHERE " . implode(' AND ', $where);

        if (!empty($filters['random'])) {
            $sql .= ' ORDER BY RAND()';
        } else {
            $sql .= ' ORDER BY m.id DESC';
        }

        $result = $this->paginate($sql, $params, $page, $perPage);
        $result['items'] = array_map(fn($r) => $this->hydrate($r, $withAnswers), $result['items']);
        return $result;
    }

    public function filterOptionsForStudent(int $studentId, array $courseIds): array
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return ['subjects' => [], 'chapters' => [], 'topics' => []];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $base = "FROM mcqs m
                 JOIN lectures l ON l.id = m.lecture_id
                 JOIN chapters ch ON ch.id = l.chapter_id
                 JOIN modules mo ON mo.id = ch.module_id
                 JOIN ai_lecture_content ac ON ac.lecture_id = l.id AND ac.status = 'published'
                 WHERE m.status = 'published' AND mo.course_id IN ({$in})";

        $subjects = $this->db->prepare("SELECT DISTINCT mo.title AS name {$base} ORDER BY mo.title");
        $subjects->execute($courseIds);
        $chapters = $this->db->prepare("SELECT DISTINCT ch.title AS name {$base} ORDER BY ch.title");
        $chapters->execute($courseIds);
        $topics = $this->db->prepare("SELECT DISTINCT m.topic AS name {$base} AND m.topic IS NOT NULL AND m.topic != '' ORDER BY m.topic");
        $topics->execute($courseIds);

        return [
            'subjects' => array_column($subjects->fetchAll(), 'name'),
            'chapters' => array_column($chapters->fetchAll(), 'name'),
            'topics'   => array_column($topics->fetchAll(), 'name'),
        ];
    }

    private function maxSort(int $lectureId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM mcqs WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn();
    }

    /** Normalise a DB row; optionally strip answer/explanation for exam delivery. */
    private function hydrate(array $row, bool $withAnswers): array
    {
        $row['option_explanations'] = !empty($row['option_explanations'])
            ? (json_decode($row['option_explanations'], true) ?: null)
            : null;

        if (!$withAnswers) {
            unset($row['correct_option'], $row['explanation'], $row['option_explanations']);
        }
        return $row;
    }
}
