<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AnalyticsRepository;
use App\Repositories\AttemptRepository;
use App\Repositories\StreakRepository;
use App\Services\StudyService;

/**
 * Study streaks, achievement badges, and the performance analytics dashboard.
 */
class ProgressController extends BaseController
{
    public function __construct(
        private StreakRepository $streaks,
        private AnalyticsRepository $analytics,
        private AttemptRepository $attempts,
        private StudyService $study
    ) {}

    /** Record a study activity ping (e.g. daily login) from the client. */
    public function ping(Request $request): void
    {
        $type = $request->input('type', 'login');
        $result = $this->study->recordActivity($request->userId(), $type);
        Response::success($result);
    }

    public function streak(Request $request): void
    {
        $studentId = $request->userId();
        $streak = $this->streaks->getStreak($studentId);

        $weekFrom  = date('Y-m-d', strtotime('-6 days'));
        $monthFrom = date('Y-m-d', strtotime('-34 days'));
        $today = date('Y-m-d');

        $badgesAll = $this->streaks->allBadges();
        $earnedIds = $this->streaks->earnedBadgeIds($studentId);
        $badges = array_map(function ($b) use ($earnedIds) {
            $b['earned'] = in_array((int) $b['id'], $earnedIds, true);
            return $b;
        }, $badgesAll);

        Response::success([
            'current_streak'  => (int) $streak['current_streak'],
            'longest_streak'  => (int) $streak['longest_streak'],
            'last_activity'   => $streak['last_activity_date'],
            'active_today'    => ($streak['last_activity_date'] ?? null) === $today,
            'week'            => $this->streaks->activityBetween($studentId, $weekFrom, $today),
            'month'           => $this->streaks->activityBetween($studentId, $monthFrom, $today),
            'badges'          => $badges,
        ]);
    }

    public function analytics(Request $request): void
    {
        $studentId = $request->userId();

        $overall = $this->analytics->overallMcqStats($studentId);
        $topic = $this->analytics->accuracyByTopic($studentId);
        $flash = $this->analytics->flashcardStats($studentId);

        $timeStudied = (int) ($overall['mcq_time'] ?? 0) + $this->analytics->lectureWatchTime($studentId);

        // Weakest / strongest topics (topic list is already sorted ascending by accuracy).
        $weak = array_slice($topic, 0, 5);
        $strong = array_reverse(array_slice($topic, -5));

        Response::success([
            'overall' => [
                'attempts'          => (int) ($overall['attempts'] ?? 0),
                'questions_answered'=> (int) ($overall['total_questions'] ?? 0),
                'correct'           => (int) ($overall['correct'] ?? 0),
                'average_score'     => round((float) ($overall['avg_score'] ?? 0)),
                'time_studied_min'  => (int) round($timeStudied / 60),
                'lectures_completed'=> $this->analytics->lecturesCompleted($studentId),
                'flashcards_reviewed'=> (int) ($flash['reviewed'] ?? 0),
                'flashcards_mastered'=> (int) ($flash['mastered'] ?? 0),
                'revision_sessions' => $this->analytics->activityTypeCount($studentId, 'revision'),
            ],
            'topic_accuracy'      => $topic,
            'difficulty_accuracy' => $this->analytics->accuracyByDifficulty($studentId),
            'daily_activity'      => $this->analytics->dailyActivity($studentId, 30),
            'weekly_progress'     => $this->analytics->weeklyProgress($studentId, 8),
            'weak_topics'         => $weak,
            'strong_topics'       => $strong,
            'recommended_topics'  => array_column($weak, 'topic'),
            'recent_attempts'     => $this->attempts->recentByStudent($studentId, 10),
        ]);
    }

    public function badges(Request $request): void
    {
        $studentId = $request->userId();
        $earnedIds = $this->streaks->earnedBadgeIds($studentId);
        $badges = array_map(function ($b) use ($earnedIds) {
            $b['earned'] = in_array((int) $b['id'], $earnedIds, true);
            return $b;
        }, $this->streaks->allBadges());
        Response::success($badges);
    }
}
