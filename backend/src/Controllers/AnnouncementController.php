<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AnnouncementRepository;
use App\Services\CourseService;

class AnnouncementController extends BaseController
{
    public function __construct(
        private AnnouncementRepository $announcements,
        private CourseService $courseService
    ) {}

    public function index(Request $request): void
    {
        if ($request->user()) {
            $result = $this->announcements->listForUser(
                $request->userId(),
                $request->userRole(),
                $request->page(),
                $request->perPage()
            );
            Response::paginated($result['items'], $result['total'], $request->page(), $request->perPage());
            return;
        }

        Response::success($this->announcements->listPublic());
    }

    public function store(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $rules = [
            'title'   => 'required|min:3',
            'content' => 'required|min:3',
        ];
        if ($request->userRole() === 'teacher') {
            $rules['course_id'] = 'required|integer';
        }

        $data = $this->validate($request, $rules);
        if (!$data) return;

        $courseId = isset($data['course_id']) ? (int) $data['course_id'] : null;
        if ($request->userRole() === 'teacher') {
            if (!$courseId || !$this->courseService->canAccess($courseId, $request->user())) {
                Response::error('Forbidden', 403);
                return;
            }
        }

        try {
            $id = $this->announcements->create([
                'course_id'    => $courseId,
                'author_id'    => $request->userId(),
                'title'        => trim((string) $data['title']),
                'content'      => trim((string) $data['content']),
                'priority'     => $request->input('priority', 'normal'),
                'is_pinned'    => (int) (bool) $request->input('is_pinned', 0),
                'published_at' => $request->input('published_at') ?: date('Y-m-d H:i:s'),
                'expires_at'   => $request->input('expires_at'),
            ]);
        } catch (\Throwable $e) {
            Response::error('Could not create announcement: ' . $e->getMessage(), 422);
            return;
        }

        Response::success(['id' => $id], 'Announcement created', 201);
    }

    public function update(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;
        $this->announcements->update((int) $request->param('id'), $request->body());
        Response::success(null, 'Updated');
    }

    public function destroy(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;
        $this->announcements->delete((int) $request->param('id'));
        Response::success(null, 'Deleted');
    }
}
