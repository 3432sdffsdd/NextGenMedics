<?php
namespace App\Repositories;

class AttemptRepository extends BaseRepository
{
    public function create(int $studentId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO mcq_attempts (student_id, source, lecture_id, challenge_id, challenge_day, total_questions, started_at)
             VALUES (?,?,?,?,?,?, NOW())'
        );
        $stmt->execute([
            $studentId,
            $data['source'] ?? 'practice',
            $data['lecture_id'] ?? null,
            $data['challenge_id'] ?? null,
            $data['challenge_day'] ?? null,
            (int) ($data['total_questions'] ?? 0),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function addAnswer(int $attemptId, int $mcqId, ?string $selected, bool $isCorrect, int $timeSpent = 0): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO mcq_attempt_answers (attempt_id, mcq_id, selected_option, is_correct, time_spent_seconds)
             VALUES (?,?,?,?,?)'
        );
        $stmt->execute([$attemptId, $mcqId, $selected, $isCorrect ? 1 : 0, $timeSpent]);
    }

    public function finalize(int $attemptId, int $correct, int $wrong, float $score, int $timeSpent): void
    {
        $stmt = $this->db->prepare(
            'UPDATE mcq_attempts
             SET correct_count = ?, wrong_count = ?, score = ?, time_spent_seconds = ?, submitted_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([$correct, $wrong, $score, $timeSpent, $attemptId]);
    }

    public function challengeAttemptExists(int $studentId, int $challengeId, int $day): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM mcq_attempts WHERE student_id = ? AND challenge_id = ? AND challenge_day = ? AND submitted_at IS NOT NULL'
        );
        $stmt->execute([$studentId, $challengeId, $day]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function recentByStudent(int $studentId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, l.title AS lecture_title
             FROM mcq_attempts a
             LEFT JOIN lectures l ON l.id = a.lecture_id
             WHERE a.student_id = ? AND a.submitted_at IS NOT NULL
             ORDER BY a.submitted_at DESC LIMIT {$limit}"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}
