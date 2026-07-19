<?php
namespace App\Services;

use App\Repositories\AnalyticsRepository;
use App\Repositories\MistakeRepository;
use App\Repositories\StudentPerformanceRepository;
use App\Repositories\StudentQuestionHistoryRepository;
use App\Repositories\VideoTrackingRepository;

class StudentPerformanceService
{
    public function __construct(
        private StudentPerformanceRepository $repo,
        private MistakeRepository $mistakes,
        private AnalyticsRepository $analytics,
        private VideoTrackingRepository $videos,
        private StudentQuestionHistoryRepository $questionHistory
    ) {}

    public function listStudents(int $teacherId, ?int $courseId = null, string $q = ''): array
    {
        $students = $this->repo->listStudentsForTeacher($teacherId, $courseId, $q);
        $teacherCourseIds = $this->repo->teacherCourseIds($teacherId);

        $out = [];
        foreach ($students as $s) {
            $sid = (int) $s['id'];
            $studentCourseIds = array_map(
                'intval',
                array_column($this->repo->studentCoursesForTeacher($teacherId, $sid), 'id')
            );
            if ($courseId) {
                $studentCourseIds = array_values(array_intersect($studentCourseIds, [$courseId]));
            }
            $scope = $studentCourseIds ?: ($courseId ? [$courseId] : $teacherCourseIds);
            $out[] = [
                'id' => $sid,
                'name' => trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')),
                'email' => $s['email'],
                'status' => $s['status'],
                'courses' => $s['courses'],
                'course_count' => (int) $s['course_count'],
                'last_enrolled_at' => $s['last_enrolled_at'],
                'stats' => $this->repo->summaryCardsForList($sid, $scope),
            ];
        }

        return [
            'students' => $out,
            'total' => count($out),
            'courses' => $this->repo->coursesByIds($teacherCourseIds),
        ];
    }

    public function studentDetail(int $teacherId, int $studentId): array
    {
        if (!$this->repo->teacherCanAccessStudent($teacherId, $studentId)) {
            throw new \RuntimeException('Student not found in your courses');
        }
        $user = $this->repo->findStudent($studentId);
        if (!$user) {
            throw new \RuntimeException('Student not found');
        }

        // Keep question-history table in sync from past quiz attempts when available.
        try {
            $this->questionHistory->syncFromQuizAttempts($studentId);
        } catch (\Throwable) {
            // Table may be missing on older DBs — quiz_attempt_answers is still used below.
        }

        $courses = $this->repo->studentCoursesForTeacher($teacherId, $studentId);
        $courseIds = array_map('intval', array_column($courses, 'id'));

        $quiz = $this->repo->quizStats($studentId, $courseIds);
        $mcq = $this->repo->mcqPracticeStats($studentId);
        $attendance = $this->repo->attendanceSummary($studentId, $courseIds);
        $mistakeStats = $this->mistakes->stats($studentId);
        $mistakeList = $this->mistakes->listForStudent($studentId, ['status' => 'active'], 1, 20);
        try {
            $wrongQuiz = $this->repo->wrongQuizQuestions($studentId, $courseIds, 20);
        } catch (\Throwable) {
            $wrongQuiz = [];
        }
        $assignments = $this->repo->assignmentStats($studentId, $courseIds);
        $videoSummary = $this->videos->studentSummary($studentId, $courseIds);
        $videoList = $this->videos->listStudentVideos($studentId, $courseIds);

        $weak = $this->mergeWeakAreas(
            $this->repo->quizAccuracyByCourse($studentId, $courseIds),
            $this->safeMcqSubjects($studentId)
        );
        $topics = $this->mergeWeakTopics(
            $this->repo->quizAccuracyByQuiz($studentId, $courseIds),
            $this->safeMcqTopics($studentId)
        );
        $timeline = $this->videos->timeline($studentId, 60);

        $mostReplay = null;
        $least = null;
        $last = null;
        foreach ($videoList as $v) {
            if ($mostReplay === null || (int) ($v['replay_count'] ?? 0) > (int) ($mostReplay['replay_count'] ?? 0)) {
                $mostReplay = $v;
            }
            if ($least === null || (float) $v['completion_pct'] < (float) $least['completion_pct']) {
                $least = $v;
            }
            if (!empty($v['last_watched_at']) && ($last === null || $v['last_watched_at'] > $last['last_watched_at'])) {
                $last = $v;
            }
        }

        $quizCorrect = (int) ($quiz['correct'] ?? 0);
        $quizIncorrect = (int) ($quiz['incorrect'] ?? 0);
        $mcqCorrect = (int) ($mcq['correct'] ?? 0);
        $mcqIncorrect = (int) ($mcq['incorrect'] ?? 0);
        $totalWrong = $quizIncorrect + $mcqIncorrect;
        $totalRight = $quizCorrect + $mcqCorrect;

        return [
            'student' => [
                'id' => (int) $user['id'],
                'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'email' => $user['email'],
                'status' => $user['status'],
            ],
            'courses' => $courses,
            'overview' => [
                'quiz_avg_score' => $quiz['avg_score'],
                'quiz_attempts' => $quiz['attempts'],
                'quiz_correct' => $quizCorrect,
                'quiz_incorrect' => $quizIncorrect,
                'quiz_answered' => (int) ($quiz['total_questions_answered'] ?? 0),
                'quiz_accuracy' => $quiz['accuracy'] ?? 0,
                'mcq_accuracy' => $mcq['accuracy'],
                'mcq_attempted' => $mcq['total_questions'],
                'mcq_correct' => $mcqCorrect,
                'mcq_incorrect' => $mcqIncorrect,
                'mcq_attempts' => (int) ($mcq['attempts'] ?? 0),
                'wrong_answers' => $totalWrong,
                'right_answers' => $totalRight,
                // Mistakes = all wrong answers (course quizzes + MCQ practice)
                'active_mistakes' => $totalWrong,
                'mistakes' => $totalWrong,
                'mistake_bank_remaining' => (int) ($mistakeStats['remaining'] ?? 0),
                'attendance_pct' => $attendance['attendance_pct'],
                'videos_completed' => $videoSummary['completed'],
                'videos_total' => $videoSummary['total_videos'],
                'avg_watch_pct' => $videoSummary['average_watch_pct'],
                'study_hours_videos' => $videoSummary['total_watch_hours'],
                'assignments_given' => $assignments['given'],
                'assignments_submitted' => $assignments['submitted'],
                'assignments_pending' => $assignments['pending'],
                'assignments_overdue' => $assignments['overdue'],
                'assignments_graded' => $assignments['graded'],
            ],
            'assignments' => $assignments,
            'quizzes' => [
                'summary' => $quiz,
                'recent_attempts' => $this->repo->recentQuizAttempts($studentId, $courseIds, 12),
                'wrong_questions' => $wrongQuiz,
            ],
            'mcq_practice' => $mcq,
            'mistakes' => [
                'stats' => [
                    'total' => (int) ($mistakeStats['total'] ?? 0),
                    'remaining' => (int) ($mistakeStats['remaining'] ?? 0),
                    'mastered' => (int) ($mistakeStats['mastered'] ?? 0),
                ],
                'items' => $mistakeList['items'] ?? [],
            ],
            'attendance' => $attendance,
            'weak_subjects' => array_slice($weak, 0, 8),
            'weak_topics' => array_slice($topics, 0, 10),
            'video_analytics' => [
                'summary' => $videoSummary,
                'by_course' => $this->videos->subjectProgress($studentId, $courseIds),
                'most_replayed' => $mostReplay,
                'least_watched' => $least,
                'last_viewed' => $last,
                'videos' => $videoList,
                'timeline' => $timeline,
            ],
        ];
    }

    private function safeMcqSubjects(int $studentId): array
    {
        try {
            return $this->analytics->accuracyBySubject($studentId);
        } catch (\Throwable) {
            return [];
        }
    }

    private function safeMcqTopics(int $studentId): array
    {
        try {
            return $this->analytics->accuracyByTopic($studentId);
        } catch (\Throwable) {
            return [];
        }
    }

    /** Merge quiz-course + MCQ-module weak subjects; lowest accuracy first. */
    private function mergeWeakAreas(array $fromQuiz, array $fromMcq): array
    {
        $map = [];
        foreach ($fromQuiz as $row) {
            $name = trim((string) ($row['subject'] ?? ''));
            if ($name === '') {
                continue;
            }
            $map[$name] = [
                'subject' => $name,
                'total' => (int) ($row['total'] ?? 0),
                'correct' => (int) ($row['correct'] ?? 0),
                'accuracy' => (float) ($row['accuracy'] ?? 0),
                'source' => 'quiz',
            ];
        }
        foreach ($fromMcq as $row) {
            $name = trim((string) ($row['subject'] ?? ''));
            if ($name === '') {
                continue;
            }
            if (!isset($map[$name])) {
                $map[$name] = [
                    'subject' => $name,
                    'total' => (int) ($row['total'] ?? 0),
                    'correct' => (int) ($row['correct'] ?? 0),
                    'accuracy' => (float) ($row['accuracy'] ?? 0),
                    'source' => 'mcq',
                ];
                continue;
            }
            $total = $map[$name]['total'] + (int) ($row['total'] ?? 0);
            $correct = $map[$name]['correct'] + (int) ($row['correct'] ?? 0);
            $map[$name]['total'] = $total;
            $map[$name]['correct'] = $correct;
            $map[$name]['accuracy'] = $total > 0 ? round(100 * $correct / $total) : 0;
            $map[$name]['source'] = 'mixed';
        }
        $list = array_values($map);
        usort($list, fn($a, $b) => $a['accuracy'] <=> $b['accuracy']);
        return $list;
    }

    private function mergeWeakTopics(array $fromQuiz, array $fromMcq): array
    {
        $map = [];
        foreach ($fromQuiz as $row) {
            $name = trim((string) ($row['topic'] ?? ''));
            if ($name === '') {
                continue;
            }
            $map[$name] = [
                'topic' => $name,
                'subject' => $row['subject'] ?? null,
                'total' => (int) ($row['total'] ?? 0),
                'correct' => (int) ($row['correct'] ?? 0),
                'accuracy' => (float) ($row['accuracy'] ?? 0),
                'source' => 'quiz',
            ];
        }
        foreach ($fromMcq as $row) {
            $name = trim((string) ($row['topic'] ?? ''));
            if ($name === '') {
                continue;
            }
            if (!isset($map[$name])) {
                $map[$name] = [
                    'topic' => $name,
                    'subject' => null,
                    'total' => (int) ($row['total'] ?? 0),
                    'correct' => (int) ($row['correct'] ?? 0),
                    'accuracy' => (float) ($row['accuracy'] ?? 0),
                    'source' => 'mcq',
                ];
                continue;
            }
            $total = $map[$name]['total'] + (int) ($row['total'] ?? 0);
            $correct = $map[$name]['correct'] + (int) ($row['correct'] ?? 0);
            $map[$name]['total'] = $total;
            $map[$name]['correct'] = $correct;
            $map[$name]['accuracy'] = $total > 0 ? round(100 * $correct / $total) : 0;
            $map[$name]['source'] = 'mixed';
        }
        $list = array_values($map);
        usort($list, fn($a, $b) => $a['accuracy'] <=> $b['accuracy']);
        return $list;
    }
}
