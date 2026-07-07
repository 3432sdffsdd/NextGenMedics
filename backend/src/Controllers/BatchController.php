<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\BatchRepository;
use App\Services\CourseService;

class BatchController extends BaseController
{
    public function __construct(
        private BatchRepository $batches,
        private CourseService $courseService
    ) {}

    /** GET /courses/{courseId}/batches */
    public function index(Request $request): void
    {
        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($this->batches->listByCourse($courseId));
    }

    /** POST /batches */
    public function store(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'course_id' => 'required|integer',
            'name'      => 'required|min:2|max:150',
        ]);
        if (!$data) return;

        $courseId = (int) $data['course_id'];
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = $this->batches->create(array_merge($request->body(), ['created_by' => $request->userId()]));
        Response::success($this->batches->findById($id), 'Batch created', 201);
    }

    /** PUT /batches/{id} */
    public function update(Request $request): void
    {
        $batch = $this->authBatch($request);
        if (!$batch) return;
        $this->batches->update((int) $batch['id'], $request->body());
        Response::success($this->batches->findById((int) $batch['id']), 'Batch updated');
    }

    /** DELETE /batches/{id} */
    public function destroy(Request $request): void
    {
        $batch = $this->authBatch($request);
        if (!$batch) return;
        $this->batches->delete((int) $batch['id']);
        Response::success(null, 'Batch deleted');
    }

    /** GET /batches/{id}/students */
    public function students(Request $request): void
    {
        $batch = $this->authBatch($request, ['admin', 'teacher', 'student']);
        if (!$batch) return;
        Response::success($this->batches->getStudents((int) $batch['id']));
    }

    /** POST /batches/{id}/students  { student_ids: [] } */
    public function assign(Request $request): void
    {
        $batch = $this->authBatch($request);
        if (!$batch) return;
        $ids = array_map('intval', (array) $request->input('student_ids', []));
        if (!$ids) {
            Response::error('student_ids is required', 422);
            return;
        }
        $this->batches->assignStudents((int) $batch['id'], $ids);
        Response::success($this->batches->getStudents((int) $batch['id']), 'Students assigned');
    }

    /** DELETE /batches/{id}/students/{studentId} */
    public function unassign(Request $request): void
    {
        $batch = $this->authBatch($request);
        if (!$batch) return;
        $this->batches->removeStudent((int) $batch['id'], (int) $request->param('studentId'));
        Response::success(null, 'Student removed from batch');
    }

    private function authBatch(Request $request, array $roles = ['admin', 'teacher']): ?array
    {
        if (!$this->requireRole($request, $roles)) return null;
        $batch = $this->batches->findById((int) $request->param('id'));
        if (!$batch) {
            Response::error('Batch not found', 404);
            return null;
        }
        if (!$this->courseService->canAccess((int) $batch['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return null;
        }
        return $batch;
    }
}
