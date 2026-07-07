<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\NotificationRepository;
use App\Services\CourseService;

class NotificationController extends BaseController
{
    public function __construct(
        private NotificationRepository $notifications,
        private CourseService $courseService
    ) {}

    public function index(Request $request): void
    {
        $result = $this->notifications->listForUser(
            $request->userId(),
            $request->page(),
            $request->perPage(),
            $request->query('unread') === '1'
        );
        Response::paginated($result['items'], $result['total'], $request->page(), $request->perPage());
    }

    public function unreadCount(Request $request): void
    {
        Response::success(['count' => $this->notifications->unreadCount($request->userId())]);
    }

    public function markRead(Request $request): void
    {
        $this->notifications->markRead((int) $request->param('id'), $request->userId());
        Response::success(null, 'Marked as read');
    }

    public function markAllRead(Request $request): void
    {
        $this->notifications->markAllRead($request->userId());
        Response::success(null, 'All marked as read');
    }

    /** GET /courses/{courseId}/tab-notifications — badge counts for student course tabs. */
    public function courseTabBadges(Request $request): void
    {
        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($this->notifications->unreadCountsForCourseTabs(
            $request->userId(),
            $courseId,
            $request->userRole() ?? 'student'
        ));
    }

    /** PATCH /courses/{courseId}/tab-notifications/read — clear badge when a tab is opened. */
    public function markCourseTabRead(Request $request): void
    {
        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        $tab = (string) $request->input('tab', '');
        $role = $request->userRole() ?? 'student';
        $allowedTabs = array_keys(NotificationRepository::tabTypesForRole($role));
        if (!in_array($tab, $allowedTabs, true)) {
            Response::error('Invalid tab', 422);
            return;
        }
        $marked = $this->notifications->markReadForCourseTab($request->userId(), $courseId, $tab, $role);
        Response::success([
            'marked' => $marked,
            'badges' => $this->notifications->unreadCountsForCourseTabs($request->userId(), $courseId, $role),
        ]);
    }
}
