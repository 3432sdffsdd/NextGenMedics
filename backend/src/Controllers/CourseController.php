<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CourseRepository;
use App\Repositories\ContentRepository;
use App\Repositories\AttendanceRepository;
use App\Services\CourseService;

class CourseController extends BaseController
{
    public function __construct(
        private CourseRepository $courses,
        private ContentRepository $content,
        private AttendanceRepository $attendance,
        private CourseService $courseService
    ) {}

    public function index(Request $request): void
    {
        $result = $this->courses->listPublished($request->page(), $request->perPage(), $request->query('category'));
        Response::paginated($result['items'], $result['total'], $request->page(), $request->perPage());
    }

    public function show(Request $request): void
    {
        $course = $this->courses->findBySlug($request->param('slug'));
        if (!$course) {
            Response::error('Course not found', 404);
            return;
        }

        if ($course['status'] !== 'published' && !$request->user()) {
            Response::error('Course not found', 404);
            return;
        }

        Response::success($course);
    }

    public function adminIndex(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;
        $result = $this->courses->listAll($request->page(), $request->perPage(), $request->query('status'));
        Response::paginated($result['items'], $result['total'], $request->page(), $request->perPage());
    }

    public function store(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $data = $this->validate($request, [
            'title' => 'required|min:3|max:255',
        ]);
        if (!$data) return;

        $course = $this->courseService->create(array_merge($request->body(), $data), $request->userId(), $request->ip());
        Response::success($course, 'Course created', 201);
    }

    public function update(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $id = (int) $request->param('id');
        if (!$this->courseService->canAccess($id, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $course = $this->courseService->update($id, $request->body(), $request->userId(), $request->ip());
        Response::success($course, 'Course updated');
    }

    public function destroy(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $this->courses->softDelete((int) $request->param('id'));
        Response::success(null, 'Course deleted');
    }

    public function archive(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;
        $this->courses->update((int) $request->param('id'), ['status' => 'archived']);
        Response::success(null, 'Course archived');
    }

    public function publish(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;
        $this->courses->update((int) $request->param('id'), ['status' => 'published']);
        Response::success(null, 'Course published');
    }

    public function duplicate(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $id = (int) $request->param('id');
        $course = $this->courses->findById($id);
        if (!$course) {
            Response::error('Course not found', 404);
            return;
        }

        $slug = \App\Helpers\SlugHelper::unique($course['title'] . ' copy', fn($s) => $this->courses->slugExists($s));
        $newId = $this->courses->duplicate($id, $slug, $request->userId());
        Response::success($this->courses->findById($newId), 'Course duplicated', 201);
    }

    public function assignTeacher(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $courseId = (int) $request->param('id');
        $teacherIds = $request->input('teacher_ids', null);

        if ($teacherIds === null) {
            // Backward compatible single-teacher payload.
            $single = $request->input('teacher_id');
            $teacherIds = $single ? [$single] : [];
        }

        $teacherIds = array_values(array_unique(array_filter(array_map('intval', (array) $teacherIds))));
        if (count($teacherIds) > 2) {
            Response::error('A course can have at most 2 teachers', 422);
            return;
        }

        $this->courses->setTeachers($courseId, $teacherIds);
        Response::success(null, 'Teachers assigned');
    }

    public function enrollStudents(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $studentIds = $request->input('student_ids', []);
        $courseId = (int) $request->param('id');
        foreach ((array) $studentIds as $studentId) {
            $this->courses->enrollStudent($courseId, (int) $studentId);
        }
        Response::success(null, 'Students enrolled');
    }

    public function myCourses(Request $request): void
    {
        $user = $request->user();
        $courses = match ($user['role']) {
            'admin'   => $this->courses->listAll(1, 200)['items'] ?? [],
            'teacher' => $this->courses->listByTeacher($user['id']),
            'student' => $this->courses->listByStudent($user['id']),
            default   => [],
        };

        $attendance = $user['role'] === 'student'
            ? $this->attendance->getStudentCourseAttendance((int) $user['id'])
            : [];

        foreach ($courses as &$course) {
            $course['time_progress'] = $this->courseTimeProgress($course);

            if ($user['role'] === 'student') {
                $summary = $attendance[(int) $course['id']] ?? null;
                $total   = $summary['total'] ?? 0;
                $present = $summary['present'] ?? 0;
                $course['attendance_total']      = $total;
                $course['attendance_present']    = $present;
                $course['attendance_percentage'] = $total > 0 ? (int) round($present / $total * 100) : null;
            }
        }
        unset($course);

        Response::success($courses);
    }

    /**
     * Time-based course progress (0-100) using the start date and either an
     * explicit end date or the parsed duration (e.g. "12 weeks").
     */
    private function courseTimeProgress(array $course): int
    {
        $start = $course['start_date'] ?? null;
        if (!$start) {
            return 0;
        }
        $startTs = strtotime($start);
        if (!$startTs) {
            return 0;
        }

        $end = $course['end_date'] ?? null;
        if ($end && strtotime($end)) {
            $endTs = strtotime($end);
        } else {
            $weeks = $this->parseDurationWeeks((string) ($course['duration'] ?? ''));
            if ($weeks <= 0) {
                return 0;
            }
            $endTs = $startTs + $weeks * 7 * 86400;
        }

        if ($endTs <= $startTs) {
            return 0;
        }

        $now = time();
        if ($now <= $startTs) {
            return 0;
        }
        if ($now >= $endTs) {
            return 100;
        }
        return (int) round(($now - $startTs) / ($endTs - $startTs) * 100);
    }

    private function parseDurationWeeks(string $duration): int
    {
        if (preg_match('/(\d+)\s*week/i', $duration, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(\d+)\s*month/i', $duration, $m)) {
            return (int) $m[1] * 4;
        }
        if (preg_match('/(\d+)\s*day/i', $duration, $m)) {
            return (int) ceil(((int) $m[1]) / 7);
        }
        return 0;
    }

    public function structure(Request $request): void
    {
        $courseId = (int) $request->param('id');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($this->content->getCourseStructure($courseId));
    }

    public function showById(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $course = $this->courses->findById((int) $request->param('id'));
        if (!$course) {
            Response::error('Course not found', 404);
            return;
        }
        Response::success($course);
    }

    public function enrollments(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $courseId = (int) $request->param('id');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($this->courses->listEnrollments($courseId));
    }

    public function enrolledStudents(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $courseId = (int) $request->param('id');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($this->courses->getEnrolledStudents($courseId));
    }

    public function unenroll(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $courseId = (int) $request->param('id');
        $studentId = (int) $request->param('studentId');
        $this->courses->unenrollStudent($courseId, $studentId);
        Response::success(null, 'Student removed from course');
    }

    /** PATCH /courses/{id}/enroll/{studentId}/download-videos */
    public function setDownloadVideos(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) {
            return;
        }

        $courseId = (int) $request->param('id');
        $studentId = (int) $request->param('studentId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        if (!$this->courses->isStudentEnrolled($courseId, $studentId)) {
            Response::error('Student is not enrolled in this course', 404);
            return;
        }

        $allowed = filter_var($request->input('can_download_videos', false), FILTER_VALIDATE_BOOLEAN);
        if (!$this->courses->setStudentCanDownloadVideos($courseId, $studentId, $allowed)) {
            Response::error('Could not update download permission. Run migration 011 first.', 500);
            return;
        }

        Response::success([
            'student_id' => $studentId,
            'course_id' => $courseId,
            'can_download_videos' => $allowed,
        ], $allowed ? 'Video download enabled for student' : 'Video download disabled for student');
    }

    public function categories(Request $request): void
    {
        Response::success($this->courses->getCategories());
    }
}
