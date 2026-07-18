<?php
namespace App\Repositories;

class AiJobStageRepository extends BaseRepository
{
    public function seedForJob(int $jobId, array $definitions): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ai_job_stages
                (job_id, stage_group, stage_key, title, status, target, sort_order)
             VALUES (?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE title = VALUES(title), target = VALUES(target)'
        );
        $order = 0;
        foreach ($definitions as $def) {
            $stmt->execute([
                $jobId,
                (int) $def['group'],
                $def['key'],
                $def['title'],
                'pending',
                (int) ($def['target'] ?? 0),
                $order++,
            ]);
        }
    }

    public function listByJob(int $jobId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ai_job_stages WHERE job_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$jobId]);
        return $stmt->fetchAll() ?: [];
    }

    public function findByKey(int $jobId, string $stageKey): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ai_job_stages WHERE job_id = ? AND stage_key = ? LIMIT 1'
        );
        $stmt->execute([$jobId, $stageKey]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Next incomplete stage (pending or failed — for resume). Never returns completed. */
    public function nextActionable(int $jobId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM ai_job_stages
             WHERE job_id = ? AND status IN ('pending','running','failed')
             ORDER BY sort_order ASC, id ASC
             LIMIT 1"
        );
        $stmt->execute([$jobId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function markRunning(int $stageId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE ai_job_stages SET status = 'running', started_at = COALESCE(started_at, NOW()), error = NULL WHERE id = ?"
        );
        $stmt->execute([$stageId]);
    }

    public function markCompleted(int $stageId, array $usage = []): void
    {
        $stmt = $this->db->prepare(
            "UPDATE ai_job_stages SET
                status = 'completed', progress = 100, done = GREATEST(done, target),
                prompt_tokens = prompt_tokens + ?, completion_tokens = completion_tokens + ?,
                total_tokens = total_tokens + ?, retries = retries + ?, latency_ms = latency_ms + ?,
                completed_at = NOW(), error = NULL
             WHERE id = ?"
        );
        $stmt->execute([
            (int) ($usage['prompt_tokens'] ?? 0),
            (int) ($usage['completion_tokens'] ?? 0),
            (int) ($usage['total_tokens'] ?? 0),
            (int) ($usage['retries'] ?? 0),
            (int) ($usage['latency_ms'] ?? 0),
            $stageId,
        ]);
    }

    public function markFailed(int $stageId, string $error, array $usage = []): void
    {
        $stmt = $this->db->prepare(
            "UPDATE ai_job_stages SET
                status = 'failed', error = ?,
                prompt_tokens = prompt_tokens + ?, completion_tokens = completion_tokens + ?,
                total_tokens = total_tokens + ?, retries = retries + ?, latency_ms = latency_ms + ?
             WHERE id = ?"
        );
        $stmt->execute([
            mb_substr($error, 0, 2000),
            (int) ($usage['prompt_tokens'] ?? 0),
            (int) ($usage['completion_tokens'] ?? 0),
            (int) ($usage['total_tokens'] ?? 0),
            max(1, (int) ($usage['retries'] ?? 1)),
            (int) ($usage['latency_ms'] ?? 0),
            $stageId,
        ]);
    }

    /**
     * Soft-fail: keep the stage pending so the next poll retries it automatically.
     * Stores the last error for the UI without stopping the job.
     */
    public function requeueAfterError(int $stageId, string $error, int $retries): void
    {
        $stmt = $this->db->prepare(
            "UPDATE ai_job_stages SET
                status = 'pending',
                error = ?,
                retries = ?,
                progress = LEAST(progress, 99)
             WHERE id = ?"
        );
        $stmt->execute([mb_substr($error, 0, 2000), max(1, $retries), $stageId]);
    }

    public function markSkipped(int $stageId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE ai_job_stages SET status = 'skipped', progress = 100, completed_at = NOW(), error = NULL WHERE id = ?"
        );
        $stmt->execute([$stageId]);
    }

    public function updateProgress(int $stageId, int $done, int $progress, array $usage = []): void
    {
        $stmt = $this->db->prepare(
            "UPDATE ai_job_stages SET
                done = ?, progress = ?,
                prompt_tokens = prompt_tokens + ?, completion_tokens = completion_tokens + ?,
                total_tokens = total_tokens + ?, retries = retries + ?, latency_ms = latency_ms + ?
             WHERE id = ?"
        );
        $stmt->execute([
            $done,
            min(100, max(0, $progress)),
            (int) ($usage['prompt_tokens'] ?? 0),
            (int) ($usage['completion_tokens'] ?? 0),
            (int) ($usage['total_tokens'] ?? 0),
            (int) ($usage['retries'] ?? 0),
            (int) ($usage['latency_ms'] ?? 0),
            $stageId,
        ]);
    }

    /** Reset a failed stage (and later pending ones stay pending) so resume can continue. */
    public function resetFailed(int $jobId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE ai_job_stages SET status = 'pending', error = NULL, progress = 0
             WHERE job_id = ? AND status = 'failed'"
        );
        $stmt->execute([$jobId]);
    }
}
