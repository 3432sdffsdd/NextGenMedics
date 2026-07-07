<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AiContentRepository;
use App\Repositories\AttemptRepository;
use App\Repositories\BookmarkRepository;
use App\Repositories\ChallengeRepository;
use App\Repositories\ContentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\DailyChallengeSetRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\McqRepository;
use App\Repositories\MistakeRepository;
use App\Services\StudyService;

/**
 * Student-facing AI learning features: Revision Center, Flashcard Center,
 * MCQ practice, and the Daily MCQ Challenge. Only published, approved content
 * from courses the student is enrolled in is ever returned.
 */
class StudentAiController extends BaseController
{
    public function __construct(
        private AiContentRepository $content,
        private FlashcardRepository $flashcards,
        private McqRepository $mcqs,
        private ChallengeRepository $challenges,
        private AttemptRepository $attempts,
        private BookmarkRepository $bookmarks,
        private ContentRepository $lectures,
        private CourseRepository $courses,
        private StudyService $study,
        private MistakeRepository $mistakes,
        private DailyChallengeSetRepository $dailySets
    ) {}

    // ── Revision Center ────────────────────────────────────────

    public function revisionLectures(Request $request): void
    {
        $studentId = $request->userId();
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        Response::success($this->content->publishedLecturesForCourses($courseIds));
    }

    public function revisionContent(Request $request): void
    {
        $studentId = $request->userId();
        $lectureId = (int) $request->param('lectureId');

        if (!$this->enrolledInLecture($studentId, $lectureId) || !$this->content->isPublished($lectureId)) {
            Response::error('Not available', 404);
            return;
        }

        $this->study->recordActivity($studentId, 'revision');

        Response::success([
            'content'    => $this->content->findByLecture($lectureId),
            'highlights' => $this->bookmarks->listHighlights($studentId, $lectureId),
            'bookmarked' => in_array($lectureId, $this->bookmarks->bookmarkedIds($studentId, 'lecture'), true),
        ]);
    }

    // ── Flashcard Center ───────────────────────────────────────

    public function flashcards(Request $request): void
    {
        $studentId = $request->userId();
        $filters = [
            'course_id'  => $request->query('course_id'),
            'lecture_id' => $request->query('lecture_id'),
            'favorite'   => $request->query('favorite'),
            'difficult'  => $request->query('difficult'),
            'search'     => $request->query('search'),
        ];
        Response::success([
            'cards'   => $this->flashcards->listForStudent($studentId, $filters),
            'filters' => $this->flashcards->studentFilters($studentId),
        ]);
    }

    public function flashcardProgress(Request $request): void
    {
        $studentId = $request->userId();
        $id = (int) $request->param('id');

        if (!$this->flashcards->isVisibleToStudent($id, $studentId)) {
            Response::error('Not found', 404);
            return;
        }

        $this->flashcards->setProgress($studentId, $id, [
            'status'       => $request->input('status'),
            'is_favorite'  => $request->input('is_favorite'),
            'is_difficult' => $request->input('is_difficult'),
            'reviewed'     => $request->input('reviewed'),
        ]);

        $result = null;
        if ($request->input('reviewed')) {
            $result = $this->study->recordActivity($studentId, 'flashcard');
        }
        Response::success($result, 'Progress saved');
    }

    // ── MCQ practice ───────────────────────────────────────────

    public function mcqPractice(Request $request): void
    {
        $studentId = $request->userId();
        $lectureId = (int) $request->param('lectureId');

        if (!$this->enrolledInLecture($studentId, $lectureId) || !$this->content->isPublished($lectureId)) {
            Response::error('Not available', 404);
            return;
        }
        // Deliver questions without answers.
        Response::success($this->mcqs->listByLecture($lectureId, 'published', false));
    }

    // ── Daily MCQ Challenge ────────────────────────────────────

    public function challengeToday(Request $request): void
    {
        $studentId = $request->userId();
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        $challenges = $this->challenges->enabledForCourses($courseIds);

        $today = new \DateTimeImmutable('today');
        $out = [];

        foreach ($challenges as $ch) {
            $start = new \DateTimeImmutable($ch['start_date'] ?: 'today');
            $day = (int) $start->diff($today)->days + 1;
            if ($day < 1) {
                continue;
            }
            $perDay = max(1, (int) $ch['mcqs_per_day']);
            $allIds = $this->mcqs->publishedIdsByLecture((int) $ch['lecture_id']);
            $slice = array_slice($allIds, ($day - 1) * $perDay, $perDay);
            if (!$slice) {
                continue; // all released / none due today
            }

            $attempted = $this->attempts->challengeAttemptExists($studentId, (int) $ch['id'], $day);
            $out[] = [
                'challenge_id'  => (int) $ch['id'],
                'lecture_id'    => (int) $ch['lecture_id'],
                'lecture_title' => $ch['lecture_title'],
                'course_title'  => $ch['course_title'],
                'day'           => $day,
                'total_today'   => count($slice),
                'attempted'     => $attempted,
                'questions'     => $attempted ? [] : $this->mcqs->findByIds($slice, false),
            ];
        }
        Response::success($out);
    }

    // ── Submit an MCQ attempt (practice or challenge) ──────────

    public function submitAttempt(Request $request): void
    {
        $studentId = $request->userId();
        $answers = $request->input('answers', []);
        if (!is_array($answers) || !$answers) {
            Response::error('No answers submitted', 422);
            return;
        }

        $mcqIds = array_values(array_filter(array_map(fn($a) => (int) ($a['mcq_id'] ?? 0), $answers)));
        $mcqs = $this->mcqs->findByIds($mcqIds, true);
        if (!$mcqs) {
            Response::error('Questions not found', 404);
            return;
        }
        $byId = [];
        foreach ($mcqs as $m) {
            $byId[(int) $m['id']] = $m;
        }

        // Authorisation: student must be enrolled in the course of every question.
        foreach ($mcqs as $m) {
            if (!$this->enrolledInLecture($studentId, (int) $m['lecture_id'])) {
                Response::error('Forbidden', 403);
                return;
            }
        }

        $lectureId = (int) ($mcqs[0]['lecture_id']);
        $source = in_array($request->input('source'), ['challenge', 'practice', 'lecture', 'daily', 'mistakes', 'bank', 'revision'], true)
            ? $request->input('source') : 'practice';

        if ($source === 'daily' && $this->dailySets->dailyAttemptExists($studentId, date('Y-m-d'))) {
            Response::error('You have already completed today\'s challenge', 409);
            return;
        }

        $attemptId = $this->attempts->create($studentId, [
            'source'          => $source,
            'lecture_id'      => $lectureId,
            'challenge_id'    => $request->input('challenge_id') ?: null,
            'challenge_day'   => $request->input('challenge_day') ?: null,
            'total_questions' => count($answers),
        ]);

        $correct = 0;
        $review = [];
        foreach ($answers as $a) {
            $mcqId = (int) ($a['mcq_id'] ?? 0);
            $mcq = $byId[$mcqId] ?? null;
            if (!$mcq) {
                continue;
            }
            $selected = isset($a['selected_option']) ? strtoupper((string) $a['selected_option']) : null;
            if (!in_array($selected, ['A', 'B', 'C', 'D', 'E'], true)) {
                $selected = null;
            }
            $isCorrect = $selected !== null && $selected === $mcq['correct_option'];
            if ($isCorrect) {
                $correct++;
            }
            $this->attempts->addAnswer($attemptId, $mcqId, $selected, $isCorrect, (int) ($a['time_spent_seconds'] ?? 0));
            $this->mistakes->recordAnswer($studentId, $mcqId, $isCorrect);

            $review[] = [
                'mcq_id'              => $mcqId,
                'question'            => $mcq['question'],
                'options'             => ['A' => $mcq['option_a'], 'B' => $mcq['option_b'], 'C' => $mcq['option_c'], 'D' => $mcq['option_d'], 'E' => $mcq['option_e']],
                'correct_option'      => $mcq['correct_option'],
                'selected_option'     => $selected,
                'is_correct'          => $isCorrect,
                'explanation'         => $mcq['explanation'],
                'option_explanations' => $mcq['option_explanations'],
                'topic'               => $mcq['topic'],
                'difficulty'          => $mcq['difficulty'],
            ];
        }

        $total = count($answers);
        $wrong = $total - $correct;
        $score = $total > 0 ? round($correct / $total * 100, 2) : 0;
        $timeSpent = (int) $request->input('time_spent_seconds', 0);
        $this->attempts->finalize($attemptId, $correct, $wrong, $score, $timeSpent);

        if ($source === 'daily') {
            $setId = (int) $request->input('daily_set_id');
            if ($setId) {
                $this->dailySets->markCompleted($setId, $attemptId);
            }
        }

        $study = $this->study->recordActivity($studentId, 'mcq');

        Response::success([
            'attempt_id'   => $attemptId,
            'total'        => $total,
            'correct'      => $correct,
            'wrong'        => $wrong,
            'score'        => $score,
            'time_spent'   => $timeSpent,
            'review'       => $review,
            'new_badges'   => $study['new_badges'] ?? [],
            'streak'       => $study['streak'] ?? null,
        ], 'Attempt submitted');
    }

    public function recentAttempts(Request $request): void
    {
        Response::success($this->attempts->recentByStudent($request->userId(), 10));
    }

    // ── Bookmarks & highlights ─────────────────────────────────

    public function toggleBookmark(Request $request): void
    {
        $data = $this->validate($request, ['content_type' => 'required', 'content_id' => 'required|integer']);
        if (!$data) return;
        $added = $this->bookmarks->toggle(
            $request->userId(),
            (string) $data['content_type'],
            (int) $data['content_id'],
            $request->input('note')
        );
        Response::success(['bookmarked' => $added], $added ? 'Bookmarked' : 'Removed');
    }

    public function listBookmarks(Request $request): void
    {
        $type = $request->query('type', 'note');
        Response::success($this->bookmarks->listByType($request->userId(), $type));
    }

    public function addHighlight(Request $request): void
    {
        $studentId = $request->userId();
        $lectureId = (int) $request->param('lectureId');
        if (!$this->enrolledInLecture($studentId, $lectureId)) {
            Response::error('Forbidden', 403);
            return;
        }
        $data = $this->validate($request, ['text' => 'required|min:1']);
        if (!$data) return;

        $id = $this->bookmarks->addHighlight(
            $studentId, $lectureId,
            $request->input('section'),
            $data['text'],
            $request->input('color', 'yellow')
        );
        Response::success(['id' => $id], 'Highlight saved', 201);
    }

    public function deleteHighlight(Request $request): void
    {
        $this->bookmarks->deleteHighlight($request->userId(), (int) $request->param('id'));
        Response::success(null, 'Highlight removed');
    }

    // ── helpers ────────────────────────────────────────────────

    private function enrolledInLecture(int $studentId, int $lectureId): bool
    {
        $courseId = $this->lectures->getLectureCourseId($lectureId);
        return $courseId !== null && $this->courses->isStudentEnrolled($courseId, $studentId);
    }
}
