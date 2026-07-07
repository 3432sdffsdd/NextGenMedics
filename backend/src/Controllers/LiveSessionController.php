<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\LiveSessionRepository;
use App\Services\CourseService;
use App\Services\NotificationService;

class LiveSessionController extends BaseController
{
    public function __construct(
        private LiveSessionRepository $sessions,
        private CourseService $courseService,
        private NotificationService $notifier
    ) {}

    public function index(Request $request): void
    {
        $courseId = (int) $request->query('course_id');
        if ($courseId) {
            if (!$this->courseService->canAccess($courseId, $request->user())) {
                Response::error('Forbidden', 403);
                return;
            }
            Response::success($this->sessions->listByCourse($courseId));
            return;
        }

        Response::success($this->sessions->listForUser(
            $request->user(),
            $request->query('from'),
            $request->query('to')
        ));
    }

    public function store(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'course_id'    => 'required|integer',
            'title'        => 'required|min:3',
            'scheduled_at' => 'required|date',
        ]);
        if (!$data) return;

        $courseId = (int) $data['course_id'];
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = $this->sessions->create(array_merge($request->body(), $data, [
            'teacher_id' => $request->userId(),
        ]));

        $session = $this->sessions->findById($id);
        $when = date('M j, Y g:i A', strtotime($data['scheduled_at']));
        $studentIds = $this->sessions->getEnrolledStudentIds($courseId);
        $this->notifier->notifyMany(
            $studentIds,
            'class_scheduled',
            'New class scheduled',
            "Class \"{$data['title']}\" for {$session['course_title']} is scheduled on {$when}.",
            ['session_id' => $id, 'course_id' => $courseId]
        );

        Response::success($session, 'Class scheduled', 201);
    }

    public function update(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $id = (int) $request->param('id');
        $session = $this->sessions->findById($id);
        if (!$session || !$this->courseService->canAccess((int) $session['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $body = $request->body();
        unset($body['course_id'], $body['teacher_id']);
        $this->sessions->update($id, $body);
        Response::success($this->sessions->findById($id), 'Session updated');
    }

    public function destroy(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $id = (int) $request->param('id');
        $session = $this->sessions->findById($id);
        if (!$session || !$this->courseService->canAccess((int) $session['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $this->sessions->update($id, ['status' => 'cancelled']);
        Response::success(null, 'Session cancelled');
    }
}
