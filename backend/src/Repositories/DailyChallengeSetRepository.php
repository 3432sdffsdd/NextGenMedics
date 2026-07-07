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
        if ($row && !empty($row['mcq_ids'])) {
            $row['mcq_ids'] = json_decode($row['mcq_ids'], true) ?: [];
        }
        return $row ?: null;
    }

    public function create(int $studentId, string $date, array $mcqIds): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO daily_challenge_sets (student_id, challenge_date, mcq_ids) VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE mcq_ids = VALUES(mcq_ids)'
        );
        $stmt->execute([$studentId, $date, json_encode(array_values($mcqIds))]);
        $existing = $this->findToday($studentId, $date);
        return (int) ($existing['id'] ?? $this->db->lastInsertId());
    }

    public function markCompleted(int $id, int $attemptId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE daily_challenge_sets SET attempt_id = ?, completed_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$attemptId, $id]);
    }

    public function dailyAttemptExists(int $studentId, string $date): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM mcq_attempts
             WHERE student_id = ? AND source = "daily" AND DATE(submitted_at) = ? AND submitted_at IS NOT NULL'
        );
        $stmt->execute([$studentId, $date]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
