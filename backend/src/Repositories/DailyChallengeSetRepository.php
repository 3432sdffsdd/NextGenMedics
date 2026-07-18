<?php
namespace App\Repositories;

class DailyChallengeSetRepository extends BaseRepository
{
    public function findToday(int $studentId, string $date): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM daily_challenge_sets WHERE student_id = ? AND challenge_date = ?'
        );
        $stmt->execute([$studentId, $date]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['mcq_ids'] = !empty($row['mcq_ids'])
            ? (json_decode($row['mcq_ids'], true) ?: [])
            : [];
        $row['quiz_question_ids'] = !empty($row['quiz_question_ids'])
            ? (json_decode($row['quiz_question_ids'], true) ?: [])
            : [];
        return $row;
    }

    public function findByIdForStudent(int $id, int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM daily_challenge_sets WHERE id = ? AND student_id = ?'
        );
        $stmt->execute([$id, $studentId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['mcq_ids'] = !empty($row['mcq_ids'])
            ? (json_decode($row['mcq_ids'], true) ?: [])
            : [];
        $row['quiz_question_ids'] = !empty($row['quiz_question_ids'])
            ? (json_decode($row['quiz_question_ids'], true) ?: [])
            : [];
        return $row;
    }

    /** @param list<int> $quizQuestionIds */
    public function createQuizSet(int $studentId, string $date, array $quizQuestionIds): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO daily_challenge_sets
                (student_id, challenge_date, mcq_ids, question_source, quiz_question_ids)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                quiz_question_ids = IF(completed_at IS NULL, VALUES(quiz_question_ids), quiz_question_ids),
                question_source = IF(completed_at IS NULL, VALUES(question_source), question_source),
                mcq_ids = IF(completed_at IS NULL, VALUES(mcq_ids), mcq_ids)'
        );
        $stmt->execute([
            $studentId,
            $date,
            json_encode([]),
            'quiz',
            json_encode(array_values($quizQuestionIds)),
        ]);
        $existing = $this->findToday($studentId, $date);
        return (int) ($existing['id'] ?? $this->db->lastInsertId());
    }

    /** Legacy Study Tools MCQ sets (kept for compatibility). */
    public function create(int $studentId, string $date, array $mcqIds): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO daily_challenge_sets (student_id, challenge_date, mcq_ids, question_source)
             VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE mcq_ids = IF(completed_at IS NULL, VALUES(mcq_ids), mcq_ids)'
        );
        $stmt->execute([$studentId, $date, json_encode(array_values($mcqIds)), 'mcq']);
        $existing = $this->findToday($studentId, $date);
        return (int) ($existing['id'] ?? $this->db->lastInsertId());
    }

    public function markCompleted(
        int $id,
        ?int $attemptId = null,
        ?int $correct = null,
        ?int $wrong = null,
        ?float $score = null,
        ?int $timeSpent = null
    ): void {
        $stmt = $this->db->prepare(
            'UPDATE daily_challenge_sets
             SET attempt_id = COALESCE(?, attempt_id),
                 completed_at = NOW(),
                 correct_count = COALESCE(?, correct_count),
                 wrong_count = COALESCE(?, wrong_count),
                 score = COALESCE(?, score),
                 time_spent_seconds = COALESCE(?, time_spent_seconds)
             WHERE id = ?'
        );
        $stmt->execute([$attemptId, $correct, $wrong, $score, $timeSpent, $id]);
    }

    public function dailyAttemptExists(int $studentId, string $date): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM daily_challenge_sets
             WHERE student_id = ? AND challenge_date = ? AND completed_at IS NOT NULL'
        );
        $stmt->execute([$studentId, $date]);
        if ((int) $stmt->fetchColumn() > 0) {
            return true;
        }
        // Legacy fallback
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM mcq_attempts
             WHERE student_id = ? AND source = "daily" AND DATE(submitted_at) = ? AND submitted_at IS NOT NULL'
        );
        $stmt->execute([$studentId, $date]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function saveAnswer(int $setId, int $studentId, int $questionId, ?string $selected, bool $isCorrect): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO daily_challenge_answers
                (challenge_set_id, student_id, question_id, selected_option, is_correct)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                selected_option = VALUES(selected_option),
                is_correct = VALUES(is_correct)'
        );
        $stmt->execute([$setId, $studentId, $questionId, $selected, $isCorrect ? 1 : 0]);
    }

    public function recentCompleted(int $studentId, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, challenge_date, score, correct_count, wrong_count, time_spent_seconds, completed_at
             FROM daily_challenge_sets
             WHERE student_id = ? AND completed_at IS NOT NULL
             ORDER BY challenge_date DESC
             LIMIT {$limit}"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}
