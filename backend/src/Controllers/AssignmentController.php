<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\FileUploadHelper;
use App\Repositories\AssignmentRepository;
use App\Repositories\CourseRepository;
use App\Services\AssignmentTestService;
use App\Services\CourseService;
use App\Services\HtmlMcqParserService;
use App\Services\NotificationService;
use App\Services\TextExtractionService;

class AssignmentController extends BaseController
{
    public function __construct(
        private AssignmentRepository $assignments,
        private CourseRepository $courses,
        private CourseService $courseService,
        private NotificationService $notifier,
        private HtmlMcqParserService $htmlParser,
        private AssignmentTestService $testService,
        private TextExtractionService $textExtractor
    ) {}

    public function index(Request $request): void
    {
        $courseId = (int) $request->query('course_id');
        if (!$courseId || !$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $items = $this->assignments->listByCourse($courseId);
        foreach ($items as &$row) {
            $row['attachments'] = $this->attachmentsForAssignment($row);
        }
        unset($row);
        if ($request->userRole() === 'student') {
            $studentId = $request->userId();
            foreach ($items as &$row) {
                if ($row['status'] !== 'published') {
                    continue;
                }
                $sub = $this->assignments->getSubmission((int) $row['id'], $studentId);
                $row['my_submission'] = $sub ? $this->enrichSubmission($sub) : null;
            }
            unset($row);
            $items = array_values(array_filter($items, fn($a) => $a['status'] === 'published'));
        }
        Response::success($items);
    }

    public function myAssignments(Request $request): void
    {
        if (!$this->requireRole($request, ['student'])) return;
        Response::success($this->assignments->listByStudent($request->userId()));
    }

    /** Preview MCQs parsed from HTML file or external link. */
    public function parseHtml(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        try {
            $html = $this->resolveHtmlInput($request);
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 422);
            return;
        }

        Response::success($this->htmlParser->parseHtml($html));
    }

    public function store(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        if ($this->multipartPayloadMissing($request)) {
            Response::error('Upload too large or form data was lost. Try smaller files or contact your host to increase post_max_filesize.', 422);
            return;
        }

        $data = $this->validate($request, [
            'course_id' => 'required|integer',
            'title'     => 'required|min:3',
            'due_date'  => 'required|date',
        ]);
        if (!$data) return;

        $data['due_date'] = $this->normalizeDueDate($data['due_date']);

        $courseId = (int) $data['course_id'];
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $body = $request->body();
        $type = ($body['assignment_type'] ?? 'file') === 'interactive_test' ? 'interactive_test' : 'file';
        if (!$this->assignments->hasAssignmentTypeColumn()) {
            $type = 'file';
        }
        $externalUrl = trim((string) ($body['external_url'] ?? ''));

        $attachmentPath = null;
        $id = null;

        try {
            $file = $request->file('attachment');
            if ($file) {
                $upload = FileUploadHelper::uploadOrFail($file, 'all', "courses/{$courseId}/assignments");
                $attachmentPath = $upload['path'];
            }

            if ($type === 'interactive_test' && !$attachmentPath && $externalUrl === '') {
                Response::error('Upload an HTML file or provide a link to the MCQ test', 422);
                return;
            }

            $id = $this->assignments->create(array_merge($data, [
                'teacher_id'      => $request->userId(),
                'description'     => $body['description'] ?? null,
                'instructions'    => $body['instructions'] ?? null,
                'max_marks'       => $body['max_marks'] ?? 100,
                'attachment_path' => $attachmentPath,
                'assignment_type' => $type,
                'external_url'    => $externalUrl !== '' ? $externalUrl : null,
                'status'          => $body['status'] ?? 'published',
            ]));

            if ($type === 'interactive_test') {
                $imported = $this->importInteractiveQuestions($id, $attachmentPath, $externalUrl);
                if ($imported === 0) {
                    throw new \RuntimeException('No valid MCQs found in the HTML. Use Question + A–D options + ANSWER: B format.');
                }
            } else {
                $this->storeAssignmentFiles($id, $courseId, $request, $attachmentPath);
            }
        } catch (\Throwable $e) {
            if ($id) {
                $this->assignments->delete($id);
            }
            $message = $e->getMessage();
            if (str_contains($message, 'assignment_attachments') || str_contains($message, 'assignment_type')) {
                $message = 'Database update required. Run migrations 008 and 009 on the server (see backend/database/live-update-all.sql).';
            }
            Response::error($message ?: 'Could not create assignment', 422);
            return;
        }

        $assignment = $this->assignments->findById($id);
        $assignment['attachments'] = $this->attachmentsForAssignment($assignment);
        $this->notifyNewAssignment($assignment, $courseId, $id);
        Response::success($assignment, 'Assignment created', 201);
    }

    public function show(Request $request): void
    {
        $assignment = $this->assignments->findById((int) $request->param('id'));
        if (!$assignment) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $assignment['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        if (($assignment['assignment_type'] ?? 'file') === 'interactive_test') {
            $assignment['question_count'] = $this->assignments->getQuestionCount((int) $assignment['id']);
        }
        $assignment['attachments'] = $this->attachmentsForAssignment($assignment);
        Response::success($assignment);
    }

    public function update(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $assignment = $this->assignments->findById((int) $request->param('id'));
        if (!$assignment) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $assignment['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $body = $request->body();
        $allowed = ['title', 'description', 'instructions', 'due_date', 'max_marks', 'status', 'external_url'];
        $fields = array_intersect_key($body, array_flip($allowed));
        if (isset($fields['due_date'])) {
            $fields['due_date'] = $this->normalizeDueDate((string) $fields['due_date']);
        }
        $file = $request->file('attachment');

        try {
            if ($file) {
                $upload = FileUploadHelper::uploadOrFail($file, 'all', "courses/{$assignment['course_id']}/assignments");
                if (!empty($assignment['attachment_path'])) {
                    FileUploadHelper::delete($assignment['attachment_path']);
                }
                $fields['attachment_path'] = $upload['path'];
            }

            if (isset($body['assignment_type']) && in_array($body['assignment_type'], ['file', 'interactive_test'], true)) {
                $fields['assignment_type'] = $body['assignment_type'];
            }

            if ($fields) {
                $this->assignments->update((int) $assignment['id'], $fields);
            }

            if (($assignment['assignment_type'] ?? 'file') !== 'interactive_test') {
                $this->storeAssignmentFiles((int) $assignment['id'], (int) $assignment['course_id'], $request, null, true);
            }
        } catch (\Throwable $e) {
            Response::error($e->getMessage() ?: 'Could not update assignment', 422);
            return;
        }

        $updated = $this->assignments->findById((int) $assignment['id']);
        $reimport = ($updated['assignment_type'] ?? 'file') === 'interactive_test'
            && ($file || !empty($fields['external_url']));
        if ($reimport) {
            try {
                $imported = $this->importInteractiveQuestions(
                    (int) $updated['id'],
                    $updated['attachment_path'] ?? null,
                    $updated['external_url'] ?? ''
                );
                if ($imported === 0) {
                    Response::error('No valid MCQs found in the updated HTML', 422);
                    return;
                }
            } catch (\Throwable $e) {
                Response::error($e->getMessage(), 422);
                return;
            }
        }

        $updated = $this->assignments->findById((int) $assignment['id']);
        $updated['attachments'] = $this->attachmentsForAssignment($updated);
        Response::success($updated, 'Assignment updated');
    }

    public function destroy(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $assignment = $this->assignments->findById((int) $request->param('id'));
        if (!$assignment) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $assignment['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        if (!empty($assignment['attachment_path'])) {
            FileUploadHelper::delete($assignment['attachment_path']);
        }
        foreach ($this->assignments->listAttachments((int) $assignment['id']) as $att) {
            if (!empty($att['file_path']) && $att['file_path'] !== ($assignment['attachment_path'] ?? '')) {
                FileUploadHelper::delete($att['file_path']);
            }
        }
        $this->assignments->deleteQuestions((int) $assignment['id']);
        $this->assignments->delete((int) $assignment['id']);
        Response::success(null, 'Assignment deleted');
    }

    public function setStatus(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $assignment = $this->assignments->findById((int) $request->param('id'));
        if (!$assignment) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $assignment['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $status = $request->input('status');
        if (!in_array($status, ['draft', 'published', 'closed'], true)) {
            Response::error('Invalid status', 422);
            return;
        }
        $this->assignments->update((int) $assignment['id'], ['status' => $status]);
        if ($status === 'published' && ($assignment['status'] ?? '') !== 'published') {
            $this->notifyNewAssignment($assignment, (int) $assignment['course_id'], (int) $assignment['id']);
        }
        Response::success(['status' => $status], 'Assignment ' . ($status === 'closed' ? 'closed' : ($status === 'published' ? 'reopened' : 'updated')));
    }

    /** Student: load interactive test questions (no correct answers). */
    public function getTest(Request $request): void
    {
        $assignment = $this->loadInteractiveAssignment($request);
        if (!$assignment) return;

        if ($assignment['status'] === 'closed') {
            Response::error('This test is closed', 403);
            return;
        }

        $existing = $this->assignments->getSubmission((int) $assignment['id'], $request->userId());
        Response::success([
            'assignment' => [
                'id'          => (int) $assignment['id'],
                'title'       => $assignment['title'],
                'description' => $assignment['description'],
                'max_marks'   => (float) $assignment['max_marks'],
                'due_date'    => $assignment['due_date'],
            ],
            'questions'      => $this->assignments->getQuestions((int) $assignment['id'], false),
            'already_submitted' => (bool) $existing,
            'submission'     => $existing ? $this->formatSubmission($existing, (int) $assignment['id']) : null,
        ]);
    }

    /** Student: submit interactive test answers — auto-graded. */
    public function submitTest(Request $request): void
    {
        if (!$this->requireRole($request, ['student'])) return;

        $assignment = $this->loadInteractiveAssignment($request);
        if (!$assignment) return;

        if ($assignment['status'] === 'closed') {
            Response::error('This test is closed', 403);
            return;
        }

        $answers = $request->input('answers', []);
        if (!is_array($answers) || !$answers) {
            Response::error('Please answer at least one question', 422);
            return;
        }

        $result = $this->testService->grade(
            (int) $assignment['id'],
            $answers,
            (float) ($assignment['max_marks'] ?? 100)
        );

        $late = strtotime($assignment['due_date']) < time();
        $this->assignments->submit([
            'assignment_id' => (int) $assignment['id'],
            'student_id'    => $request->userId(),
            'answers_json'  => $answers,
            'marks'         => $result['score'],
            'percentage'    => $result['percentage'],
            'passed'        => $result['passed'],
            'status'        => $late ? 'late' : 'graded',
        ]);

        $teacherIds = $this->courses->getTeacherIdsForNotify((int) $assignment['course_id']);
        if ($teacherIds) {
            $user = $request->user();
            $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'A student';
            $this->notifier->notifyCourseTeachers(
                (int) $assignment['course_id'],
                $teacherIds,
                $request->userId(),
                'assignment_submitted',
                'Interactive test submitted',
                "{$name} completed \"{$assignment['title']}\" — {$result['percentage']}%.",
                ['assignment_id' => (int) $assignment['id']]
            );
        }

        Response::success($result, 'Test submitted');
    }

    public function deleteSubmission(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $submission = $this->assignments->getSubmissionById((int) $request->param('id'));
        if (!$submission) {
            Response::error('Not found', 404);
            return;
        }
        $assignment = $this->assignments->findById((int) $submission['assignment_id']);
        if (!$assignment || !$this->courseService->canAccess((int) $assignment['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        foreach ($this->assignments->listSubmissionFiles((int) $submission['id']) as $f) {
            if (!empty($f['file_path']) && $f['file_path'] !== ($submission['file_path'] ?? '')) {
                FileUploadHelper::delete($f['file_path']);
            }
        }
        if (!empty($submission['file_path'])) {
            FileUploadHelper::delete($submission['file_path']);
        }
        $this->assignments->deleteSubmission((int) $submission['id']);
        Response::success(null, 'Submission deleted');
    }

    public function deleteAttachment(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $att = $this->assignments->findAttachment((int) $request->param('attachmentId'));
        if (!$att) {
            Response::error('Not found', 404);
            return;
        }
        $assignment = $this->assignments->findById((int) $att['assignment_id']);
        if (!$assignment || !$this->courseService->canAccess((int) $assignment['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        FileUploadHelper::delete($att['file_path']);
        $this->assignments->deleteAttachment((int) $att['id']);
        if (($assignment['attachment_path'] ?? '') === $att['file_path']) {
            $remaining = $this->assignments->listAttachments((int) $assignment['id']);
            $this->assignments->update((int) $assignment['id'], [
                'attachment_path' => $remaining[0]['file_path'] ?? null,
            ]);
        }
        Response::success(null, 'Attachment removed');
    }

    public function submit(Request $request): void
    {
        if (!$this->requireRole($request, ['student'])) return;

        $assignmentId = (int) $request->param('id');
        $assignment = $this->assignments->findById($assignmentId);
        if (!$assignment) {
            Response::error('Not found', 404);
            return;
        }
        if (($assignment['assignment_type'] ?? 'file') === 'interactive_test') {
            Response::error('Use the Start test flow for interactive assignments', 422);
            return;
        }

        $uploads = $this->collectUploads($request, 'files', 'file');
        $text = trim((string) $request->input('submission_text', ''));
        if (!$uploads && $text === '') {
            Response::error('Upload at least one file or add notes', 422);
            return;
        }

        $first = $uploads[0] ?? null;
        $this->assignments->submit([
            'assignment_id'     => $assignmentId,
            'student_id'        => $request->userId(),
            'file_path'         => $first['path'] ?? null,
            'original_filename' => $first['original_name'] ?? null,
            'submission_text'   => $text !== '' ? $text : null,
            'status'            => strtotime($assignment['due_date']) < time() ? 'late' : 'submitted',
        ]);

        $submissionId = $this->assignments->getSubmissionId($assignmentId, $request->userId());
        if ($submissionId && $uploads) {
            $this->assignments->deleteSubmissionFiles($submissionId);
            foreach ($uploads as $i => $up) {
                $this->assignments->addSubmissionFile($submissionId, [
                    'title'             => $up['title'],
                    'file_path'         => $up['path'],
                    'original_filename' => $up['original_name'],
                    'mime_type'         => $up['mime_type'],
                    'file_size'         => $up['size'],
                    'sort_order'        => $i,
                ]);
            }
        }

        $this->notifyTeachersFileSubmission($assignment, $assignmentId, $request);
        Response::success(null, 'Assignment submitted');
    }

    public function submissions(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;
        $rows = $this->assignments->listSubmissions((int) $request->param('id'));
        foreach ($rows as &$row) {
            $row = $this->enrichSubmission($row);
        }
        unset($row);
        Response::success($rows);
    }

    public function grade(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'submission_id' => 'required|integer',
            'marks'         => 'required|numeric',
        ]);
        if (!$data) return;

        $this->assignments->gradeSubmission((int) $data['submission_id'], [
            'marks'     => $data['marks'],
            'remarks'   => $data['remarks'] ?? null,
            'status'    => $data['status'] ?? 'graded',
            'graded_by' => $request->userId(),
        ]);

        $submission = $this->assignments->getSubmissionById((int) $data['submission_id']);
        if ($submission) {
            $assignment = $this->assignments->findById((int) $submission['assignment_id']);
            $this->notifier->notify(
                (int) $submission['student_id'],
                'assignment_graded',
                'Assignment graded',
                "Your assignment \"{$assignment['title']}\" was graded: {$data['marks']} marks.",
                ['assignment_id' => (int) $assignment['id'], 'course_id' => (int) $assignment['course_id']],
                false
            );
        }

        Response::success(null, 'Submission graded');
    }

    private function notifyNewAssignment(array $assignment, int $courseId, int $id): void
    {
        if (($assignment['status'] ?? '') !== 'published') {
            return;
        }
        $studentIds = $this->courses->getEnrolledStudents($courseId);
        $label = ($assignment['assignment_type'] ?? 'file') === 'interactive_test' ? 'Interactive test' : 'Assignment';
        $this->notifier->notifyMany(
            array_column($studentIds, 'id'),
            'new_assignment',
            "New {$label} posted",
            "{$label} \"{$assignment['title']}\" has been posted. Due: {$assignment['due_date']}.",
            ['assignment_id' => $id, 'course_id' => $courseId]
        );
    }

    private function notifyTeachersFileSubmission(array $assignment, int $assignmentId, Request $request): void
    {
        $teacherIds = $this->courses->getTeacherIdsForNotify((int) $assignment['course_id']);
        if (!$teacherIds) {
            return;
        }
        $user = $request->user();
        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'A student';
        $this->notifier->notifyCourseTeachers(
            (int) $assignment['course_id'],
            $teacherIds,
            $request->userId(),
            'assignment_submitted',
            'New assignment submission',
            "{$name} submitted \"{$assignment['title']}\".",
            ['assignment_id' => $assignmentId]
        );
    }

    private function importInteractiveQuestions(int $assignmentId, ?string $attachmentPath, string $externalUrl): int
    {
        $html = '';
        if ($attachmentPath) {
            $ext = strtolower(pathinfo($attachmentPath, PATHINFO_EXTENSION));
            $absolute = $this->textExtractor->resolvePath($attachmentPath);
            if (in_array($ext, ['html', 'htm'], true)) {
                $html = is_file($absolute) ? (string) file_get_contents($absolute) : '';
            } else {
                $html = $this->textExtractor->extract($attachmentPath, $ext);
            }
        } elseif ($externalUrl !== '') {
            $html = $this->htmlParser->fetchUrl($externalUrl);
        }
        if (trim($html) === '') {
            throw new \RuntimeException('Could not read MCQ content from HTML file or link');
        }
        $parsed = $this->htmlParser->parseHtml($html);
        return $this->testService->importQuestions($assignmentId, $parsed['valid'] ?? []);
    }

    private function resolveHtmlInput(Request $request): string
    {
        $url = trim((string) $request->input('external_url', ''));
        if ($url !== '') {
            return $this->htmlParser->fetchUrl($url);
        }

        $file = $request->file('attachment') ?? $request->file('file');
        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['html', 'htm'], true)) {
                throw new \InvalidArgumentException('Upload an .html file');
            }
            $content = (string) file_get_contents($file['tmp_name']);
            if (trim($content) === '') {
                throw new \RuntimeException('HTML file is empty');
            }
            return $content;
        }

        $raw = trim((string) $request->input('html', ''));
        if ($raw !== '') {
            return $raw;
        }

        throw new \InvalidArgumentException('Upload an HTML file or provide a link');
    }

    private function loadInteractiveAssignment(Request $request): ?array
    {
        $assignment = $this->assignments->findById((int) $request->param('id'));
        if (!$assignment) {
            Response::error('Not found', 404);
            return null;
        }
        if (!$this->courseService->canAccess((int) $assignment['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return null;
        }
        if (($assignment['assignment_type'] ?? 'file') !== 'interactive_test') {
            Response::error('This assignment is not an interactive test', 422);
            return null;
        }
        return $assignment;
    }

    /** @param array<string,mixed> $submission */
    private function formatSubmission(array $submission, int $assignmentId): array
    {
        $answers = [];
        if (!empty($submission['answers_json'])) {
            $answers = is_string($submission['answers_json'])
                ? (json_decode($submission['answers_json'], true) ?: [])
                : $submission['answers_json'];
        }
        $review = null;
        if ($answers) {
            $review = $this->testService->grade(
                $assignmentId,
                $answers,
                100
            )['review'];
        }
        return [
            'marks'       => $submission['marks'],
            'percentage'  => $submission['percentage'],
            'passed'      => (bool) ($submission['passed'] ?? false),
            'status'      => $submission['status'],
            'submitted_at'=> $submission['submitted_at'],
            'review'      => $review,
            'files'       => $submission['files'] ?? [],
            'submission_text' => $submission['submission_text'] ?? null,
            'file_path'   => $submission['file_path'] ?? null,
            'original_filename' => $submission['original_filename'] ?? null,
        ];
    }

    /** @return array<int, array<string,mixed>> */
    private function attachmentsForAssignment(array $assignment): array
    {
        $list = $this->assignments->listAttachments((int) $assignment['id']);
        $legacyPath = trim((string) ($assignment['attachment_path'] ?? ''));
        if ($legacyPath !== '') {
            $paths = array_column($list, 'file_path');
            if (!in_array($legacyPath, $paths, true)) {
                array_unshift($list, [
                    'id'                => 0,
                    'title'             => basename($legacyPath) ?: 'Attachment',
                    'file_path'         => $legacyPath,
                    'original_filename' => basename($legacyPath),
                ]);
            }
        }
        return $list;
    }

    /** @param array<string,mixed> $submission */
    private function enrichSubmission(array $submission): array
    {
        $files = $this->assignments->listSubmissionFiles((int) $submission['id']);
        $legacyPath = trim((string) ($submission['file_path'] ?? ''));
        if ($legacyPath !== '') {
            $paths = array_column($files, 'file_path');
            if (!in_array($legacyPath, $paths, true)) {
                array_unshift($files, [
                    'id'                => 0,
                    'title'             => $submission['original_filename'] ?? basename($legacyPath) ?: 'Uploaded file',
                    'file_path'         => $legacyPath,
                    'original_filename' => $submission['original_filename'] ?? basename($legacyPath),
                ]);
            }
        }
        $submission['files'] = $files;
        return $submission;
    }

    /** @return array<int, array{path:string,original_name:string,mime_type:?string,size:?int,title:string}> */
    private function collectUploads(Request $request, string $multiKey, string $singleKey): array
    {
        $files = $request->files($multiKey);
        $single = $request->file($singleKey);
        if (!$files && $single) {
            $files = [$single];
        }
        $body = $request->body();
        $titles = $body['file_titles'] ?? $body['file_titles'] ?? [];
        if (!is_array($titles)) {
            $titles = $titles !== null && $titles !== '' ? [$titles] : [];
        }
        $out = [];
        foreach ($files as $i => $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            $upload = FileUploadHelper::upload($file, 'all', "submissions/{$request->userId()}");
            if (!$upload) {
                continue;
            }
            $title = trim((string) ($titles[$i] ?? ''));
            if ($title === '') {
                $title = $upload['original_name'] ?? 'File ' . ($i + 1);
            }
            $out[] = array_merge($upload, ['title' => $title]);
        }
        return $out;
    }

    private function storeAssignmentFiles(int $assignmentId, int $courseId, Request $request, ?string $legacyPath, bool $append = false): void
    {
        $files = $this->assignmentFilesFromRequest($request);
        $supportsMulti = $this->assignments->supportsAttachments();

        if (!$supportsMulti && count($files) > 1) {
            throw new \RuntimeException(
                'Multiple files require database migration 009. Run backend/database/live-update-all.sql, or upload one file at a time.'
            );
        }

        if (!$append && $legacyPath && !$files) {
            if ($supportsMulti) {
                $this->assignments->addAttachment($assignmentId, [
                    'title'             => 'Attachment',
                    'file_path'         => $legacyPath,
                    'original_filename' => basename($legacyPath),
                    'sort_order'        => 0,
                ]);
            }
            return;
        }

        $body = $request->body();
        $titles = $body['file_titles'] ?? [];
        if (!is_array($titles)) {
            $titles = $titles !== null && $titles !== '' ? [$titles] : [];
        }
        $order = $supportsMulti ? count($this->assignments->listAttachments($assignmentId)) : 0;
        foreach ($files as $i => $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new \RuntimeException(FileUploadHelper::uploadErrorMessage((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)));
            }
            $upload = FileUploadHelper::uploadOrFail($file, 'all', "courses/{$courseId}/assignments");
            $title = trim((string) ($titles[$i] ?? ''));
            if ($title === '') {
                $title = $upload['original_name'] ?? 'File ' . ($i + 1);
            }

            if ($supportsMulti) {
                $this->assignments->addAttachment($assignmentId, [
                    'title'             => $title,
                    'file_path'         => $upload['path'],
                    'original_filename' => $upload['original_name'],
                    'mime_type'         => $upload['mime_type'],
                    'file_size'         => $upload['size'],
                    'sort_order'        => $order++,
                ]);
            }

            if ($i === 0 || !$supportsMulti) {
                $this->assignments->update($assignmentId, ['attachment_path' => $upload['path']]);
            }
        }
    }

    /** @return array<int, array<string,mixed>> */
    private function assignmentFilesFromRequest(Request $request): array
    {
        $files = $request->files('files');
        if (!$files) {
            $single = $request->file('files');
            if ($single) {
                $files = [$single];
            }
        }
        return $files;
    }

    private function normalizeDueDate(string $value): string
    {
        $value = trim(str_replace('T', ' ', $value));
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }
        return $value;
    }

    private function multipartPayloadMissing(Request $request): bool
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            return false;
        }
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (!str_contains($contentType, 'multipart/form-data')) {
            return false;
        }
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($contentLength < 1024) {
            return false;
        }
        $body = $request->body();
        return empty($body) && empty($_FILES);
    }
}
