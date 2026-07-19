<?php
namespace App\Repositories;

/**
 * Tracks which quiz questions a student has already seen/attempted
 * so Daily Challenge can avoid duplicates until the bank is exhausted.
 * Also powers Weak Areas / Mistakes from Daily Challenge + course quiz attempts.
 */
class StudentQuestionHistoryRepository extends BaseRepository
{
    /** @return list<int> */
    public function seenQuestionIds(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT question_id FROM student_question_history WHERE student_id = ?'
        );
        $stmt->execute([$studentId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'question_id'));
    }

    public function countSeen(int $studentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM student_question_history WHERE student_id = ?'
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    public function resetForStudent(int $studentId): void
    {
        $stmt = $this->db->prepare('DELETE FROM student_question_history WHERE student_id = ?');
        $stmt->execute([$studentId]);
    }

    public function record(
        int $studentId,
        int $questionId,
        ?bool $isCorrect,
        ?string $selectedOption,
        string $attemptDate,
        ?string $dailyChallengeDate = null,
        ?int $challengeSetId = null,
        string $source = 'daily'
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO student_question_history
                (student_id, question_id, is_correct, selected_option, attempt_date, daily_challenge_date, challenge_set_id, source)
             VALUES (?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                is_correct = VALUES(is_correct),
                selected_option = COALESCE(VALUES(selected_option), selected_option),
                attempt_date = VALUES(attempt_date),
                daily_challenge_date = COALESCE(VALUES(daily_challenge_date), daily_challenge_date),
                challenge_set_id = COALESCE(VALUES(challenge_set_id), challenge_set_id),
                source = VALUES(source)'
        );
        $stmt->execute([
            $studentId,
            $questionId,
            $isCorrect === null ? null : ($isCorrect ? 1 : 0),
            $selectedOption,
            $attemptDate,
            $dailyChallengeDate,
            $challengeSetId,
            $source,
        ]);
    }

    /**
     * Sync (and refresh) history from submitted course quiz attempts in Quizzes tab.
     * Uses UPSERT so later quiz retries update correctness.
     */
    public function syncFromQuizAttempts(int $studentId): int
    {
        $stmt = $this->db->prepare(
            "SELECT qa.student_id, qaa.question_id, qaa.is_correct, qaa.selected_option_ids,
                    DATE(qa.submitted_at) AS attempt_date
             FROM quiz_attempt_answers qaa
             JOIN quiz_attempts qa ON qa.id = qaa.attempt_id
             WHERE qa.student_id = ?
               AND qa.submitted_at IS NOT NULL
               AND qa.status IN ('submitted','evaluated')
             ORDER BY qa.submitted_at ASC, qaa.id ASC"
        );
        $stmt->execute([$studentId]);
        $rows = $stmt->fetchAll();
        $n = 0;
        foreach ($rows as $row) {
            $letter = $this->optionIdsToLetter((int) $row['question_id'], $row['selected_option_ids'] ?? null);
            $isCorrect = $row['is_correct'] === null ? null : ((int) $row['is_correct'] === 1);
            $this->record(
                (int) $row['student_id'],
                (int) $row['question_id'],
                $isCorrect,
                $letter,
                (string) $row['attempt_date'],
                null,
                null,
                'quiz'
            );
            $n++;
        }
        return $n;
    }

    private function optionIdsToLetter(int $questionId, mixed $selectedJson): ?string
    {
        if ($selectedJson === null || $selectedJson === '') {
            return null;
        }
        if (is_string($selectedJson) && preg_match('/^[A-Ea-e]$/', trim($selectedJson))) {
            return strtoupper(trim($selectedJson));
        }
        $ids = $selectedJson;
        if (is_string($selectedJson)) {
            $decoded = json_decode($selectedJson, true);
            if ($decoded === null && is_numeric(trim($selectedJson))) {
                $ids = [(int) trim($selectedJson)];
            } elseif (is_array($decoded)) {
                $ids = $decoded;
            } elseif (is_int($decoded) || is_float($decoded)) {
                $ids = [(int) $decoded];
            } else {
                $ids = [];
            }
        } elseif (is_int($selectedJson) || is_float($selectedJson)) {
            $ids = [(int) $selectedJson];
        } elseif (!is_array($selectedJson)) {
            $ids = [];
        }
        if (!$ids) {
            return null;
        }
        $selectedId = 0;
        foreach ($ids as $v) {
            if (is_numeric($v)) {
                $selectedId = (int) $v;
                break;
            }
            if (is_string($v) && preg_match('/^[A-Ea-e]$/', trim($v))) {
                return strtoupper(trim($v));
            }
        }
        if ($selectedId <= 0) {
            return null;
        }
        $opts = $this->db->prepare(
            'SELECT id FROM quiz_question_options WHERE question_id = ? ORDER BY sort_order, id LIMIT 5'
        );
        $opts->execute([$questionId]);
        $letters = ['A', 'B', 'C', 'D', 'E'];
        foreach ($opts->fetchAll() as $i => $opt) {
            if ((int) $opt['id'] === $selectedId) {
                return $letters[$i] ?? null;
            }
        }
        return null;
    }

    public function incorrectQuestions(int $studentId, int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT h.question_id, h.is_correct, h.selected_option, h.attempt_date, h.source,
                    qq.question_text, qq.explanation, qq.marks,
                    q.title AS quiz_title, q.id AS quiz_id,
                    c.title AS course_title, c.id AS course_id
             FROM student_question_history h
             JOIN quiz_questions qq ON qq.id = h.question_id
             JOIN quizzes q ON q.id = qq.quiz_id
             JOIN courses c ON c.id = q.course_id
             WHERE h.student_id = ? AND h.is_correct = 0
             ORDER BY h.attempt_date DESC, h.id DESC
             LIMIT {$limit} OFFSET {$offset}"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function countIncorrect(int $studentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM student_question_history WHERE student_id = ? AND is_correct = 0'
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    public function countAll(int $studentId): int
    {
        return $this->countSeen($studentId);
    }

    public function countCorrect(int $studentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM student_question_history WHERE student_id = ? AND is_correct = 1'
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Mistakes later answered correctly in Practice My Mistakes / Weak Areas.
     * (Excludes first-try correct quiz answers — those are not "mastered mistakes".)
     */
    public function countMasteredMistakes(int $studentId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM student_question_history
             WHERE student_id = ? AND is_correct = 1 AND source IN ('weak', 'practice')"
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    /** @return list<int> */
    public function incorrectQuestionIds(int $studentId, int $limit = 0): array
    {
        $sql = 'SELECT question_id FROM student_question_history
                WHERE student_id = ? AND is_correct = 0 ORDER BY attempt_date DESC, id DESC';
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'question_id'));
    }

    /**
     * Paginated mistakes list shaped for My Mistakes UI (quiz + daily sources).
     */
    public function listMistakes(int $studentId, array $filters, int $page = 1, int $perPage = 20): array
    {
        $where = ['h.student_id = ?', 'h.is_correct = 0'];
        $params = [$studentId];
        if (!empty($filters['topic'])) {
            $where[] = 'q.title LIKE ?';
            $params[] = '%' . $filters['topic'] . '%';
        }
        if (!empty($filters['subject'])) {
            // Subject filter = topic/quiz title (not course)
            $where[] = 'q.title LIKE ?';
            $params[] = '%' . $filters['subject'] . '%';
        }
        if (!empty($filters['chapter'])) {
            $where[] = 'q.title LIKE ?';
            $params[] = '%' . $filters['chapter'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'h.attempt_date >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'h.attempt_date <= ?';
            $params[] = $filters['date_to'];
        }
        $sqlWhere = implode(' AND ', $where);
        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM student_question_history h
             JOIN quiz_questions qq ON qq.id = h.question_id
             JOIN quizzes q ON q.id = qq.quiz_id
             WHERE {$sqlWhere}"
        );
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $stmt = $this->db->prepare(
            "SELECT h.id, h.question_id AS mcq_id, h.selected_option, h.attempt_date, h.source,
                    h.attempt_date AS last_wrong_at, 1 AS wrong_count, 0 AS consecutive_correct,
                    qq.question_text AS question, qq.explanation,
                    q.title AS topic, q.title AS chapter, q.title AS subject
             FROM student_question_history h
             JOIN quiz_questions qq ON qq.id = h.question_id
             JOIN quizzes q ON q.id = qq.quiz_id
             WHERE {$sqlWhere}
             ORDER BY h.attempt_date DESC, h.id DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }
}
