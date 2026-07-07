<?php
namespace App\Services;

use App\Repositories\StreakRepository;

/**
 * Tracks study engagement: daily activity, streaks, and achievement badges.
 * Called whenever a student performs a study activity (lecture, MCQ, flashcard,
 * revision, login).
 */
class StudyService
{
    /** Activity types that count towards a study day. */
    public const TYPES = ['login', 'lecture', 'mcq', 'revision', 'flashcard'];

    public function __construct(
        private StreakRepository $streaks,
        private NotificationService $notifications
    ) {}

    /**
     * Record one study activity, update the streak, and award any newly earned
     * badges. Returns the refreshed streak plus any badges just unlocked.
     */
    public function recordActivity(int $studentId, string $type): array
    {
        if (!in_array($type, self::TYPES, true)) {
            $type = 'login';
        }
        $today = date('Y-m-d');
        $this->streaks->logActivity($studentId, $today, $type);
        $this->recomputeStreak($studentId, $today);

        $newBadges = $this->awardBadges($studentId);

        return [
            'streak'     => $this->streaks->getStreak($studentId),
            'new_badges' => $newBadges,
        ];
    }

    private function recomputeStreak(int $studentId, string $today): void
    {
        $s = $this->streaks->getStreak($studentId);
        $last = $s['last_activity_date'] ?? null;

        if ($last === $today) {
            return; // already counted a study day today
        }

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $current = $last === $yesterday ? ((int) $s['current_streak'] + 1) : 1;
        $longest = max((int) $s['longest_streak'], $current);

        $this->streaks->saveStreak($studentId, $current, $longest, $today);
    }

    /** Check criteria and award any unearned badges the student now qualifies for. */
    public function awardBadges(int $studentId): array
    {
        $streak = $this->streaks->getStreak($studentId);
        $earned = $this->streaks->earnedBadgeIds($studentId);

        $metrics = [
            'streak'    => (int) $streak['current_streak'],
            'mcq'       => $this->streaks->totalMcqsAnswered($studentId),
            'flashcard' => $this->streaks->totalFlashcardsReviewed($studentId),
        ];

        $awarded = [];
        foreach ($this->streaks->allBadges() as $badge) {
            $type = $badge['criteria_type'];
            if (!isset($metrics[$type])) {
                continue;
            }
            if (in_array((int) $badge['id'], $earned, true)) {
                continue;
            }
            if ($metrics[$type] >= (int) $badge['threshold']) {
                if ($this->streaks->awardBadge($studentId, (int) $badge['id'])) {
                    $awarded[] = $badge;
                    $this->notifications->notify(
                        $studentId,
                        'badge_unlocked',
                        'Achievement unlocked: ' . $badge['name'],
                        $badge['description'] ?? ('You earned the ' . $badge['name'] . ' badge!'),
                        ['badge_code' => $badge['code']],
                        false
                    );
                }
            }
        }
        return $awarded;
    }

    /** Cron: warn students whose streak will break if they don't study today. */
    public function remindExpiringStreaks(): int
    {
        $count = 0;
        foreach ($this->streaks->studentsWithExpiringStreak() as $row) {
            $this->notifications->notify(
                (int) $row['student_id'],
                'streak_reminder',
                'Keep your streak alive!',
                "You're on a {$row['current_streak']}-day study streak. Do one activity today to keep it going.",
                ['current_streak' => (int) $row['current_streak']],
                false
            );
            $count++;
        }
        return $count;
    }
}
