<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AttendanceRepository;
use App\Services\CourseService;

class AttendanceController extends BaseController
{
    public function __construct(
        private AttendanceRepository $attendance,
        private CourseService $courseService
    ) {}

    public function createSession(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'course_id'    => 'required|integer',
            'session_date' => 'required|date',
        ]);
        if (!$data) return;

        if (!$this->courseService->canAccess((int) $data['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        // Dedup: reuse an existing session for the same course + date.
        $existing = $this->attendance->findSessionByCourseDate((int) $data['course_id'], $data['session_date']);
        if ($existing) {
            Response::success(['id' => (int) $existing['id'], 'existing' => true], 'Loaded existing session');
            return;
        }

        $id = $this->attendance->createSession(array_merge($data, [
            'teacher_id' => $request->userId(),
            'title'      => $request->input('title'),
            'notes'      => $request->input('notes'),
        ]));

        Response::success(['id' => $id, 'existing' => false], 'Session created', 201);
    }

    /** Load an existing session (and its marks) for a course on a given date, if any. */
    public function sessionByDate(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $date = $request->query('date');
        if (!$date) {
            Response::error('date required', 422);
            return;
        }
        $session = $this->attendance->findSessionByCourseDate($courseId, $date);
        Response::success([
            'session' => $session,
            'records' => $session ? $this->attendance->getSessionRecords((int) $session['id']) : [],
        ]);
    }

    public function updateSession(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $session = $this->attendance->findSession((int) $request->param('sessionId'));
        if (!$session || !$this->courseService->canAccess((int) $session['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $this->attendance->updateSession((int) $session['id'], [
            'title' => $request->input('title'),
            'notes' => $request->input('notes'),
        ]);
        Response::success(null, 'Session updated');
    }

    public function deleteSession(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $session = $this->attendance->findSession((int) $request->param('sessionId'));
        if (!$session || !$this->courseService->canAccess((int) $session['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $this->attendance->deleteSession((int) $session['id']);
        Response::success(null, 'Attendance session deleted');
    }

    public function mark(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $records = $request->input('records', []);
        $marked = [];
        foreach ($records as $r) {
            $marked[] = [
                'session_id' => (int) $r['session_id'],
                'student_id' => (int) $r['student_id'],
                'status'     => $r['status'],
                'remarks'    => $r['remarks'] ?? null,
                'marked_by'  => $request->userId(),
            ];
        }
        $this->attendance->markAttendance($marked);
        Response::success(null, 'Attendance marked');
    }

    public function byCourse(Request $request): void
    {
        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        Response::success($this->attendance->getByCourse(
            $courseId,
            $request->query('from'),
            $request->query('to')
        ));
    }

    public function myAttendance(Request $request): void
    {
        Response::success($this->attendance->getStudentAttendance(
            $request->userId(),
            $request->query('course_id') ? (int) $request->query('course_id') : null
        ));
    }

    public function reports(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $courseId = (int) $request->query('course_id');
        if (!$courseId) {
            Response::error('course_id required', 422);
            return;
        }

        Response::success([
            'statistics' => $this->attendance->getStatistics($courseId),
            'sessions'   => $this->attendance->getByCourse($courseId, $request->query('from'), $request->query('to')),
        ]);
    }

    public function sessionRecords(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $sessionId = (int) $request->param('sessionId');
        Response::success($this->attendance->getSessionRecords($sessionId));
    }
}
