<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\DashboardRepository;

class DashboardController extends BaseController
{
    public function __construct(private DashboardRepository $dashboard) {}

    public function admin(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $stats = $this->dashboard->getAdminStats();
        $stats['attendance'] = ['note' => 'Use /api/attendance/reports for detailed stats'];
        $stats['recent_activities'] = $this->dashboard->getRecentActivities();

        Response::success($stats);
    }

    public function teacher(Request $request): void
    {
        if (!$this->requireRole($request, ['teacher'])) return;

        Response::success($this->dashboard->getTeacherStats($request->userId()));
    }

    public function student(Request $request): void
    {
        if (!$this->requireRole($request, ['student'])) return;

        Response::success($this->dashboard->getStudentStats($request->userId()));
    }
}
