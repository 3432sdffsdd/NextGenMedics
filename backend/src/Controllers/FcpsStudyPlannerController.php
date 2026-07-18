<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\FcpsPlanner\StudyPlannerService;

class FcpsStudyPlannerController extends BaseController
{
    public function __construct(private StudyPlannerService $planner) {}

    public function getPlan(Request $request): void
    {
        Response::success($this->planner->getPlan($request->userId()) ?: ['plan' => null]);
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

    public function regenerate(Request $request): void
    {
        $existing = $this->planner->getPlan($request->userId());
        if (!$existing || empty($existing['plan'])) {
            Response::error('No plan to regenerate', 404);
            return;
        }
        try {
            Response::success(
                $this->planner->generate($request->userId(), array_merge($existing['plan'], $request->body())),
                'Plan regenerated'
            );
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
            Response::success($this->planner->calendarMonth($request->userId(), $request->query('month')));
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    public function day(Request $request): void
    {
        $date = (string) ($request->query('date') ?: date('Y-m-d'));
        try {
            Response::success($this->planner->dayDetail($request->userId(), $date));
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 404);
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

    public function rescheduleTask(Request $request): void
    {
        try {
            Response::success($this->planner->rescheduleTask(
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
            Response::success($this->planner->handleMissed($request->userId()), 'Missed work redistributed');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function search(Request $request): void
    {
        try {
            Response::success($this->planner->search(
                $request->userId(),
                (string) $request->query('q', ''),
                $request->query('status'),
                $request->query('type')
            ));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function reset(Request $request): void
    {
        $this->planner->reset($request->userId());
        Response::success(null, 'Study plan reset');
    }

    public function export(Request $request): void
    {
        try {
            Response::success($this->planner->export($request->userId()));
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    public function exportCsv(Request $request): void
    {
        try {
            $csv = $this->planner->exportCsv($request->userId());
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="fcps-study-schedule.csv"');
            echo $csv;
            exit;
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    public function exportPrint(Request $request): void
    {
        try {
            header('Content-Type: text/html; charset=utf-8');
            echo $this->planner->exportPrintHtml($request->userId());
            exit;
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 404);
        }
    }
}
