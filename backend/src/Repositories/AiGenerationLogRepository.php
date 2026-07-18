<?php
namespace App\Repositories;

class AiGenerationLogRepository extends BaseRepository
{
    public function log(array $row): void
    {
        if (!$this->tableExists('ai_generation_logs')) {
            return;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO ai_generation_logs
                (job_id, stage_id, stage_key, model, status, prompt_tokens, completion_tokens,
                 total_tokens, latency_ms, retries, estimated_cost, error)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $row['job_id'] ?? null,
            $row['stage_id'] ?? null,
            $row['stage_key'] ?? null,
            $row['model'] ?? null,
            $row['status'] ?? 'success',
            (int) ($row['prompt_tokens'] ?? 0),
            (int) ($row['completion_tokens'] ?? 0),
            (int) ($row['total_tokens'] ?? 0),
            (int) ($row['latency_ms'] ?? 0),
            (int) ($row['retries'] ?? 0),
            (float) ($row['estimated_cost'] ?? 0),
            isset($row['error']) ? mb_substr((string) $row['error'], 0, 2000) : null,
        ]);
    }
}
