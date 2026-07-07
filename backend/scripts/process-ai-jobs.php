<?php
/**
 * AI generation worker.
 *
 * Processes queued AI generation jobs to completion. Run manually or via cron:
 *   php backend/scripts/process-ai-jobs.php
 *
 * The web UI can also drive generation by polling /ai/jobs/{id}/process, so
 * this worker is optional — useful for batch/background processing on hosting
 * that supports cron.
 */

require __DIR__ . '/../bootstrap.php';

// Load .env so AI_* and DB_* variables are available (mirrors Application::loadEnv).
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if (!getenv($k)) {
            putenv("{$k}={$v}");
            $_ENV[$k] = $v;
        }
    }
}

use App\AI\AiClient;
use App\Repositories\AiContentRepository;
use App\Repositories\AiJobRepository;
use App\Repositories\ContentRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\McqRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use App\Services\AiContentService;
use App\Services\AiGenerationService;
use App\Services\MailService;
use App\Services\NotificationService;
use App\Services\TextExtractionService;

$jobs = new AiJobRepository();

$notifications = new NotificationService(
    new NotificationRepository(),
    new MailService(),
    new UserRepository()
);

$generator = new AiGenerationService(
    new AiContentService(AiClient::fromAppConfig()),
    new TextExtractionService(),
    $jobs,
    new AiContentRepository(),
    new FlashcardRepository(),
    new McqRepository(),
    new ContentRepository(),
    $notifications
);

$maxJobs = (int) ($argv[1] ?? 5);
$processed = 0;

echo "[AI worker] starting\n";

while ($processed < $maxJobs) {
    $job = $jobs->claimNextPending();
    if (!$job) {
        echo "[AI worker] no pending jobs\n";
        break;
    }
    $jobId = (int) $job['id'];
    echo "[AI worker] processing job #{$jobId} (lecture {$job['lecture_id']})\n";

    $guard = 0;
    do {
        $job = $generator->step($jobId);
        $guard++;
        echo "  step={$job['current_step']} status={$job['status']} progress={$job['progress']}%\n";
        if ($guard > 60) {
            echo "  aborting job #{$jobId}: too many steps\n";
            break;
        }
    } while (!in_array($job['status'], ['completed', 'failed'], true));

    if ($job['status'] === 'failed') {
        echo "  job #{$jobId} FAILED: {$job['error']}\n";
    } else {
        echo "  job #{$jobId} completed\n";
    }
    $processed++;
}

echo "[AI worker] done ({$processed} job(s))\n";
