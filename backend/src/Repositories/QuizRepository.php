<?php
namespace App\Repositories;

class QuizRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON c.id = q.course_id WHERE q.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getQuestions(int $quizId, bool $shuffle = false, bool $includeCorrect = false): array
    {
        $order = $shuffle ? 'RAND()' : 'sort_order';
        $stmt = $this->db->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY {$order}");
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();

        $optCols = $includeCorrect
            ? 'id, option_text, match_pair, sort_order, is_correct'
            : 'id, option_text, match_pair, sort_order';

        foreach ($questions as &$q) {
            $opts = $this->db->prepare("SELECT {$optCols} FROM quiz_question_options WHERE question_id = ? ORDER BY sort_order");
            $opts->execute([$q['id']]);
            $q['options'] = $opts->fetchAll();
        }
        return $questions;
    }

    public function listStudentAttempts(int $quizId, int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, attempt_number, score, percentage, passed, status, started_at, submitted_at, time_taken_seconds
             FROM quiz_attempts WHERE quiz_id = ? AND student_id = ? AND status IN ("submitted", "evaluated")
             ORDER BY submitted_at DESC'
        );
        $stmt->execute([$quizId, $studentId]);
        return $stmt->fetchAll();
    }

    /** All submitted attempts for a quiz (teacher dashboard). */
    public function listAttemptsForQuiz(int $quizId, ?string $search = null): array
    {
        $sql = 'SELECT qa.*, u.username, u.email,
                       CONCAT(u.first_name, " ", u.last_name) AS student_name
                FROM quiz_attempts qa
                JOIN users u ON u.id = qa.student_id
                WHERE qa.quiz_id = ? AND qa.status IN ("submitted", "evaluated")';
        $params = [$quizId];
        if ($search !== null && trim($search) !== '') {
            $sql .= ' AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name, " ", u.last_name) LIKE ?)';
            $like = '%' . trim($search) . '%';
            $params = array_merge($params, array_fill(0, 5, $like));
        }
        $sql .= ' ORDER BY qa.percentage DESC, qa.submitted_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function bulkImportQuestions(int $quizId, array $questions): int
    {
        $sortStmt = $this->db->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM quiz_questions WHERE quiz_id = ?');
        $sortStmt->execute([$quizId]);
        $sort = (int) $sortStmt->fetchColumn();
        $imported = 0;

        foreach ($questions as $q) {
            $sort++;
            $questionId = $this->addQuestion([
                'quiz_id'       => $quizId,
                'question_type' => 'single_choice',
                'question_text' => $q['question_text'],
                'marks'         => $q['marks'] ?? 1,
                'explanation'   => $q['explanation'] ?? null,
                'sort_order'    => $sort,
            ]);
            foreach ($q['options'] as $i => $opt) {
                $this->addOption([
                    'question_id' => $questionId,
                    'option_text' => $opt['option_text'],
                    'is_correct'  => !empty($opt['is_correct']) ? 1 : 0,
                    'sort_order'  => $i,
                ]);
            }
            $imported++;
        }
        return $imported;
    }

    public function getAttemptAnswers(int $attemptId): array
    {
        $stmt = $this->db->prepare(
            'SELECT aa.*, qq.question_text, qq.question_type, qq.explanation, qq.marks
             FROM quiz_attempt_answers aa
             JOIN quiz_questions qq ON qq.id = aa.question_id
             WHERE aa.attempt_id = ?
             ORDER BY qq.sort_order'
        );
        $stmt->execute([$attemptId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            if (!empty($row['selected_option_ids'])) {
                $row['selected_option_ids'] = json_decode($row['selected_option_ids'], true);
            }
        }
        return $rows;
    }

    public function getQuestionOptions(int $questionId, bool $includeCorrect = true): array
    {
        $cols = $includeCorrect
            ? 'id, option_text, match_pair, sort_order, is_correct'
            : 'id, option_text, match_pair, sort_order';
        $stmt = $this->db->prepare("SELECT {$cols} FROM quiz_question_options WHERE question_id = ? ORDER BY sort_order");
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    public function listByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT q.*,
                    (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count
             FROM quizzes q
             WHERE q.course_id = ?
             ORDER BY q.created_at DESC'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function countQuestions(int $quizId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = ?');
        $stmt->execute([$quizId]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO quizzes (course_id, teacher_id, title, description, quiz_type, duration_minutes,
             passing_marks, total_marks, random_questions, question_pool_size, negative_marking,
             negative_mark_value, shuffle_questions, shuffle_options, max_attempts, show_leaderboard,
             auto_evaluate, show_review, available_from, available_until, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['course_id'], $data['teacher_id'], $data['title'],
            $data['description'] ?? null, $data['quiz_type'] ?? 'mcq',
            $data['duration_minutes'] ?? 30, $data['passing_marks'] ?? 50,
            $data['total_marks'] ?? 100, $data['random_questions'] ?? 0,
            $data['question_pool_size'] ?? null, $data['negative_marking'] ?? 0,
            $data['negative_mark_value'] ?? 0, $data['shuffle_questions'] ?? 0,
            $data['shuffle_options'] ?? 0, $data['max_attempts'] ?? 1,
            $data['show_leaderboard'] ?? 0, $data['auto_evaluate'] ?? 1,
            $data['show_review'] ?? 1,
            $data['available_from'] ?? null, $data['available_until'] ?? null,
            $data['status'] ?? 'draft',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['title', 'description', 'quiz_type', 'duration_minutes', 'passing_marks',
            'total_marks', 'shuffle_questions', 'shuffle_options', 'max_attempts',
            'show_leaderboard', 'auto_evaluate', 'show_review', 'available_from', 'available_until', 'status'];
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
        }
        if (!$fields) {
            return false;
        }
        $values[] = $id;
        $stmt = $this->db->prepare('UPDATE quizzes SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM quizzes WHERE id = ?')->execute([$id]);
    }

    public function deleteQuestion(int $questionId): bool
    {
        return $this->db->prepare('DELETE FROM quiz_questions WHERE id = ?')->execute([$questionId]);
    }

    public function getQuestionQuizId(int $questionId): ?int
    {
        $stmt = $this->db->prepare('SELECT quiz_id FROM quiz_questions WHERE id = ?');
        $stmt->execute([$questionId]);
        $v = $stmt->fetchColumn();
        return $v !== false ? (int) $v : null;
    }

    /** Deep-copy a quiz with its questions and options. Returns the new quiz id. */
    public function duplicate(int $id, int $teacherId): ?int
    {
        $quiz = $this->findById($id);
        if (!$quiz) {
            return null;
        }
        $this->db->beginTransaction();
        try {
            $quiz['teacher_id'] = $teacherId;
            $quiz['title'] = $quiz['title'] . ' (Copy)';
            $quiz['status'] = 'draft';
            $newId = $this->create($quiz);

            foreach ($this->getQuestions($id) as $q) {
                $newQ = $this->addQuestion([
                    'quiz_id'       => $newId,
                    'question_type' => $q['question_type'],
                    'question_text' => $q['question_text'],
                    'marks'         => $q['marks'] ?? 1,
                    'explanation'   => $q['explanation'] ?? null,
                    'sort_order'    => $q['sort_order'] ?? 0,
                ]);
                // Re-read options WITH correctness (getQuestions strips is_correct not, it keeps only some cols)
                $opts = $this->db->prepare('SELECT option_text, is_correct, match_pair, sort_order FROM quiz_question_options WHERE question_id = ? ORDER BY sort_order');
                $opts->execute([$q['id']]);
                foreach ($opts->fetchAll() as $o) {
                    $this->addOption(array_merge($o, ['question_id' => $newQ]));
                }
            }
            $this->db->commit();
            return $newId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function addQuestion(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO quiz_questions (quiz_id, question_type, question_text, marks, explanation, sort_order)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['quiz_id'], $data['question_type'], $data['question_text'],
            $data['marks'] ?? 1, $data['explanation'] ?? null, $data['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function addOption(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO quiz_question_options (question_id, option_text, is_correct, match_pair, sort_order)
             VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $data['question_id'], $data['option_text'],
            $data['is_correct'] ?? 0, $data['match_pair'] ?? null, $data['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function startAttempt(int $quizId, int $studentId): int
    {
        $count = $this->db->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?');
        $count->execute([$quizId, $studentId]);
        $attemptNum = (int) $count->fetchColumn() + 1;

        $stmt = $this->db->prepare(
            'INSERT INTO quiz_attempts (quiz_id, student_id, attempt_number, status) VALUES (?,?,?,?)'
        );
        $stmt->execute([$quizId, $studentId, $attemptNum, 'in_progress']);
        return (int) $this->db->lastInsertId();
    }

    public function submitAttempt(int $attemptId, float $score, float $percentage, bool $passed, ?int $timeTaken = null): bool
    {
        if ($timeTaken !== null) {
            $stmt = $this->db->prepare(
                'UPDATE quiz_attempts SET score = ?, percentage = ?, passed = ?, status = "evaluated",
                 submitted_at = NOW(), evaluated_at = NOW(), time_taken_seconds = ? WHERE id = ?'
            );
            return $stmt->execute([$score, $percentage, $passed ? 1 : 0, $timeTaken, $attemptId]);
        }
        $stmt = $this->db->prepare(
            'UPDATE quiz_attempts SET score = ?, percentage = ?, passed = ?, status = "evaluated",
             submitted_at = NOW(), evaluated_at = NOW() WHERE id = ?'
        );
        return $stmt->execute([$score, $percentage, $passed ? 1 : 0, $attemptId]);
    }

    public function saveAnswer(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO quiz_attempt_answers (attempt_id, question_id, selected_option_ids, text_answer, is_correct, marks_awarded)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['attempt_id'], $data['question_id'],
            isset($data['selected_option_ids']) ? json_encode($data['selected_option_ids']) : null,
            $data['text_answer'] ?? null,
            $data['is_correct'] ?? null, $data['marks_awarded'] ?? null,
        ]);
    }

    public function getAttempt(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM quiz_attempts WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getLeaderboard(int $quizId): array
    {
        $stmt = $this->db->prepare(
            'SELECT qa.score, qa.percentage, qa.time_taken_seconds,
                    CONCAT(u.first_name, " ", u.last_name) AS student_name
             FROM quiz_attempts qa JOIN users u ON u.id = qa.student_id
             WHERE qa.quiz_id = ? AND qa.status IN ("submitted", "evaluated")
             ORDER BY qa.score DESC, qa.time_taken_seconds ASC LIMIT 20'
        );
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    public function countPendingReviews(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM quiz_attempts WHERE status = 'submitted' AND score IS NULL"
        )->fetchColumn();
    }
}
