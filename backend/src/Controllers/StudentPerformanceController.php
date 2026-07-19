<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\StudentPerformanceService;

class StudentPerformanceController extends BaseController
{
    public function __construct(private StudentPerformanceService $service) {}

    public function list(Request $request): void
    {
        try {
            $courseId = $request->query('course_id');
            $q = (string) ($request->query('q') ?? '');
            Response::success($this->service->listStudents(
                $request->userId(),
                $courseId !== null && $courseId !== '' ? (int) $courseId : null,
                $q
            ));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function show(Request $request): void
    {
        try {
            Response::success($this->service->studentDetail(
                $request->userId(),
                (int) $request->param('studentId')
            ));
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
