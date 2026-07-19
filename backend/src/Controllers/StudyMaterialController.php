<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CourseRepository;
use App\Repositories\StudentVideoWatchRepository;

class StudyMaterialController extends BaseController
{
    public function __construct(
        private CourseRepository $courses,
        private StudentVideoWatchRepository $watches
    ) {}

    public function summary(Request $request): void
    {
        $studentId = $request->userId();
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        Response::success($this->watches->summaryForStudent($studentId, $courseIds));
    }

    public function list(Request $request): void
    {
        try {
            $studentId = $request->userId();
            $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
            $filters = [
                'course_id'    => $request->query('course_id'),
                'topic'        => $request->query('topic'),
                'lecture_id'   => $request->query('lecture_id'),
                'watch_status' => $request->query('watch_status'),
            ];
            Response::success([
                'summary' => $this->watches->summaryForStudent($studentId, $courseIds),
                'topics'  => $this->watches->topicsForStudent($courseIds),
                'videos'  => $this->watches->listVideos($studentId, $courseIds, $filters),
            ]);
        } catch (\Throwable $e) {
            Response::error('Lecture videos error: ' . $e->getMessage(), 500);
        }
    }

    public function toggleWatch(Request $request): void
    {
        $studentId = $request->userId();
        $resourceId = (int) $request->input('resource_id', $request->param('id'));
        if ($resourceId <= 0) {
            Response::error('resource_id is required', 422);
            return;
        }
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        if (!$this->watches->resourceBelongsToStudentCourses($resourceId, $courseIds)) {
            Response::error('Video not found', 404);
            return;
        }
        $raw = $request->input('watched', true);
        $watched = !in_array($raw, [false, 0, '0', 'false', 'off', ''], true);
        $this->watches->setWatched($studentId, $resourceId, $watched);
        Response::success([
            'resource_id' => $resourceId,
            'watched'     => $watched,
            'summary'     => $this->watches->summaryForStudent($studentId, $courseIds),
        ], $watched ? 'Marked as watched' : 'Marked as unwatched');
    }
}
