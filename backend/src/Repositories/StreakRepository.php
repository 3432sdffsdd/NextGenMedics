<?php
namespace App\Repositories;

class StreakRepository extends BaseRepository
{
    // ── Streak state ───────────────────────────────────────────

    public function getStreak(int $studentId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM study_streaks WHERE student_id = ?');
        $stmt->execute([$studentId]);
        $row = $stmt->fetch();
        return $row ?: ['student_id' => $studentId, 'current_streak' => 0, 'longest_streak' => 0, 'last_activity_date' => null];
    }

    public function saveStreak(int $studentId, int $current, int $longest, string $lastDate): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO study_streaks (student_id, current_streak, longest_streak, last_activity_date)
             VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE current_streak = VALUES(current_streak),
                                     longest_streak = VALUES(longest_streak),
                                     last_activity_date = VALUES(last_activity_date)'
        );
        $stmt->execute([$studentId, $current, $longest, $lastDate]);
    }

    // ── Activity log ───────────────────────────────────────────

    public function logActivity(int $studentId, string $date, string $type): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO study_activity_log (student_id, activity_date, activity_type, count)
             VALUES (?,?,?,1)
             ON DUPLICATE KEY UPDATE count = count + 1'
        );
        $stmt->execute([$studentId, $date, $type]);
    }

    /** Daily activity between two dates: [ ['activity_date'=>..,'total'=>..,'types'=>..], ... ]. */
    public function activityBetween(int $studentId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT activity_date,
                    SUM(count) AS total,
                    GROUP_CONCAT(DISTINCT activity_type) AS types
             FROM study_activity_log
             WHERE student_id = ? AND activity_date BETWEEN ? AND ?
             GROUP BY activity_date
             ORDER BY activity_date ASC"
        );
        $stmt->execute([$studentId, $from, $to]);
        return $stmt->fetchAll();
    }

    // ── Badges ─────────────────────────────────────────────────

    public function allBadges(): array
    {
        return $this->db->query('SELECT * FROM badges ORDER BY criteria_type, threshold')->fetchAll();
    }

    public function earnedBadgeIds(int $studentId): array
    {
        $stmt = $this->db->prepare('SELECT badge_id FROM student_badges WHERE student_id = ?');
        $stmt->execute([$studentId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'badge_id'));
    }

    public function earnedBadges(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, sb.earned_at FROM student_badges sb
             JOIN badges b ON b.id = sb.badge_id
             WHERE sb.student_id = ? ORDER BY sb.earned_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /** Award a badge; returns true only if it was newly awarded. */
    public function awardBadge(int $studentId, int $badgeId): bool
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO student_badges (student_id, badge_id) VALUES (?,?)');
        $stmt->execute([$studentId, $badgeId]);
        return $stmt->rowCount() > 0;
    }

    // ── Counters for badge criteria ────────────────────────────

    public function totalMcqsAnswered(int $studentId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(total_questions),0) FROM mcq_attempts WHERE student_id = ?');
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    public function totalFlashcardsReviewed(int $studentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM student_flashcard_progress WHERE student_id = ? AND last_reviewed_at IS NOT NULL'
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    /** Students whose streak is at risk (last activity was yesterday, none today). */
    public function studentsWithExpiringStreak(): array
    {
        $stmt = $this->db->prepare(
            "SELECT student_id, current_streak FROM study_streaks
             WHERE current_streak > 0 AND last_activity_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
