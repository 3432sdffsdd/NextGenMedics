<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\ClassReminderService;
use App\Services\StudyService;

class CronController extends BaseController
{
    public function __construct(
        private ClassReminderService $reminders,
        private StudyService $study
    ) {}

    public function classReminders(Request $request): void
    {
        if (!$this->authorize($request)) return;

        $result = $this->reminders->processDueReminders();
        Response::success($result, 'Class reminders processed');
    }

    public function streakReminders(Request $request): void
    {
        if (!$this->authorize($request)) return;

        $count = $this->study->remindExpiringStreaks();
        Response::success(['reminded' => $count], 'Streak reminders processed');
    }

    private function authorize(Request $request): bool
    {
        $config = require __DIR__ . '/../../config/config.php';
        $secret = $config['cron_secret'] ?? '';
        $key = $request->query('key') ?? '';

        if ($secret && $key !== $secret) {
            Response::error('Unauthorized', 401);
            return false;
        }
        return true;
    }
}
