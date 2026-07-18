<?php
/**
 * AI Generation Engine daemon — runs continuously.
 *
 * - Picks pending / processing / failed jobs
 * - Advances one stage at a time
 * - On transient failure, waits a few seconds and retries automatically
 * - No teacher interaction required
 *
 * Start once:
 *   php backend/scripts/ai-engine-daemon.php
 * Or:
 *   backend\cron\run-ai-engine-worker.bat
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

$lockDir = __DIR__ . '/../storage/cache';
if (!is_dir($lockDir)) {
    @mkdir($lockDir, 0775, true);
}
$lockFile = $lockDir . '/ai-engine-worker.lock';
$fp = @fopen($lockFile, 'c+');
if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
    // Another daemon is already running.
    exit(0);
}
fwrite($fp, (string) getmypid());
fflush($fp);

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

@set_time_limit(0);
ignore_user_abort(true);

$idleSeconds = 5;      // wait when no jobs
$retrySeconds = 8;     // wait after a transient failure before next attempt
$maxIdleCycles = 90;   // exit after ~7.5 min idle so kick can restart fresh
$idleCycles = 0;

echo "[AI daemon] started pid=" . getmypid() . "\n";

while ($idleCycles < $maxIdleCycles) {
    @touch($lockFile);
    $job = $jobs->claimNextWorkable(true);
    if (!$job) {
        $idleCycles++;
        sleep($idleSeconds);
        continue;
    }

    $idleCycles = 0;
    $jobId = (int) $job['id'];
    $isEngine = !empty($job['engine']);
    echo "[AI daemon] job #{$jobId} (" . ($isEngine ? 'engine' : 'legacy') . ") lecture={$job['lecture_id']}\n";

    $guard = 0;
    $maxSteps = $isEngine ? 120 : 80;
    $hadTransientFail = false;

            do {
        @touch($lockFile);
        try {
            $job = $isEngine ? $engine->step($jobId) : $legacy->step($jobId);
        } catch (Throwable $e) {
            echo "  exception: {$e->getMessage()}\n";
            $hadTransientFail = true;
            break;
        }

        $guard++;
        $label = $job['stage_label'] ?? $job['current_step'] ?? '?';
        $status = $job['status'] ?? '?';
        $progress = $job['progress'] ?? 0;
        echo "  [{$guard}] {$label} | {$status} | {$progress}%\n";

        // Soft-retry path: engine keeps status=processing with "Retrying…" label.
        if ($isEngine && str_starts_with(strtolower((string) $label), 'retrying')) {
            $hadTransientFail = true;
            break;
        }

        if ($guard >= $maxSteps) {
            echo "  guard limit reached\n";
            break;
        }
    } while (!in_array($job['status'] ?? '', ['completed', 'failed', 'cancelled'], true));

    if (($job['status'] ?? '') === 'completed') {
        echo "  completed\n";
        sleep(1);
        continue;
    }

    if (($job['status'] ?? '') === 'cancelled') {
        echo "  cancelled\n";
        sleep(1);
        continue;
    }

    // Failed or transient — wait, then loop will claim/retry automatically.
    echo "  will auto-retry in {$retrySeconds}s\n";
    sleep($retrySeconds);
}

echo "[AI daemon] idle exit\n";
flock($fp, LOCK_UN);
fclose($fp);
@unlink($lockFile);
