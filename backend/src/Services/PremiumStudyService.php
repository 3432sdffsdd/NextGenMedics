<?php
namespace App\Services;

use App\Repositories\AnalyticsRepository;
use App\Repositories\AttemptRepository;
use App\Repositories\CourseRepository;
use App\Repositories\DailyChallengeSetRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\McqRepository;
use App\Repositories\MistakeRepository;
use App\Repositories\RevisionSessionRepository;
use App\Repositories\StudyPlanRepository;

class PremiumStudyService
{
    private const DAILY_MCQ_COUNT = 10;
    private const DAILY_MINUTES = 10;

    public function __construct(
        private CourseRepository $courses,
        private McqRepository $mcqs,
        private DailyChallengeSetRepository $dailySets,
        private MistakeRepository $mistakes,
        private StudyPlanRepository $plans,
        private RevisionSessionRepository $revisionSessions,
        private AnalyticsRepository $analytics,
        private AttemptRepository $attempts,
        private FlashcardRepository $flashcards
    ) {}

    public function dashboardSummary(int $studentId): array
    {
        $today = date('Y-m-d');
        $daily = $this->buildDailyChallenge($studentId, false);
        $mistakeStats = $this->mistakes->stats($studentId);
        $plan = $this->plans->getPlan($studentId);
        $todayTasks = $plan ? $this->plans->tasksForDate((int) $plan['id'], $today) : [];

        return [
            'daily_challenge' => $daily,
            'weak_areas'      => $this->analytics->weakAreasWithResources($studentId, 5),
            'mistakes'        => $mistakeStats,
            'study_plan'      => $plan ? [
                'exam_date'     => $plan['exam_date'],
                'hours_per_day' => (float) $plan['hours_per_day'],
                'today_tasks'   => $todayTasks,
            ] : null,
            'recent_performance' => [
                'weekly' => $this->analytics->weeklyProgress($studentId, 4),
                'overall'=> $this->analytics->overallMcqStats($studentId),
            ],
        ];
    }

    public function buildDailyChallenge(int $studentId, bool $withQuestions = true): array
    {
        $today = date('Y-m-d');
        $completed = $this->dailySets->dailyAttemptExists($studentId, $today);
        $secondsUntilMidnight = strtotime('tomorrow') - time();
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');

        if ($completed) {
            $recent = $this->attempts->recentByStudent($studentId, 1);
            $last = $recent[0] ?? null;
            return [
                'available'             => true,
                'date'                  => $today,
                'completed'             => true,
                'duration_minutes'      => self::DAILY_MINUTES,
                'total_questions'       => self::DAILY_MCQ_COUNT,
                'seconds_until_next'    => $secondsUntilMidnight,
                'last_score'            => $last['score'] ?? null,
                'last_time_spent'       => $last['time_spent_seconds'] ?? null,
                'questions'             => [],
            ];
        }

        if (!$courseIds) {
            return $this->unavailableChallenge($today, $secondsUntilMidnight, 'not_enrolled',
                'Enroll in a course to unlock the daily challenge.');
        }

        $poolSize = $this->mcqs->countAvailableForStudent($courseIds);
        if ($poolSize === 0) {
            return $this->unavailableChallenge($today, $secondsUntilMidnight, 'no_questions',
                'No Study Tools MCQs are published yet. Course quizzes are separate — ask your teacher to generate and publish MCQs from the Study Tools tab on each lecture.');
        }

        $set = $this->dailySets->findToday($studentId, $today);
        $mcqIds = $set['mcq_ids'] ?? [];
        if (!$set || !$mcqIds) {
            $ids = $this->mcqs->randomFromCompletedLectures($studentId, $courseIds, self::DAILY_MCQ_COUNT);
            if (count($ids) < self::DAILY_MCQ_COUNT) {
                $ids = array_merge($ids, $this->mcqs->randomPublishedByCourses($courseIds, self::DAILY_MCQ_COUNT - count($ids), $ids));
            }
            $mcqIds = array_values(array_unique(array_slice($ids, 0, self::DAILY_MCQ_COUNT)));
            $this->dailySets->create($studentId, $today, $mcqIds);
            $set = $this->dailySets->findToday($studentId, $today);
        }

        if (!$mcqIds) {
            return $this->unavailableChallenge($today, $secondsUntilMidnight, 'no_questions',
                'Not enough Study Tools MCQs are available yet. Practice lecture MCQs or ask your teacher to publish more.');
        }

        $questionCount = count($mcqIds);
        return [
            'available'          => true,
            'date'               => $today,
            'completed'          => false,
            'duration_minutes'   => self::DAILY_MINUTES,
            'total_questions'    => $questionCount,
            'target_questions'   => self::DAILY_MCQ_COUNT,
            'seconds_until_next' => $secondsUntilMidnight,
            'questions'          => $withQuestions ? $this->mcqs->findByIds($mcqIds, false) : [],
            'daily_set_id'       => (int) ($set['id'] ?? 0),
        ];
    }

    private function unavailableChallenge(string $today, int $secondsUntilMidnight, string $reason, string $message): array
    {
        return [
            'available'          => false,
            'reason'             => $reason,
            'message'            => $message,
            'date'               => $today,
            'completed'          => false,
            'duration_minutes'   => self::DAILY_MINUTES,
            'total_questions'    => 0,
            'target_questions'   => self::DAILY_MCQ_COUNT,
            'seconds_until_next' => $secondsUntilMidnight,
            'questions'          => [],
            'daily_set_id'       => 0,
        ];
    }

    public function saveStudyPlan(int $studentId, string $examDate, float $hoursPerDay): array
    {
        $planId = $this->plans->upsert($studentId, $examDate, $hoursPerDay);
        $this->plans->clearFutureTasks($planId, date('Y-m-d'));
        $tasks = $this->generatePlanTasks($studentId, $planId, $examDate, $hoursPerDay);
        if ($tasks) {
            $this->plans->addTasks($planId, $tasks);
        }
        return [
            'plan'  => $this->plans->getPlan($studentId),
            'tasks' => $this->plans->tasksRange($planId, date('Y-m-d'), date('Y-m-d', strtotime('+6 days'))),
        ];
    }

    public function startRevisionSession(int $studentId): array
    {
        $items = [];
        $mistakeIds = $this->mistakes->practiceIds($studentId, 5);
        foreach ($mistakeIds as $id) {
            $items[] = ['item_type' => 'mcq', 'item_id' => $id];
        }
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        $extraMcq = $this->mcqs->randomFromCompletedLectures($studentId, $courseIds, max(0, 5 - count($mistakeIds)));
        foreach ($extraMcq as $id) {
            if (!in_array($id, $mistakeIds, true)) {
                $items[] = ['item_type' => 'mcq', 'item_id' => $id];
            }
        }
        $cards = $this->flashcards->listForStudent($studentId, ['difficult' => 1]);
        foreach (array_slice($cards, 0, 3) as $card) {
            $items[] = ['item_type' => 'flashcard', 'item_id' => (int) $card['id']];
        }

        $sessionId = $this->revisionSessions->create($studentId, $items);
        $session = $this->revisionSessions->find($sessionId);
        $mcqIds = array_column(array_filter($items, fn($i) => $i['item_type'] === 'mcq'), 'item_id');
        return [
            'session_id' => $sessionId,
            'items'      => $session['items'] ?? [],
            'mcqs'       => $this->mcqs->findByIds($mcqIds, false),
            'flashcards' => array_values(array_filter($cards, fn($c, $i) => $i < 3, ARRAY_FILTER_USE_BOTH)),
        ];
    }

    private function generatePlanTasks(int $studentId, int $planId, string $examDate, float $hoursPerDay): array
    {
        $weak = $this->analytics->weakAreasWithResources($studentId, 3);
        $tasks = [];
        $day = new \DateTimeImmutable('today');
        $end = new \DateTimeImmutable($examDate);
        $sort = 0;

        for ($i = 0; $i < 7 && $day <= $end; $i++, $day = $day->modify('+1 day')) {
            $date = $day->format('Y-m-d');
            $weakArea = $weak[$i % max(1, count($weak))] ?? null;
            if ($weakArea && $weakArea['lecture_id']) {
                $tasks[] = [
                    'task_date' => $date, 'task_type' => 'lecture', 'sort_order' => $sort++,
                    'title' => 'Watch: ' . ($weakArea['lecture_title'] ?? $weakArea['subject']),
                    'lecture_id' => (int) $weakArea['lecture_id'],
                ];
            }
            $mcqCount = min(30, max(10, (int) round($hoursPerDay * 10)));
            $tasks[] = [
                'task_date' => $date, 'task_type' => 'mcq', 'sort_order' => $sort++,
                'title' => "Solve {$mcqCount} MCQs", 'target_count' => $mcqCount,
            ];
            $tasks[] = [
                'task_date' => $date, 'task_type' => 'flashcard', 'sort_order' => $sort++,
                'title' => 'Revise flashcards', 'target_count' => 15,
            ];
            $tasks[] = [
                'task_date' => $date, 'task_type' => 'revision', 'sort_order' => $sort++,
                'title' => 'Read revision notes',
            ];
        }
        return $tasks;
    }
}
