<?php
/**
 * AI generation worker (legacy Study Tools + Gemini Engine).
 *
 * Processes queued AI generation jobs to completion. Run manually or via cron:
 *   php backend/scripts/process-ai-jobs.php
 *
 * The web UI can also drive generation by polling process endpoints.
 */

require __DIR__ . '/../bootstrap.php';

$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        putenv("{$k}={$v}");
        $_ENV[$k] = $v;
    }
}

use App\AI\AiClient;
use App\AI\Prompts\PromptBuilder;
use App\AI\Prompts\PromptRepository;
use App\Repositories\AiContentRepository;
use App\Repositories\AiGenerationLogRepository;
use App\Repositories\AiJobRepository;
use App\Repositories\AiJobStageRepository;
use App\Repositories\ClinicalCaseRepository;
use App\Repositories\ContentRepository;
use App\Repositories\DiseaseComparisonRepository;
use App\Repositories\DrugTableRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\McqRepository;
use App\Repositories\MnemonicRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\RevisionSheetRepository;
use App\Repositories\UserRepository;
use App\Repositories\VideoSimulationRepository;
use App\Repositories\VivaQuestionRepository;
use App\Services\AiContentService;
use App\Services\AiEngineService;
use App\Services\AiGenerationService;
use App\Services\MailService;
use App\Services\NotificationService;
use App\Services\TextExtractionService;

$config = require __DIR__ . '/../config/config.php';
$jobs = new AiJobRepository();

$notifications = new NotificationService(
    new NotificationRepository(),
    new MailService(),
    new UserRepository()
);

$legacy = new AiGenerationService(
    new AiContentService(AiClient::fromAppConfig(), (int) ($config['ai']['max_input_chars'] ?? 8000)),
    new TextExtractionService(),
    $jobs,
    new AiContentRepository(),
    new FlashcardRepository(),
    new McqRepository(),
    new ContentRepository(),
    $notifications
);

$engine = new AiEngineService(
    AiClient::gemini(),
    new PromptBuilder(new PromptRepository()),
    new TextExtractionService(),
    $jobs,
    new AiJobStageRepository(),
    new AiGenerationLogRepository(),
    new AiContentRepository(),
    new FlashcardRepository(),
    new McqRepository(),
    new DrugTableRepository(),
    new DiseaseComparisonRepository(),
    new MnemonicRepository(),
    new VivaQuestionRepository(),
    new ClinicalCaseRepository(),
    new RevisionSheetRepository(),
    new VideoSimulationRepository(),
    new ContentRepository(),
    $notifications,
    (int) ($config['gemini']['max_input_chars'] ?? 100000)
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
    $isEngine = !empty($job['engine']);
    echo "[AI worker] processing job #{$jobId} (" . ($isEngine ? 'engine' : 'legacy') . ", lecture {$job['lecture_id']})\n";

    $guard = 0;
    do {
        $job = $isEngine ? $engine->step($jobId) : $legacy->step($jobId);
        $guard++;
        $step = $job['current_step'] ?? $job['stage_label'] ?? '?';
        echo "  step={$step} status={$job['status']} progress={$job['progress']}%\n";
        $maxSteps = $isEngine ? 80 : 60;
        if ($guard > $maxSteps) {
            echo "  aborting job #{$jobId}: too many steps\n";
            break;
        }
    } while (!in_array($job['status'], ['completed', 'failed', 'cancelled'], true));

    if ($job['status'] === 'failed') {
        echo "  job #{$jobId} FAILED: {$job['error']}\n";
    } else {
        echo "  job #{$jobId} {$job['status']}\n";
    }
    $processed++;
}

echo "[AI worker] done ({$processed} job(s))\n";
