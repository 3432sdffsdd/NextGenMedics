<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\ClassScheduleRepository;
use App\Services\CourseService;

class ClassScheduleController extends BaseController
{
    public function __construct(
        private ClassScheduleRepository $schedules,
        private CourseService $courseService
    ) {}

    public function mySchedule(Request $request): void
    {
        Response::success($this->schedules->listForUser($request->user()));
    }

    public function byCourse(Request $request): void
    {
        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($this->schedules->listByCourse($courseId));
    }

    public function store(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $slots = $request->input('slots', []);
        if (!is_array($slots) || empty($slots)) {
            Response::error('At least one class slot is required', 422);
            return;
        }

        foreach ($slots as $slot) {
            if (!isset($slot['day_of_week'], $slot['start_time'])) {
                Response::error('Each slot needs day_of_week and start_time', 422);
                return;
            }
        }

        $this->schedules->replaceForCourse($courseId, $slots);
        Response::success($this->schedules->listByCourse($courseId), 'Weekly timetable saved');
    }

    public function destroy(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $slot = $this->schedules->findById((int) $request->param('id'));
        if (!$slot || !$this->courseService->canAccess((int) $slot['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $this->schedules->delete((int) $request->param('id'));
        Response::success(null, 'Slot removed');
    }
}
