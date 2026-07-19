<?php
namespace App\Services;

use App\Repositories\AnalyticsRepository;
use App\Repositories\AttemptRepository;
use App\Repositories\CourseRepository;
use App\Repositories\DailyChallengeSetRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\McqRepository;
use App\Repositories\MistakeRepository;
use App\Repositories\QuizQuestionBankRepository;
use App\Repositories\RevisionSessionRepository;
use App\Repositories\StudentQuestionHistoryRepository;
use App\Repositories\StudyPlanRepository;
use App\Repositories\StreakRepository;

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
        private FlashcardRepository $flashcards,
        private QuizQuestionBankRepository $quizBank,
        private StudentQuestionHistoryRepository $questionHistory,
        private StreakRepository $streaks,
        private StudyService $study
    ) {}

    public function dashboardSummary(int $studentId): array
    {
        $today = date('Y-m-d');
        $daily = $this->buildDailyChallenge($studentId, false);
        $plan = $this->plans->getPlan($studentId);
        $todayTasks = $plan ? $this->plans->tasksForDate((int) $plan['id'], $today) : [];
        $weak = $this->weakAreasSummary($studentId, 5);
        $perf = $this->performanceStats($studentId);
        // Same source as My Mistakes page (course quizzes + Daily Challenge)
        $mistakeStats = $this->quizMistakeStats($studentId);

        return [
            'daily_challenge' => $daily,
            'weak_areas'      => $weak,
            'mistakes'        => $mistakeStats,
            'performance'     => $perf,
            'study_plan'      => $plan ? [
                'exam_date'     => $plan['exam_date'],
                'hours_per_day' => (float) $plan['hours_per_day'],
                'today_tasks'   => $todayTasks,
            ] : null,
            'recent_performance' => [
                'weekly' => $this->analytics->weeklyProgress($studentId, 4),
                // Quiz-bank stats only (not Gemini / Study Tools MCQs)
                'overall'=> (static function (array $s) {
                    return [
                        'attempts'        => $s['attempted'],
                        'total_questions' => $s['attempted'],
                        'correct'         => $s['correct'],
                        'avg_score'       => $s['accuracy'],
                        'mcq_time'        => 0,
                    ];
                })($this->quizBank->overallStats($studentId)),
            ],
        ];
    }

    /**
     * Build (or resume) today's Daily Challenge from teacher-uploaded quiz questions.
     * Persists the question set so logout/login same day continues the same challenge.
     */
    public function buildDailyChallenge(int $studentId, bool $withQuestions = true): array
    {
        $today = date('Y-m-d');
        $secondsUntilMidnight = strtotime('tomorrow') - time();
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');

        $set = $this->dailySets->findToday($studentId, $today);
        if ($set && !empty($set['completed_at'])) {
            return [
                'available'          => true,
                'date'               => $today,
                'completed'          => true,
                'duration_minutes'   => self::DAILY_MINUTES,
                'total_questions'    => self::DAILY_MCQ_COUNT,
                'seconds_until_next' => $secondsUntilMidnight,
                'last_score'         => $set['score'] ?? null,
                'last_time_spent'    => $set['time_spent_seconds'] ?? null,
                'questions'          => [],
                'daily_set_id'       => (int) $set['id'],
            ];
        }

        if (!$courseIds) {
            return $this->unavailableChallenge($today, $secondsUntilMidnight, 'not_enrolled',
                'Enroll in a course to unlock the daily challenge.');
        }

        $poolSize = $this->quizBank->countAvailableForCourses($courseIds);
        if ($poolSize === 0) {
            return $this->unavailableChallenge($today, $secondsUntilMidnight, 'no_questions',
                'No published course quizzes with MCQs yet. Ask your teacher to upload and publish quizzes.');
        }

        // Resume today's set if already generated
        $questionIds = $set['quiz_question_ids'] ?? [];
        if (!$set || !$questionIds) {
            $this->questionHistory->syncFromQuizAttempts($studentId);
            $questionIds = $this->selectDailyQuestionIds($studentId, $courseIds, self::DAILY_MCQ_COUNT);
            if (count($questionIds) < 1) {
                return $this->unavailableChallenge($today, $secondsUntilMidnight, 'no_questions',
                    'Not enough quiz questions are available yet.');
            }
            $setId = $this->dailySets->createQuizSet($studentId, $today, $questionIds);
            $set = $this->dailySets->findToday($studentId, $today) ?: ['id' => $setId];
        }

        $questionCount = count($questionIds);
        return [
            'available'          => true,
            'date'               => $today,
            'completed'          => false,
            'duration_minutes'   => self::DAILY_MINUTES,
            'total_questions'    => $questionCount,
            'target_questions'   => self::DAILY_MCQ_COUNT,
            'seconds_until_next' => $secondsUntilMidnight,
            'questions'          => $withQuestions ? $this->quizBank->findByIds($questionIds, false) : [],
            'daily_set_id'       => (int) ($set['id'] ?? 0),
            'question_source'    => 'quiz',
        ];
    }

    /**
     * Pick up to $limit unseen quiz questions. If the student has exhausted the bank, reset history and reshuffle.
     * @return list<int>
     */
    private function selectDailyQuestionIds(int $studentId, array $courseIds, int $limit): array
    {
        $seen = $this->questionHistory->seenQuestionIds($studentId);
        $ids = $this->quizBank->pickRandomIds($courseIds, $limit, $seen);

        if (count($ids) < $limit) {
            $pool = $this->quizBank->countAvailableForCourses($courseIds);
            if ($pool > 0 && count($seen) >= $pool) {
                // Entire bank seen — start a new cycle
                $this->questionHistory->resetForStudent($studentId);
                $ids = $this->quizBank->pickRandomIds($courseIds, $limit, []);
            } elseif (count($ids) < $limit) {
                // Partial fill from remaining + allow reshuffle of older ones only if needed
                $need = $limit - count($ids);
                $extra = $this->quizBank->pickRandomIds($courseIds, $need, $ids);
                $ids = array_values(array_unique(array_merge($ids, $extra)));
            }
        }

        return array_values(array_slice($ids, 0, $limit));
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

    /**
     * Grade and persist today's Daily Challenge. Answers: [{mcq_id|question_id, selected_option}]
     */
    public function submitDailyChallenge(int $studentId, int $dailySetId, array $answers, int $timeSpentSeconds = 0): array
    {
        $today = date('Y-m-d');
        $set = $this->dailySets->findByIdForStudent($dailySetId, $studentId);
        if (!$set || ($set['challenge_date'] ?? '') !== $today) {
            throw new \RuntimeException('Daily challenge set not found for today.');
        }
        if (!empty($set['completed_at'])) {
            throw new \RuntimeException('You have already completed today\'s challenge.');
        }

        $questionIds = $set['quiz_question_ids'] ?? [];
        if (!$questionIds) {
            throw new \RuntimeException('No questions in today\'s challenge.');
        }

        $questions = $this->quizBank->findByIds($questionIds, true);
        $byId = [];
        foreach ($questions as $q) {
            $byId[(int) $q['id']] = $q;
        }

        $answerMap = [];
        foreach ($answers as $a) {
            $qid = (int) ($a['mcq_id'] ?? $a['question_id'] ?? 0);
            if ($qid) {
                $sel = isset($a['selected_option']) ? strtoupper((string) $a['selected_option']) : null;
                $answerMap[$qid] = in_array($sel, ['A', 'B', 'C', 'D', 'E'], true) ? $sel : null;
            }
        }

        $correct = 0;
        $review = [];
        foreach ($questionIds as $qid) {
            $qid = (int) $qid;
            $q = $byId[$qid] ?? null;
            if (!$q) {
                continue;
            }
            $selected = $answerMap[$qid] ?? null;
            $isCorrect = $selected !== null && $selected === ($q['correct_option'] ?? null);
            if ($isCorrect) {
                $correct++;
            }
            $this->dailySets->saveAnswer($dailySetId, $studentId, $qid, $selected, $isCorrect);
            $this->questionHistory->record(
                $studentId,
                $qid,
                $isCorrect,
                $selected,
                $today,
                $today,
                $dailySetId,
                'daily'
            );

            $options = [];
            foreach (['A', 'B', 'C', 'D', 'E'] as $l) {
                $options[$l] = $q['option_' . strtolower($l)] ?? '';
            }
            $review[] = [
                'mcq_id'          => $qid,
                'question'        => $q['question'],
                'options'         => $options,
                'correct_option'  => $q['correct_option'] ?? null,
                'selected_option' => $selected,
                'is_correct'      => $isCorrect,
                'explanation'     => $q['explanation'] ?? null,
                'topic'           => $q['topic'] ?? null,
                'subject'         => $q['subject'] ?? null,
                'chapter'         => $q['chapter'] ?? null,
            ];
        }

        $total = count($questionIds);
        $wrong = max(0, $total - $correct);
        $score = $total > 0 ? round($correct / $total * 100, 2) : 0.0;

        // Lightweight attempt row for streak / legacy history (no mcq_attempt_answers — quiz IDs)
        $attemptId = $this->attempts->create($studentId, [
            'source'          => 'daily',
            'lecture_id'      => null,
            'total_questions' => $total,
        ]);
        $this->attempts->finalize($attemptId, $correct, $wrong, $score, $timeSpentSeconds);
        $this->dailySets->markCompleted($dailySetId, $attemptId, $correct, $wrong, $score, $timeSpentSeconds);

        $study = $this->study->recordActivity($studentId, 'mcq');

        return [
            'attempt_id' => $attemptId,
            'total'      => $total,
            'correct'    => $correct,
            'wrong'      => $wrong,
            'score'      => $score,
            'time_spent' => $timeSpentSeconds,
            'review'     => $review,
            'new_badges' => $study['new_badges'] ?? [],
            'streak'     => $study['streak'] ?? null,
        ];
    }

    public function dailyHistory(int $studentId, int $limit = 30): array
    {
        $rows = $this->dailySets->recentCompleted($studentId, $limit);
        return array_map(static function ($r) {
            return [
                'id'                 => (int) $r['id'],
                'submitted_at'       => $r['completed_at'] ?? $r['challenge_date'],
                'score'              => (float) ($r['score'] ?? 0),
                'correct_count'      => (int) ($r['correct_count'] ?? 0),
                'wrong_count'        => (int) ($r['wrong_count'] ?? 0),
                'time_spent_seconds' => (int) ($r['time_spent_seconds'] ?? 0),
                'challenge_date'     => $r['challenge_date'],
            ];
        }, $rows);
    }

    public function weakAreasSummary(int $studentId, int $limit = 5): array
    {
        $this->questionHistory->syncFromQuizAttempts($studentId);
        $areas = array_values(array_filter(
            $this->quizBank->accuracyByTopic($studentId),
            fn($a) => (int) $a['accuracy'] < 75
        ));
        $out = [];
        foreach (array_slice($areas, 0, $limit) as $area) {
            $topic = (string) ($area['topic'] ?? $area['subject'] ?? 'Topic');
            $acc = (int) $area['accuracy'];
            $out[] = [
                'topic'      => $topic,
                'subject'    => $topic,
                'accuracy'   => $acc,
                'total'      => (int) $area['total'],
                'correct'    => (int) $area['correct'],
                'message'    => "You need more practice in {$topic}.",
            ];
        }
        return $out;
    }

    public function weakAreasDetail(int $studentId, int $incorrectPage = 1, int $perPage = 20): array
    {
        $this->questionHistory->syncFromQuizAttempts($studentId);
        $overall = $this->quizBank->overallStats($studentId);
        $topics = $this->quizBank->accuracyByTopic($studentId);
        $incorrectTotal = $this->questionHistory->countIncorrect($studentId);
        $offset = max(0, ($incorrectPage - 1) * $perPage);
        $incorrectRows = $this->questionHistory->incorrectQuestions($studentId, $perPage, $offset);

        $incorrect = [];
        foreach ($incorrectRows as $row) {
            $q = $this->quizBank->findOneWithOptions((int) $row['question_id'], true);
            if (!$q) {
                continue;
            }
            $options = [];
            foreach (['A', 'B', 'C', 'D', 'E'] as $l) {
                $options[$l] = $q['option_' . strtolower($l)] ?? '';
            }
            $topic = $row['quiz_title'] ?? $q['topic'];
            $incorrect[] = [
                'question_id'     => (int) $row['question_id'],
                'question'        => $q['question'],
                'student_answer'  => $row['selected_option'],
                'correct_answer'  => $q['correct_option'] ?? null,
                'explanation'     => $q['explanation'] ?? null,
                'subject'         => $topic,
                'topic'           => $topic,
                'chapter'         => $topic,
                'source'          => $row['source'] ?? null,
                'date_attempted'  => $row['attempt_date'],
                'options'         => $options,
            ];
        }

        // Only topics below threshold — never pad with strong topics
        $weakTopics = array_values(array_filter($topics, fn($s) => (int) $s['accuracy'] < 75));

        return [
            'overall_accuracy' => $overall['accuracy'],
            'stats'            => $overall,
            'weak_topics'      => $weakTopics,
            'weak_subjects'    => $weakTopics,
            'weak_chapters'    => array_slice($weakTopics, 0, 15),
            'most_incorrect'   => [
                'items'    => $incorrect,
                'total'    => $incorrectTotal,
                'page'     => $incorrectPage,
                'per_page' => $perPage,
            ],
            'performance'      => $this->performanceStats($studentId),
        ];
    }

    /** My Mistakes — incorrect items from Daily Challenge + course quizzes. */
    public function quizMistakes(int $studentId, array $filters, int $page = 1, int $perPage = 20): array
    {
        $this->questionHistory->syncFromQuizAttempts($studentId);
        $result = $this->questionHistory->listMistakes($studentId, $filters, $page, $perPage);
        $items = [];
        foreach ($result['items'] ?? [] as $row) {
            $q = $this->quizBank->findOneWithOptions((int) ($row['mcq_id'] ?? 0), true);
            $options = [];
            if ($q) {
                foreach (['A', 'B', 'C', 'D', 'E'] as $l) {
                    $options[$l] = $q['option_' . strtolower($l)] ?? '';
                }
            }
            $selected = $row['selected_option'] ?? null;
            if (is_string($selected)) {
                $selected = strtoupper(trim($selected));
                if ($selected === '') {
                    $selected = null;
                }
            }
            $correct = $q['correct_option'] ?? null;
            $items[] = array_merge($row, [
                'selected_option' => $selected,
                'selected_option_text' => ($selected && isset($options[$selected])) ? $options[$selected] : null,
                'correct_option' => $correct,
                'correct_option_text' => ($correct && isset($options[$correct])) ? $options[$correct] : null,
                'options' => $options,
            ]);
        }
        $result['items'] = $items;
        return $result;
    }

    public function quizMistakeStats(int $studentId): array
    {
        $this->questionHistory->syncFromQuizAttempts($studentId);
        $remaining = $this->questionHistory->countIncorrect($studentId);
        $mastered = $this->questionHistory->countMasteredMistakes($studentId);
        return [
            // Total = open mistakes + ones cleared via practice (not all quiz corrects)
            'total'     => $remaining + $mastered,
            'remaining' => $remaining,
            'mastered'  => $mastered,
            'incorrect' => $remaining,
            'correct'   => $mastered,
        ];
    }

    /** Practice set from previously incorrect quiz questions. limit: 10|20|0(all) */
    public function practiceWeakAreas(int $studentId, int $limit = 10): array
    {
        $this->questionHistory->syncFromQuizAttempts($studentId);
        $ids = $this->questionHistory->incorrectQuestionIds($studentId, $limit > 0 ? $limit : 0);
        if (!$ids) {
            return ['questions' => [], 'total' => 0];
        }
        if ($limit > 0) {
            $ids = array_slice($ids, 0, $limit);
        }
        shuffle($ids);
        return [
            'questions' => $this->quizBank->findByIds($ids, false),
            'total'     => count($ids),
        ];
    }

    /**
     * Submit weak-area practice (quiz questions). Records history updates.
     */
    public function submitWeakPractice(int $studentId, array $answers, int $timeSpentSeconds = 0): array
    {
        return $this->submitQuizPracticeAnswers($studentId, $answers, $timeSpentSeconds, 'weak');
    }

    /** Question Bank practice submit → quiz history + Study Tools attempts / mistakes. */
    public function submitBankPractice(int $studentId, array $answers, int $timeSpentSeconds = 0): array
    {
        $quizAnswers = [];
        $studyAnswers = [];
        foreach ($answers as $a) {
            $sourceType = (string) ($a['source_type'] ?? '');
            $bankId = (string) ($a['bank_id'] ?? $a['mcq_id'] ?? '');
            if ($sourceType === '' && is_string($bankId)) {
                if (str_starts_with($bankId, 'study-')) {
                    $sourceType = 'study';
                } elseif (str_starts_with($bankId, 'quiz-')) {
                    $sourceType = 'quiz';
                }
            }
            if ($sourceType === 'study') {
                $raw = (int) ($a['raw_id'] ?? 0);
                if ($raw <= 0 && str_starts_with($bankId, 'study-')) {
                    $raw = (int) substr($bankId, 6);
                }
                if ($raw <= 0) {
                    $raw = (int) ($a['mcq_id'] ?? 0);
                }
                if ($raw > 0) {
                    $studyAnswers[] = array_merge($a, ['mcq_id' => $raw, 'bank_id' => 'study-' . $raw]);
                }
            } else {
                $raw = (int) ($a['raw_id'] ?? 0);
                if ($raw <= 0 && str_starts_with($bankId, 'quiz-')) {
                    $raw = (int) substr($bankId, 5);
                }
                if ($raw <= 0) {
                    $raw = (int) ($a['mcq_id'] ?? $a['question_id'] ?? 0);
                }
                if ($raw > 0) {
                    $quizAnswers[] = array_merge($a, ['mcq_id' => $raw, 'bank_id' => 'quiz-' . $raw]);
                }
            }
        }

        $quizResult = $quizAnswers
            ? $this->submitQuizPracticeAnswers($studentId, $quizAnswers, $timeSpentSeconds, 'practice')
            : ['total' => 0, 'correct' => 0, 'wrong' => 0, 'score' => 0, 'review' => []];
        $studyResult = $studyAnswers
            ? $this->submitStudyToolBankAnswers($studentId, $studyAnswers, $timeSpentSeconds)
            : ['total' => 0, 'correct' => 0, 'wrong' => 0, 'score' => 0, 'review' => []];

        $total = (int) $quizResult['total'] + (int) $studyResult['total'];
        $correct = (int) $quizResult['correct'] + (int) $studyResult['correct'];
        $wrong = max(0, $total - $correct);
        $score = $total > 0 ? round($correct / $total * 100, 2) : 0.0;

        return [
            'total'      => $total,
            'correct'    => $correct,
            'wrong'      => $wrong,
            'score'      => $score,
            'time_spent' => $timeSpentSeconds,
            'review'     => array_merge($quizResult['review'] ?? [], $studyResult['review'] ?? []),
        ];
    }

    private function submitStudyToolBankAnswers(int $studentId, array $answers, int $timeSpentSeconds): array
    {
        $ids = [];
        $answerMap = [];
        $bankMap = [];
        foreach ($answers as $a) {
            $qid = (int) ($a['mcq_id'] ?? 0);
            if (!$qid) {
                continue;
            }
            $ids[] = $qid;
            $sel = isset($a['selected_option']) ? strtoupper((string) $a['selected_option']) : null;
            $answerMap[$qid] = in_array($sel, ['A', 'B', 'C', 'D', 'E'], true) ? $sel : null;
            $bankMap[$qid] = (string) ($a['bank_id'] ?? ('study-' . $qid));
        }
        $ids = array_values(array_unique($ids));
        $questions = $this->mcqs->findByIds($ids, true);
        $byId = [];
        foreach ($questions as $q) {
            $byId[(int) $q['id']] = $q;
        }
        if (!$byId) {
            return ['total' => 0, 'correct' => 0, 'wrong' => 0, 'score' => 0, 'review' => []];
        }

        $lectureId = (int) ($questions[0]['lecture_id'] ?? 0);
        $attemptId = $this->attempts->create($studentId, [
            'source'          => 'bank',
            'lecture_id'      => $lectureId ?: null,
            'total_questions' => count($ids),
        ]);

        $correct = 0;
        $review = [];
        foreach ($ids as $qid) {
            $q = $byId[$qid] ?? null;
            if (!$q) {
                continue;
            }
            $selected = $answerMap[$qid] ?? null;
            $isCorrect = $selected !== null && $selected === ($q['correct_option'] ?? null);
            if ($isCorrect) {
                $correct++;
            }
            $this->attempts->addAnswer($attemptId, $qid, $selected, $isCorrect, 0);
            $this->mistakes->recordAnswer($studentId, $qid, $isCorrect);
            $review[] = [
                'mcq_id'          => $bankMap[$qid] ?? ('study-' . $qid),
                'question'        => $q['question'],
                'options'         => [
                    'A' => $q['option_a'] ?? '',
                    'B' => $q['option_b'] ?? '',
                    'C' => $q['option_c'] ?? '',
                    'D' => $q['option_d'] ?? '',
                    'E' => $q['option_e'] ?? '',
                ],
                'correct_option'  => $q['correct_option'] ?? null,
                'selected_option' => $selected,
                'is_correct'      => $isCorrect,
                'explanation'     => $q['explanation'] ?? null,
                'topic'           => $q['topic'] ?? null,
            ];
        }
        $total = count($ids);
        $wrong = max(0, $total - $correct);
        $score = $total > 0 ? round($correct / $total * 100, 2) : 0.0;
        $this->attempts->finalize($attemptId, $correct, $wrong, $score, $timeSpentSeconds);
        $this->study->recordActivity($studentId, 'mcq');

        return [
            'total'      => $total,
            'correct'    => $correct,
            'wrong'      => $wrong,
            'score'      => $score,
            'time_spent' => $timeSpentSeconds,
            'review'     => $review,
        ];
    }

    private function submitQuizPracticeAnswers(
        int $studentId,
        array $answers,
        int $timeSpentSeconds,
        string $source
    ): array {
        $ids = [];
        $answerMap = [];
        $bankMap = [];
        foreach ($answers as $a) {
            $qid = (int) ($a['raw_id'] ?? 0);
            if ($qid <= 0) {
                $qid = (int) ($a['mcq_id'] ?? $a['question_id'] ?? 0);
            }
            $bankId = (string) ($a['bank_id'] ?? '');
            if ($qid <= 0 && str_starts_with($bankId, 'quiz-')) {
                $qid = (int) substr($bankId, 5);
            }
            if ($qid <= 0 && is_string($a['mcq_id'] ?? null) && str_starts_with((string) $a['mcq_id'], 'quiz-')) {
                $qid = (int) substr((string) $a['mcq_id'], 5);
            }
            if ($qid <= 0) {
                continue;
            }
            $ids[] = $qid;
            $sel = isset($a['selected_option']) ? strtoupper((string) $a['selected_option']) : null;
            $answerMap[$qid] = in_array($sel, ['A', 'B', 'C', 'D', 'E'], true) ? $sel : null;
            $bankMap[$qid] = $bankId !== '' ? $bankId : ('quiz-' . $qid);
        }
        $ids = array_values(array_unique($ids));
        $questions = $this->quizBank->findByIds($ids, true);
        $byId = [];
        foreach ($questions as $q) {
            $byId[(int) $q['id']] = $q;
        }

        $correct = 0;
        $review = [];
        $today = date('Y-m-d');
        foreach ($ids as $qid) {
            $q = $byId[$qid] ?? null;
            if (!$q) {
                continue;
            }
            $selected = $answerMap[$qid] ?? null;
            $isCorrect = $selected !== null && $selected === ($q['correct_option'] ?? null);
            if ($isCorrect) {
                $correct++;
            }
            $this->questionHistory->record($studentId, $qid, $isCorrect, $selected, $today, null, null, $source);
            $options = [];
            foreach (['A', 'B', 'C', 'D', 'E'] as $l) {
                $options[$l] = $q['option_' . strtolower($l)] ?? '';
            }
            $review[] = [
                'mcq_id'          => $bankMap[$qid] ?? $qid,
                'question'        => $q['question'],
                'options'         => $options,
                'correct_option'  => $q['correct_option'] ?? null,
                'selected_option' => $selected,
                'is_correct'      => $isCorrect,
                'explanation'     => $q['explanation'] ?? null,
                'topic'           => $q['topic'] ?? null,
            ];
        }
        $total = count($ids);
        $wrong = max(0, $total - $correct);
        $score = $total > 0 ? round($correct / $total * 100, 2) : 0.0;

        return [
            'total'      => $total,
            'correct'    => $correct,
            'wrong'      => $wrong,
            'score'      => $score,
            'time_spent' => $timeSpentSeconds,
            'review'     => $review,
        ];
    }

    public function questionBankFilters(int $studentId): array
    {
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        $quizTopics = $this->quizBank->topicsForCourses($courseIds);
        $studyTopics = $this->mcqs->topicsForCourses($courseIds);
        $merged = [];
        foreach (array_merge($quizTopics, $studyTopics) as $t) {
            $title = (string) ($t['title'] ?? '');
            if ($title === '') {
                continue;
            }
            if (!isset($merged[$title])) {
                $merged[$title] = [
                    'id'             => (int) ($t['id'] ?? 0),
                    'title'          => $title,
                    'course_title'   => (string) ($t['course_title'] ?? ''),
                    'question_count' => 0,
                ];
            }
            $merged[$title]['question_count'] += (int) ($t['question_count'] ?? 0);
        }
        $topics = array_values($merged);
        usort($topics, static fn($a, $b) => strcasecmp($a['title'], $b['title']));
        return [
            'topics'  => array_map(static fn($t) => $t['title'], $topics),
            'quizzes' => $topics,
        ];
    }

    public function questionBank(int $studentId, array $filters, int $page = 1, int $perPage = 20): array
    {
        $this->questionHistory->syncFromQuizAttempts($studentId);
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        $quiz = $this->quizBank->searchBank($studentId, $courseIds, $filters, 1, 500);
        $study = $this->mcqs->listBankItems($studentId, $courseIds, $filters, 500);

        $items = [];
        foreach ($quiz['items'] ?? [] as $row) {
            $id = (int) $row['id'];
            $items[] = array_merge($row, [
                'id'          => $id,
                'bank_id'     => 'quiz-' . $id,
                'source_type' => 'quiz',
            ]);
        }
        foreach ($study as $row) {
            $items[] = $row;
        }

        usort($items, static function ($a, $b) {
            $cmp = strcasecmp((string) ($a['topic'] ?? ''), (string) ($b['topic'] ?? ''));
            return $cmp !== 0 ? $cmp : ((int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0));
        });

        $total = count($items);
        $offset = max(0, ($page - 1) * $perPage);
        return [
            'items'    => array_slice($items, $offset, $perPage),
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    public function questionBankPractice(int $studentId, array $filters, int $limit = 20): array
    {
        $this->questionHistory->syncFromQuizAttempts($studentId);
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        $half = max(1, (int) ceil($limit / 2));
        $quizIds = $this->quizBank->pickBankIds($studentId, $courseIds, $filters, $half);
        $studyIds = $this->mcqs->pickBankIds($studentId, $courseIds, $filters, $limit - count($quizIds));
        // If one source is short, fill from the other
        if (count($quizIds) + count($studyIds) < $limit) {
            $need = $limit - count($quizIds) - count($studyIds);
            if (count($studyIds) < $half) {
                $quizIds = array_values(array_unique(array_merge(
                    $quizIds,
                    $this->quizBank->pickBankIds($studentId, $courseIds, $filters, $need + count($quizIds))
                )));
                $quizIds = array_slice($quizIds, 0, $limit - count($studyIds));
            } else {
                $studyIds = array_values(array_unique(array_merge(
                    $studyIds,
                    $this->mcqs->pickBankIds($studentId, $courseIds, $filters, $need + count($studyIds))
                )));
                $studyIds = array_slice($studyIds, 0, $limit - count($quizIds));
            }
        }

        $questions = [];
        foreach ($this->quizBank->findByIds($quizIds, false) as $q) {
            $id = (int) $q['id'];
            $q['raw_id'] = $id;
            $q['bank_id'] = 'quiz-' . $id;
            $q['source_type'] = 'quiz';
            $q['id'] = $q['bank_id'];
            $questions[] = $q;
        }
        foreach ($this->mcqs->findByIds($studyIds, false) as $q) {
            $id = (int) $q['id'];
            $topic = trim((string) ($q['topic'] ?? $q['lecture_title'] ?? 'Study Tools'));
            $questions[] = [
                'id'           => 'study-' . $id,
                'bank_id'      => 'study-' . $id,
                'raw_id'       => $id,
                'source_type'  => 'study',
                'question'     => (string) $q['question'],
                'option_a'     => $q['option_a'] ?? '',
                'option_b'     => $q['option_b'] ?? '',
                'option_c'     => $q['option_c'] ?? '',
                'option_d'     => $q['option_d'] ?? '',
                'option_e'     => $q['option_e'] ?? '',
                'topic'        => $topic,
                'subject'      => $topic,
                'chapter'      => $topic,
                'explanation'  => null,
            ];
        }
        shuffle($questions);
        $questions = array_slice($questions, 0, $limit);
        return [
            'questions' => $questions,
            'total'     => count($questions),
        ];
    }

    public function performanceStats(int $studentId): array
    {
        $this->questionHistory->syncFromQuizAttempts($studentId);
        $overall = $this->quizBank->overallStats($studentId);
        $topics = $this->quizBank->accuracyByTopic($studentId);
        $weakTopics = array_values(array_filter($topics, fn($t) => (int) $t['accuracy'] < 75));
        usort($weakTopics, fn($a, $b) => (int) $a['accuracy'] <=> (int) $b['accuracy']);
        $weakest = $weakTopics[0] ?? null;

        $streak = $this->streaks->getStreak($studentId);
        $weakTopic = $weakest['topic'] ?? $weakest['subject'] ?? null;

        return [
            'questions_attempted'       => $overall['attempted'],
            'correct'                   => $overall['correct'],
            'incorrect'                 => $overall['incorrect'],
            'accuracy'                  => $overall['accuracy'],
            'current_streak'            => (int) ($streak['current_streak'] ?? 0),
            'daily_challenges_completed'=> $this->quizBank->countDailyCompleted($studentId),
            'weakest_topic'             => $weakTopic,
            'weakest_subject'           => $weakTopic,
            'weakest_accuracy'          => isset($weakest['accuracy']) ? (int) $weakest['accuracy'] : null,
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
        // Prefer incorrect quiz questions when Study Tools MCQs are empty
        if (count($items) < 5) {
            $weakIds = $this->questionHistory->incorrectQuestionIds($studentId, 5 - count($items));
            foreach ($weakIds as $id) {
                $items[] = ['item_type' => 'quiz_question', 'item_id' => $id];
            }
        }
        $cards = $this->flashcards->listForStudent($studentId, ['difficult' => 1]);
        foreach (array_slice($cards, 0, 3) as $card) {
            $items[] = ['item_type' => 'flashcard', 'item_id' => (int) $card['id']];
        }

        $sessionId = $this->revisionSessions->create($studentId, $items);
        $session = $this->revisionSessions->find($sessionId);
        $mcqIds = array_column(array_filter($items, fn($i) => $i['item_type'] === 'mcq'), 'item_id');
        $quizIds = array_column(array_filter($items, fn($i) => $i['item_type'] === 'quiz_question'), 'item_id');
        $quizQs = $quizIds ? $this->quizBank->findByIds($quizIds, false) : [];
        return [
            'session_id' => $sessionId,
            'items'      => $session['items'] ?? [],
            'mcqs'       => array_merge($this->mcqs->findByIds($mcqIds, false), $quizQs),
            'flashcards' => array_values(array_filter($cards, fn($c, $i) => $i < 3, ARRAY_FILTER_USE_BOTH)),
        ];
    }

    private function generatePlanTasks(int $studentId, int $planId, string $examDate, float $hoursPerDay): array
    {
        $weak = $this->weakAreasSummary($studentId, 3);
        $tasks = [];
        $day = new \DateTimeImmutable('today');
        $end = new \DateTimeImmutable($examDate);
        $sort = 0;

        for ($i = 0; $i < 7 && $day <= $end; $i++, $day = $day->modify('+1 day')) {
            $date = $day->format('Y-m-d');
            $weakArea = $weak[$i % max(1, count($weak))] ?? null;
            if ($weakArea && !empty($weakArea['lecture_id'])) {
                $tasks[] = [
                    'task_date' => $date, 'task_type' => 'lecture', 'sort_order' => $sort++,
                    'title' => 'Watch: ' . ($weakArea['lecture_title'] ?? $weakArea['subject']),
                    'lecture_id' => (int) $weakArea['lecture_id'],
                ];
            } elseif ($weakArea) {
                $tasks[] = [
                    'task_date' => $date, 'task_type' => 'mcq', 'sort_order' => $sort++,
                    'title' => 'Practice: ' . ($weakArea['subject'] ?? 'Weak area'),
                    'target_count' => 10,
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
