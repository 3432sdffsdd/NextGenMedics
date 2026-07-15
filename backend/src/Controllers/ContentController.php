<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\FileUploadHelper;
use App\Repositories\ContentRepository;
use App\Repositories\CourseRepository;
use App\Services\CourseService;
use App\Services\NotificationService;

class ContentController extends BaseController
{
    public function __construct(
        private ContentRepository $content,
        private CourseRepository $courses,
        private CourseService $courseService,
        private NotificationService $notifier
    ) {}

    public function createModule(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'course_id' => 'required|integer',
            'title'     => 'required|min:2',
        ]);
        if (!$data) return;

        $courseId = (int) $data['course_id'];
        if ($courseId <= 0) {
            Response::error('Invalid course selected', 422);
            return;
        }

        if (!$this->courses->findById($courseId)) {
            Response::error('Course not found', 404);
            return;
        }

        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('You are not assigned to this course. Ask the administrator to assign you as teacher.', 403);
            return;
        }

        try {
            $id = $this->content->createModule([
                'course_id' => $courseId,
                'title'     => trim($data['title']),
                'description' => $data['description'] ?? null,
            ]);
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'modules')) {
                Response::error('Course content tables are missing. Run the database update script on the server.', 500);
                return;
            }
            throw $e;
        }

        Response::success(['id' => $id], 'Module created', 201);
    }

    public function createChapter(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'module_id' => 'required|integer',
            'title'     => 'required|min:2',
        ]);
        if (!$data) return;

        $courseId = $this->content->getCourseIdFromModule((int) $data['module_id']);
        if (!$courseId || !$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('You cannot add a chapter to this module', 403);
            return;
        }

        $id = $this->content->createChapter($data);
        Response::success(['id' => $id], 'Chapter created', 201);
    }

    public function createLecture(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'chapter_id' => 'required|integer',
            'title'      => 'required|min:2',
        ]);
        if (!$data) return;

        $courseId = $this->content->getCourseIdFromChapter((int) $data['chapter_id']);
        if (!$courseId || !$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('You cannot add a lecture to this chapter', 403);
            return;
        }

        $id = $this->content->createLecture($data);
        Response::success(['id' => $id], 'Lecture created', 201);
    }

    public function uploadResource(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $lectureId = (int) $request->input('lecture_id');
        $type = $request->input('type', 'pdf');
        $courseId = $this->content->getLectureCourseId($lectureId);

        if (!$courseId || !$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $externalUrl = trim((string) $request->input('external_url', ''));
        $file = $request->file('file');

        if (!$file && $externalUrl === '') {
            Response::error('Please upload a file or provide an external link', 422);
            return;
        }

        if ($externalUrl !== '' && !filter_var($externalUrl, FILTER_VALIDATE_URL)) {
            Response::error('Please enter a valid URL (e.g. https://...)', 422);
            return;
        }

        $upload = null;
        if ($file) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            // Auto-detect resource type from extension when uploading files.
            if (in_array($ext, ['ppt', 'pptx'], true)) {
                $type = 'slides';
            } elseif ($ext === 'pdf') {
                $type = 'pdf';
            } elseif (in_array($ext, ['mp4', 'webm', 'mov'], true)) {
                $type = 'video';
            } elseif (in_array($ext, ['html', 'htm'], true)) {
                $type = 'reference';
            }

            $category = match ($type) {
                'video'  => 'video',
                'pdf'    => 'pdf',
                'slides' => 'ppt',
                default  => 'all',
            };
            $upload = FileUploadHelper::upload($file, $category, "courses/{$courseId}/lectures");
            if (!$upload) return;
        } elseif ($externalUrl !== '') {
            $type = in_array($type, ['video', 'link', 'reference'], true) ? $type : 'video';
        }

        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            $title = $upload['original_name'] ?? ($type === 'video' ? 'Video link' : 'External link');
        }

        $resourceId = $this->content->addLectureResource([
            'lecture_id'    => $lectureId,
            'type'          => $type,
            'title'         => $title,
            'file_path'     => $upload['path'] ?? null,
            'external_url'  => $externalUrl !== '' ? $externalUrl : null,
            'mime_type'     => $upload['mime_type'] ?? null,
            'size'          => $upload['size'] ?? null,
            'uploaded_by'   => $request->userId(),
        ]);

        $this->notifyStudentsNewContent((int) $courseId, $lectureId, (int) $resourceId, $title);

        Response::success(['id' => $resourceId], 'Material uploaded successfully', 201);
    }

    private function notifyStudentsNewContent(int $courseId, int $lectureId, int $resourceId, string $title): void
    {
        $studentIds = array_column($this->courses->getEnrolledStudents($courseId), 'id');
        if (!$studentIds) {
            return;
        }
        $lecture = $this->content->getLecture($lectureId);
        $lectureTitle = $lecture['title'] ?? 'a lecture';
        $this->notifier->notifyMany(
            $studentIds,
            'new_content',
            'New study material uploaded',
            "\"{$title}\" was added to {$lectureTitle}. Open Learn to view it.",
            [
                'course_id'   => $courseId,
                'lecture_id'  => $lectureId,
                'resource_id' => $resourceId,
                'tab'         => 'learn',
            ],
            false
        );
    }

    public function update(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $table = $request->param('entity');
        $allowed = ['modules', 'chapters', 'lectures', 'lecture_resources'];
        if (!in_array($table, $allowed, true)) {
            Response::error('Invalid entity', 422);
            return;
        }

        $this->content->updateEntity($table, (int) $request->param('id'), $request->body());
        Response::success(null, 'Updated');
    }

    public function destroy(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $table = $request->param('entity');
        $allowed = ['modules', 'chapters', 'lectures', 'lecture_resources'];
        if (!in_array($table, $allowed, true)) {
            Response::error('Invalid entity', 422);
            return;
        }

        $id = (int) $request->param('id');

        // Clean up the physical file when deleting a material.
        if ($table === 'lecture_resources') {
            $resource = $this->content->getResource($id);
            if ($resource && !empty($resource['file_path'])) {
                FileUploadHelper::delete($resource['file_path']);
            }
        }

        $this->content->deleteEntity($table, $id);
        Response::success(null, 'Deleted');
    }
}
