<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\AiJobRepository;
use App\Services\AiEngineService;

/** Admin dashboard for AI Generation Engine jobs. */
class AiAdminController extends BaseController
{
    public function __construct(
        private AiEngineService $engine,
        private AiJobRepository $jobs
    ) {}

    public function overview(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) {
            return;
        }
        $status = $request->query('status');
        $page = max(1, (int) $request->query('page', 1));
        $list = $this->jobs->listAdmin($status ?: null, $page, 30);
        Response::success([
            'counts' => $this->jobs->adminCounts(),
            'jobs'   => $list['items'],
            'total'  => $list['total'],
            'page'   => $page,
            'engine' => [
                'ready' => $this->engine->isReady(),
                'model' => $this->engine->model(),
            ],
        ]);
    }

    public function job(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) {
            return;
        }
        $jobId = (int) $request->param('jobId');
        $job = $this->jobs->find($jobId);
        if (!$job) {
            Response::error('Job not found', 404);
            return;
        }
        Response::success(['job' => $this->engine->snapshot($jobId)]);
    }

    public function resume(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) {
            return;
        }
        $jobId = (int) $request->param('jobId');
        try {
            Response::success(['job' => $this->engine->resume($jobId)], 'Job resumed');
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function retry(Request $request): void
    {
        // Alias of resume — resets failed stage and continues.
        $this->resume($request);
    }

    public function cancel(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) {
            return;
        }
        $jobId = (int) $request->param('jobId');
        Response::success(['job' => $this->engine->cancel($jobId)], 'Job cancelled');
    }

    public function process(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) {
            return;
        }
        $jobId = (int) $request->param('jobId');
        $job = $this->jobs->find($jobId);
        if (!$job) {
            Response::error('Job not found', 404);
            return;
        }
        Response::success(['job' => $this->engine->step($jobId)]);
    }
}
