<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\PersonalPlanner\StudyPlannerService;

class PersonalStudyPlannerController extends BaseController
{
    public function __construct(private StudyPlannerService $planner) {}

    public function bootstrap(Request $request): void
    {
        Response::success($this->planner->bootstrap($request->userId()));
    }

    public function setup(Request $request): void
    {
        try {
            Response::success($this->planner->saveSetup($request->userId(), $request->body()), 'Settings saved');
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    public function catalog(Request $request): void
    {
        Response::success($this->planner->catalog($request->userId()));
    }

    public function dashboard(Request $request): void
    {
        Response::success($this->planner->dashboard($request->userId()));
    }

    public function createPlan(Request $request): void
    {
        try {
            Response::success($this->planner->createPlan($request->userId(), $request->body()), 'Plan created');
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function setTask(Request $request): void
    {
        try {
            Response::success($this->planner->setTaskStatus(
                $request->userId(),
                (int) $request->param('id'),
                (string) $request->input('status', 'completed')
            ));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function moveTask(Request $request): void
    {
        try {
            Response::success($this->planner->moveTask(
                $request->userId(),
                (int) $request->param('id'),
                (string) $request->input('action', 'tomorrow')
            ));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
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

    public function history(Request $request): void
    {
        Response::success($this->planner->history($request->userId()));
    }

    public function viewPlan(Request $request): void
    {
        try {
            Response::success($this->planner->viewPlan($request->userId(), (int) $request->param('id')));
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    public function resume(Request $request): void
    {
        try {
            Response::success($this->planner->resume($request->userId(), (int) $request->param('id')), 'Plan resumed');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function archive(Request $request): void
    {
        try {
            Response::success($this->planner->archive($request->userId(), (int) $request->param('id')), 'Plan archived');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function delete(Request $request): void
    {
        try {
            Response::success($this->planner->delete($request->userId(), (int) $request->param('id')), 'Plan deleted');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function duplicate(Request $request): void
    {
        try {
            Response::success($this->planner->duplicate($request->userId(), (int) $request->param('id')), 'Plan duplicated');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function reset(Request $request): void
    {
        Response::success($this->planner->resetActive($request->userId()), 'Active plan reset');
    }

    public function export(Request $request): void
    {
        try {
            $planId = $request->query('plan_id') ? (int) $request->query('plan_id') : null;
            Response::success($this->planner->export($request->userId(), $planId));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 404);
        }
    }
}
