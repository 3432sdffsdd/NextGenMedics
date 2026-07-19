<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\QuizRepository;
use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;
use App\Services\CourseService;
use App\Services\NotificationService;
use App\Services\QuizEvaluationService;
use App\Services\QuizWordParserService;
use App\Services\QuizTemplateService;
use App\Services\TextExtractionService;

class QuizController extends BaseController
{
    public function __construct(
        private QuizRepository $quizzes,
        private CourseRepository $courses,
        private UserRepository $users,
        private CourseService $courseService,
        private QuizEvaluationService $evaluator,
        private NotificationService $notifier,
        private QuizWordParserService $wordParser,
        private TextExtractionService $textExtractor
    ) {}

    public function index(Request $request): void
    {
        $courseId = (int) $request->query('course_id');
        if (!$courseId || !$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $items = $this->quizzes->listByCourse($courseId);
        if ($request->userRole() === 'student') {
            $items = array_values(array_filter(
                $items,
                fn($q) => ($q['status'] ?? '') === 'published' && (int) ($q['question_count'] ?? 0) > 0
            ));
        }
        Response::success($items);
    }

    public function store(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $body = $this->normalizeQuizInput($request->body());
        $validator = new Validator();
        if (!$validator->validate($body, [
            'course_id' => 'required|integer',
            'title'     => 'required|min:3',
        ])) {
            Response::error('Validation failed', 422, $validator->errors());
            return;
        }

        $courseId = (int) $body['course_id'];
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = $this->quizzes->create(array_merge($body, [
            'course_id'  => $courseId,
            'teacher_id' => $request->userId(),
        ]));

        $quiz = $this->quizzes->findById($id);
        if (($quiz['status'] ?? '') === 'published') {
            $this->notifyQuizPublished($quiz);
        }

        Response::success($quiz, 'Quiz created', 201);
    }

    /** Coerce quiz form fields to DB-safe types. */
    private function normalizeQuizInput(array $body): array
    {
        $body['title'] = trim((string) ($body['title'] ?? ''));
        $body['description'] = trim((string) ($body['description'] ?? ''));
        $body['course_id'] = (int) ($body['course_id'] ?? 0);
        $body['duration_minutes'] = max(1, (int) ($body['duration_minutes'] ?? 30));
        $body['passing_marks'] = (float) ($body['passing_marks'] ?? 50);
        $body['total_marks'] = (float) ($body['total_marks'] ?? 100);
        $body['max_attempts'] = max(1, (int) ($body['max_attempts'] ?? 1));
        $body['shuffle_questions'] = !empty($body['shuffle_questions']) ? 1 : 0;
        $body['show_leaderboard'] = !empty($body['show_leaderboard']) ? 1 : 0;
        $body['auto_evaluate'] = !isset($body['auto_evaluate']) || !empty($body['auto_evaluate']) ? 1 : 0;
        $body['show_review'] = !isset($body['show_review']) || !empty($body['show_review']) ? 1 : 0;
        return $body;
    }

    private function notifyQuizPublished(array $quiz): void
    {
        $courseId = (int) $quiz['course_id'];
        $studentIds = array_column($this->courses->getEnrolledStudents($courseId), 'id');
        if (!$studentIds) {
            return;
        }
        $this->notifier->notifyMany(
            $studentIds,
            'new_quiz',
            'New quiz available',
            "Quiz \"{$quiz['title']}\" is now available. Check the Quizzes tab in your course.",
            ['quiz_id' => (int) $quiz['id'], 'course_id' => $courseId, 'tab' => 'quizzes'],
            false
        );
    }

    /** Publish a draft quiz automatically once it has at least one question. */
    private function maybeAutoPublishQuiz(int $quizId): bool
    {
        $quiz = $this->quizzes->findById($quizId);
        if (!$quiz || ($quiz['status'] ?? '') === 'published') {
            return ($quiz['status'] ?? '') === 'published';
        }
        if ($this->quizzes->countQuestions($quizId) < 1) {
            return false;
        }
        $this->quizzes->update($quizId, ['status' => 'published']);
        $published = $this->quizzes->findById($quizId);
        if ($published) {
            $this->notifyQuizPublished($published);
        }
        return true;
    }

    public function addQuestion(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'quiz_id'       => 'required|integer',
            'question_type' => 'required',
            'question_text' => 'required|min:3',
        ]);
        if (!$data) return;

        $questionId = $this->quizzes->addQuestion($data);

        $options = $request->input('options', []);
        foreach ($options as $i => $opt) {
            $this->quizzes->addOption([
                'question_id' => $questionId,
                'option_text' => $opt['option_text'],
                'is_correct'  => $opt['is_correct'] ?? 0,
                'match_pair'  => $opt['match_pair'] ?? null,
                'sort_order'  => $i,
            ]);
        }

        $published = $this->maybeAutoPublishQuiz((int) $data['quiz_id']);
        Response::success(['id' => $questionId, 'published' => $published], 'Question added', 201);
    }

    public function update(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $quiz = $this->quizzes->findById((int) $request->param('id'));
        if (!$quiz) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $this->quizzes->update((int) $quiz['id'], $this->normalizeQuizInput($request->body()));
        Response::success($this->quizzes->findById((int) $quiz['id']), 'Quiz updated');
    }

    public function destroy(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $quiz = $this->quizzes->findById((int) $request->param('id'));
        if (!$quiz) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $this->quizzes->delete((int) $quiz['id']);
        Response::success(null, 'Quiz deleted');
    }

    public function setStatus(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $quiz = $this->quizzes->findById((int) $request->param('id'));
        if (!$quiz) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $status = $request->input('status');
        if (!in_array($status, ['draft', 'published', 'closed'], true)) {
            Response::error('Invalid status', 422);
            return;
        }
        $this->quizzes->update((int) $quiz['id'], ['status' => $status]);
        if ($status === 'published' && ($quiz['status'] ?? '') !== 'published') {
            $this->notifyQuizPublished($this->quizzes->findById((int) $quiz['id']));
        }
        Response::success(['status' => $status], 'Quiz ' . ($status === 'published' ? 'published' : 'updated'));
    }

    public function duplicate(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $quiz = $this->quizzes->findById((int) $request->param('id'));
        if (!$quiz) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $newId = $this->quizzes->duplicate((int) $quiz['id'], $request->userId());
        if (!$newId) {
            Response::error('Could not duplicate quiz', 500);
            return;
        }
        Response::success($this->quizzes->findById($newId), 'Quiz duplicated', 201);
    }

    public function deleteQuestion(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $questionId = (int) $request->param('questionId');
        $quizId = $this->quizzes->getQuestionQuizId($questionId);
        if (!$quizId) {
            Response::error('Not found', 404);
            return;
        }
        $quiz = $this->quizzes->findById($quizId);
        if (!$quiz || !$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $this->quizzes->deleteQuestion($questionId);
        Response::success(null, 'Question deleted');
    }

    public function show(Request $request): void
    {
        $quiz = $this->quizzes->findById((int) $request->param('id'));
        if (!$quiz) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $quiz['questions'] = $this->quizzes->getQuestions($quiz['id'], (bool) $quiz['shuffle_questions']);

        if ($request->userRole() === 'student') {
            foreach ($quiz['questions'] as &$q) {
                if ($q['question_type'] !== 'essay') {
                    foreach ($q['options'] as &$o) {
                        unset($o['is_correct']);
                    }
                }
            }
        }

        Response::success($quiz);
    }

    public function startAttempt(Request $request): void
    {
        if (!$this->requireRole($request, ['student'])) return;

        $quizId = (int) $request->param('id');
        $quiz = $this->quizzes->findById($quizId);
        if (!$quiz || !$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        if (($quiz['status'] ?? '') !== 'published') {
            Response::error('This quiz is not available yet', 403);
            return;
        }
        if ($this->quizzes->countQuestions($quizId) < 1) {
            Response::error('This quiz has no questions yet', 422);
            return;
        }

        $attemptId = $this->quizzes->startAttempt($quizId, $request->userId());
        Response::success(['attempt_id' => $attemptId], 'Attempt started', 201);
    }

    public function submitAttempt(Request $request): void
    {
        if (!$this->requireRole($request, ['student'])) return;

        $attemptId = (int) $request->param('attemptId');
        $attempt = $this->quizzes->getAttempt($attemptId);
        if (!$attempt || $attempt['student_id'] != $request->userId()) {
            Response::error('Forbidden', 403);
            return;
        }
        if ($attempt['status'] !== 'in_progress') {
            Response::error('Attempt already submitted', 422);
            return;
        }

        $answers = $request->input('answers', []);
        $timeTaken = $request->input('time_taken_seconds');
        $timeTaken = $timeTaken !== null && $timeTaken !== '' ? max(0, (int) $timeTaken) : null;
        $result = $this->evaluator->evaluateAttempt($attemptId, $answers, $timeTaken);

        $quiz = $this->quizzes->findById((int) $attempt['quiz_id']);
        if ($quiz) {
            $teacherIds = $this->courses->getTeacherIdsForNotify((int) $quiz['course_id']);
            $student = $this->users->findById($request->userId());
            $name = $student
                ? trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))
                : 'A student';
            if ($name === '') {
                $name = 'A student';
            }
            $this->notifier->notifyCourseTeachers(
                (int) $quiz['course_id'],
                $teacherIds,
                $request->userId(),
                'quiz_submitted',
                'Quiz submitted',
                "{$name} completed quiz \"{$quiz['title']}\".",
                ['quiz_id' => (int) $quiz['id'], 'attempt_id' => $attemptId]
            );
        }

        Response::success($result, 'Quiz submitted');
    }

    /** Review a submitted attempt with correct answers (when auto-evaluated). */
    public function attemptReview(Request $request): void
    {
        if (!$this->requireRole($request, ['student'])) return;

        $attemptId = (int) $request->param('attemptId');
        $attempt = $this->quizzes->getAttempt($attemptId);
        if (!$attempt || $attempt['student_id'] != $request->userId()) {
            Response::error('Forbidden', 403);
            return;
        }
        if (!in_array($attempt['status'], ['submitted', 'evaluated'], true)) {
            Response::error('Attempt not submitted yet', 422);
            return;
        }

        $quiz = $this->quizzes->findById((int) $attempt['quiz_id']);
        if (!$quiz || !$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $answers = $this->quizzes->getAttemptAnswers($attemptId);
        $review = [];
        $showReview = (bool) ($quiz['show_review'] ?? 1);
        if ($quiz['auto_evaluate'] && $showReview) {
            foreach ($answers as $row) {
                $options = $this->quizzes->getQuestionOptions((int) $row['question_id'], true);
                $correctIds = array_map('intval', array_column(
                    array_filter($options, fn($o) => $o['is_correct']),
                    'id'
                ));
                $selected = $row['selected_option_ids'];
                if (is_string($selected)) {
                    $selected = json_decode($selected, true);
                }
                if (!is_array($selected)) {
                    $selected = [];
                }
                $selected = array_values(array_map('intval', $selected));

                $review[] = [
                    'question_id'   => (int) $row['question_id'],
                    'question_text' => $row['question_text'],
                    'explanation'   => $row['explanation'],
                    'selected'      => $selected,
                    'text_answer'   => $row['text_answer'],
                    'is_correct'    => $row['is_correct'] !== null ? (bool) $row['is_correct'] : null,
                    'marks_awarded' => $row['marks_awarded'],
                    'correct_ids'   => $correctIds,
                    'options'       => array_map(fn($o) => [
                        'id'          => (int) $o['id'],
                        'option_text' => $o['option_text'],
                        'is_correct'  => (bool) $o['is_correct'],
                    ], $options),
                ];
            }
        }

        Response::success([
            'attempt' => $attempt,
            'quiz'    => ['id' => $quiz['id'], 'title' => $quiz['title'], 'passing_marks' => $quiz['passing_marks']],
            'review'  => $review ?: null,
        ]);
    }

    /** Past attempts for a quiz by the logged-in student. */
    public function myAttempts(Request $request): void
    {
        if (!$this->requireRole($request, ['student'])) return;

        $quizId = (int) $request->param('id');
        $quiz = $this->quizzes->findById($quizId);
        if (!$quiz || !$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($this->quizzes->listStudentAttempts($quizId, $request->userId()));
    }

    public function leaderboard(Request $request): void
    {
        Response::success($this->quizzes->getLeaderboard((int) $request->param('id')));
    }

    /** Download shared quiz template (.txt or .docx — same format). */
    public function downloadTemplate(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) {
            return;
        }

        $format = strtolower((string) $request->query('format', 'txt'));
        $text = QuizTemplateService::TEXT;

        if ($format === 'docx') {
            $bytes = \App\Helpers\SimpleDocxWriter::fromText($text);
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="quiz-mcq-template.docx"');
            header('Content-Length: ' . strlen($bytes));
            echo $bytes;
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="quiz-mcq-template.txt"');
        echo $text;
    }

    /** Parse a Word file for bulk MCQ import (preview only). */
    public function parseWord(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $text = trim((string) $request->input('text', ''));
        if ($text === '') {
            $file = $request->file('file');
            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                Response::error('Please select a quiz file (.docx, .doc, or .txt)', 422);
                return;
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['docx', 'doc', 'txt'], true)) {
                Response::error('Upload a .docx, .doc, or .txt file in the quiz template format', 422);
                return;
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                Response::error('File exceeds 5 MB limit', 422);
                return;
            }

            try {
                $text = $this->textExtractor->extractAbsolute($file['tmp_name'], $ext);
            } catch (\Throwable $e) {
                Response::error('Could not extract text from document: ' . $e->getMessage(), 422);
                return;
            }
        }

        $result = $this->wordParser->parseText($text);
        if ($result['summary']['valid'] === 0) {
            $hint = $result['summary']['invalid'] > 0
                ? 'Some question blocks had format errors — check Answer: X and A–D options on separate lines.'
                : 'Use numbered questions (1. …), options A–D on their own lines, then Answer: X and optional Rationale:.';
            Response::error('Could not parse quiz format. ' . $hint, 422);
            return;
        }

        Response::success($result);
    }

    /** Bulk-import validated MCQ questions into a quiz. */
    public function importQuestions(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $quizId = (int) $request->param('id');
        $quiz = $this->quizzes->findById($quizId);
        if (!$quiz) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $questions = $request->input('questions', []);
        if (!is_array($questions) || !$questions) {
            Response::error('No questions to import', 422);
            return;
        }

        $valid = [];
        $invalid = [];
        foreach ($questions as $i => $q) {
            $errors = $this->wordParser->validateImportQuestion($q);
            if ($errors) {
                $invalid[] = ['index' => $i, 'question_text' => $q['question_text'] ?? '', 'errors' => $errors];
            } else {
                $valid[] = $q;
            }
        }

        if (!$valid) {
            Response::error('No valid questions to import', 422, ['invalid' => $invalid]);
            return;
        }

        $imported = $this->quizzes->bulkImportQuestions($quizId, $valid);
        $published = $this->maybeAutoPublishQuiz($quizId);
        Response::success([
            'imported'  => $imported,
            'skipped'   => count($invalid),
            'invalid'   => $invalid,
            'published' => $published,
        ], "{$imported} question(s) imported" . ($published ? ' and quiz published to students' : ''), 201);
    }

    /** Teacher: list all student attempts for a quiz. */
    public function quizAttempts(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $quizId = (int) $request->param('id');
        $quiz = $this->quizzes->findById($quizId);
        if (!$quiz) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $search = $request->query('search');
        Response::success([
            'quiz'     => ['id' => $quiz['id'], 'title' => $quiz['title'], 'passing_marks' => $quiz['passing_marks']],
            'attempts' => $this->quizzes->listAttemptsForQuiz($quizId, is_string($search) ? $search : null),
        ]);
    }

    /** Teacher: detailed attempt review with question-by-question analysis. */
    public function teacherAttemptReview(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $attemptId = (int) $request->param('attemptId');
        $attempt = $this->quizzes->getAttempt($attemptId);
        if (!$attempt) {
            Response::error('Not found', 404);
            return;
        }

        $quiz = $this->quizzes->findById((int) $attempt['quiz_id']);
        if (!$quiz || !$this->courseService->canAccess((int) $quiz['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $student = $this->users->findById((int) $attempt['student_id']);
        if ($student) {
            unset($student['password']);
            $student['student_name'] = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        }
        $answers = $this->quizzes->getAttemptAnswers($attemptId);
        $review = [];
        foreach ($answers as $row) {
            $options = $this->quizzes->getQuestionOptions((int) $row['question_id'], true);
            $correctIds = array_map('intval', array_column(
                array_filter($options, fn($o) => $o['is_correct']),
                'id'
            ));
            $review[] = [
                'question_id'   => (int) $row['question_id'],
                'question_text' => $row['question_text'],
                'explanation'   => $row['explanation'],
                'selected'      => $row['selected_option_ids'],
                'text_answer'   => $row['text_answer'],
                'is_correct'    => $row['is_correct'] !== null ? (bool) $row['is_correct'] : null,
                'marks_awarded' => $row['marks_awarded'],
                'correct_ids'   => $correctIds,
                'options'       => array_map(fn($o) => [
                    'id'          => (int) $o['id'],
                    'option_text' => $o['option_text'],
                    'is_correct'  => (bool) $o['is_correct'],
                ], $options),
            ];
        }

        Response::success([
            'attempt' => $attempt,
            'quiz'    => ['id' => $quiz['id'], 'title' => $quiz['title'], 'passing_marks' => $quiz['passing_marks']],
            'student' => $student,
            'review'  => $review,
        ]);
    }
}
