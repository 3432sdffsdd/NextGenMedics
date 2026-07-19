<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\VideoTracking\VideoTrackingService;

class VideoTrackingController extends BaseController
{
    public function __construct(private VideoTrackingService $tracking) {}

    public function resume(Request $request): void
    {
        try {
            $id = (int) ($request->query('resource_id') ?: $request->param('id'));
            Response::success($this->tracking->getResume($request->userId(), $id));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    public function track(Request $request): void
    {
        try {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
            if (is_string($ip) && str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }
            Response::success($this->tracking->track($request->userId(), $request->body(), is_string($ip) ? $ip : null));
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function dashboard(Request $request): void
    {
        Response::success($this->tracking->studentDashboard($request->userId()));
    }

    public function videos(Request $request): void
    {
        Response::success($this->tracking->studentVideos($request->userId(), [
            'course_id' => $request->query('course_id'),
            'status' => $request->query('status'),
        ]));
    }

    public function teacherOverview(Request $request): void
    {
        try {
            $courseId = (int) ($request->query('course_id') ?: $request->param('courseId'));
            Response::success($this->tracking->teacherOverview($request->userId(), $courseId));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    public function teacherStudent(Request $request): void
    {
        try {
            Response::success($this->tracking->teacherStudent(
                $request->userId(),
                (int) $request->param('courseId'),
                (int) $request->param('studentId')
            ));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    public function exportCsv(Request $request): void
    {
        try {
            $courseId = (int) ($request->query('course_id') ?: 0);
            $csv = $this->tracking->exportClassCsv($request->userId(), $courseId);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="video-class-report.csv"');
            echo $csv;
            exit;
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 404);
        }
    }
}
