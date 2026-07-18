<?php
namespace App\Services;

/**
 * Starts (or reuses) the background AI engine worker so jobs continue
 * without the teacher keeping the browser open or clicking Resume.
 */
class AiWorkerLauncher
{
    public static function kick(): void
    {
        $lockDir = dirname(__DIR__, 2) . '/storage/cache';
        if (!is_dir($lockDir)) {
            @mkdir($lockDir, 0775, true);
        }
        $lockFile = $lockDir . '/ai-engine-worker.lock';

        // If a worker heartbeat is fresh, do nothing.
        if (is_file($lockFile)) {
            $age = time() - (int) @filemtime($lockFile);
            if ($age >= 0 && $age < 20) {
                return;
            }
        }

        $php = self::phpBinary();
        $script = dirname(__DIR__, 2) . '/scripts/ai-engine-daemon.php';
        if (!is_file($script) || !is_file($php)) {
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'start /B "" ' . escapeshellarg($php) . ' ' . escapeshellarg($script) . ' >NUL 2>&1';
            @pclose(@popen($cmd, 'r'));
            return;
        }

        $cmd = escapeshellarg($php) . ' ' . escapeshellarg($script) . ' > /dev/null 2>&1 &';
        @exec($cmd);
    }

    private static function phpBinary(): string
    {
        if (defined('PHP_BINARY') && PHP_BINARY && is_file(PHP_BINARY)) {
            return PHP_BINARY;
        }
        $xampp = 'C:\\xampp\\php\\php.exe';
        if (is_file($xampp)) {
            return $xampp;
        }
        return 'php';
    }
}
