<?php
namespace App\Repositories;

class AiJobRepository extends BaseRepository
{
    public function create(int $lectureId, ?int $courseId, ?int $userId, array $options, bool $engine = false): int
    {
        $hasEngine = $this->columnExists('ai_generation_jobs', 'engine');
        if ($hasEngine) {
            $stmt = $this->db->prepare(
                'INSERT INTO ai_generation_jobs
                    (lecture_id, course_id, requested_by, status, current_step, options,
                     flashcard_target, mcq_target, engine, model, started_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $lectureId,
                $courseId,
                $userId,
                'pending',
                $engine ? 'extract' : 'extract',
                json_encode($options),
                (int) ($options['flashcards'] ?? 0),
                (int) ($options['mcqs'] ?? 0),
                $engine ? 1 : 0,
                $options['model'] ?? null,
                $engine ? date('Y-m-d H:i:s') : null,
            ]);
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO ai_generation_jobs
                    (lecture_id, course_id, requested_by, status, current_step, options, flashcard_target, mcq_target)
                 VALUES (?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $lectureId,
                $courseId,
                $userId,
                'pending',
                'extract',
                json_encode($options),
                (int) ($options['flashcards'] ?? 0),
                (int) ($options['mcqs'] ?? 0),
            ]);
        }
        return (int) $this->db->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ai_generation_jobs WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function latestForLecture(int $lectureId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ai_generation_jobs WHERE lecture_id = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$lectureId]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function latestEngineForLecture(int $lectureId): ?array
    {
        if (!$this->columnExists('ai_generation_jobs', 'engine')) {
            return $this->latestForLecture($lectureId);
        }
        $stmt = $this->db->prepare(
            'SELECT * FROM ai_generation_jobs WHERE lecture_id = ? AND engine = 1 ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$lectureId]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function hasActive(int $lectureId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM ai_generation_jobs WHERE lecture_id = ? AND status IN ('pending','processing')"
        );
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function claimNextPending(): ?array
    {
        return $this->claimNextWorkable(false);
    }

    /**
     * Claim the next job that needs work:
     * - pending
     * - processing (continue)
     * - failed (auto-retry after a short cool-down), unless the error is permanent
     */
    public function claimNextWorkable(bool $includeFailed = true): ?array
    {
        $this->db->beginTransaction();
        try {
            $statuses = $includeFailed
                ? "('pending','processing','failed')"
                : "('pending','processing')";

            $stmt = $this->db->query(
                "SELECT * FROM ai_generation_jobs
                 WHERE status IN {$statuses}
                   AND NOT (
                     status = 'failed' AND (
                       LOWER(COALESCE(error,'')) LIKE '%gemini_api_key%'
                       OR LOWER(COALESCE(error,'')) LIKE '%not configured%'
                       OR LOWER(COALESCE(error,'')) LIKE '%no powerpoint%'
                       OR LOWER(COALESCE(error,'')) LIKE '%upload a file%'
                       OR LOWER(COALESCE(error,'')) LIKE '%lecture text is missing%'
                     )
                   )
                 ORDER BY
                   CASE status
                     WHEN 'pending' THEN 0
                     WHEN 'processing' THEN 1
                     WHEN 'failed' THEN 2
                     ELSE 3
                   END,
                   id ASC
                 LIMIT 1
                 FOR UPDATE"
            );
            $row = $stmt->fetch();
            if (!$row) {
                $this->db->commit();
                return null;
            }

            $upd = $this->db->prepare("UPDATE ai_generation_jobs SET status = 'processing', error = NULL WHERE id = ?");
            $upd->execute([$row['id']]);
            $this->db->commit();
            $row['status'] = 'processing';
            $row['error'] = null;
            return $this->hydrate($row);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $fields): void
    {
        if (!$fields) {
            return;
        }
        $sets = [];
        $vals = [];
        foreach ($fields as $col => $val) {
            $sets[] = "{$col} = ?";
            $vals[] = $val;
        }
        $vals[] = $id;
        $stmt = $this->db->prepare('UPDATE ai_generation_jobs SET ' . implode(', ', $sets) . ' WHERE id = ?');
        $stmt->execute($vals);
    }

    public function setError(int $id, string $error): void
    {
        $stmt = $this->db->prepare("UPDATE ai_generation_jobs SET status = 'failed', error = ? WHERE id = ?");
        $stmt->execute([mb_substr($error, 0, 2000), $id]);
    }

    public function cancel(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE ai_generation_jobs SET status = 'cancelled' WHERE id = ? AND status IN ('pending','processing','failed')"
        );
        $stmt->execute([$id]);
    }

    /** Admin dashboard listing with optional status filter. */
    public function listAdmin(?string $status = null, int $page = 1, int $perPage = 30): array
    {
        $where = '1=1';
        $params = [];
        if ($this->columnExists('ai_generation_jobs', 'engine')) {
            $where .= ' AND j.engine = 1';
        }
        if ($status && in_array($status, ['pending', 'processing', 'completed', 'failed', 'cancelled'], true)) {
            $where .= ' AND j.status = ?';
            $params[] = $status;
        }
        $sql = "SELECT j.*, l.title AS lecture_title, c.title AS course_title, u.full_name AS requested_by_name
                FROM ai_generation_jobs j
                LEFT JOIN lectures l ON l.id = j.lecture_id
                LEFT JOIN courses c ON c.id = j.course_id
                LEFT JOIN users u ON u.id = j.requested_by
                WHERE {$where}
                ORDER BY j.id DESC";
        $result = $this->paginate($sql, $params, $page, $perPage);
        $result['items'] = array_map([$this, 'hydrate'], $result['items']);
        return $result;
    }

    public function adminCounts(): array
    {
        $engineFilter = $this->columnExists('ai_generation_jobs', 'engine') ? ' AND engine = 1' : '';
        $stmt = $this->db->query(
            "SELECT status, COUNT(*) AS cnt FROM ai_generation_jobs WHERE 1=1 {$engineFilter} GROUP BY status"
        );
        $out = ['pending' => 0, 'processing' => 0, 'completed' => 0, 'failed' => 0, 'cancelled' => 0, 'running' => 0];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $out[$row['status']] = (int) $row['cnt'];
        }
        $out['running'] = $out['pending'] + $out['processing'];
        return $out;
    }

    private function hydrate(array $row): array
    {
        $row['options'] = !empty($row['options']) ? (json_decode($row['options'], true) ?: []) : [];
        if (isset($row['engine'])) {
            $row['engine'] = (int) $row['engine'];
        }
        return $row;
    }
}
