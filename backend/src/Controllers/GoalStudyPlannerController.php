<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\GoalPlanner\GoalStudyPlannerService;

class GoalStudyPlannerController extends BaseController
{
    public function __construct(private GoalStudyPlannerService $planner) {}

    public function catalog(Request $request): void
    {
        Response::success($this->planner->catalog($request->userId()));
    }

    public function generate(Request $request): void
    {
        try {
            Response::success($this->planner->generate($request->userId(), $request->body()), 'Study plan generated');
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function dashboard(Request $request): void
    {
        Response::success($this->planner->dashboard($request->userId()));
    }

    public function calendar(Request $request): void
    {
        try {
            Response::success($this->planner->calendar($request->userId(), $request->query('month')));
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    public function day(Request $request): void
    {
        try {
            Response::success($this->planner->day($request->userId(), (string) ($request->query('date') ?: date('Y-m-d'))));
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    public function setTask(Request $request): void
    {
        try {
            Response::success($this->planner->setTask(
                $request->userId(),
                (int) $request->param('id'),
                (string) $request->input('status', 'completed')
            ));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function reschedule(Request $request): void
    {
        try {
            Response::success($this->planner->reschedule(
                $request->userId(),
                (int) $request->param('id'),
                (string) $request->input('date')
            ));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function resetToday(Request $request): void
    {
        try {
            Response::success($this->planner->resetToday($request->userId()), 'Today reset');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function missed(Request $request): void
    {
        try {
            Response::success($this->planner->handleMissed($request->userId()), 'Missed tasks redistributed');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function createChallenge(Request $request): void
    {
        try {
            Response::success($this->planner->createChallenge($request->userId(), $request->body()), 'Challenge created');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function reset(Request $request): void
    {
        $this->planner->reset($request->userId());
        Response::success(null, 'Plan reset');
    }

    public function export(Request $request): void
    {
        try {
            Response::success($this->planner->export($request->userId()));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 404);
        }
    }
}
