<?php
namespace App\Repositories;

class AiJobRepository extends BaseRepository
{
    public function create(int $lectureId, ?int $courseId, ?int $userId, array $options): int
    {
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
        return (int) $this->db->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ai_generation_jobs WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /** Most recent active/complete job for a lecture (for status polling). */
    public function latestForLecture(int $lectureId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM ai_generation_jobs WHERE lecture_id = ? ORDER BY id DESC LIMIT 1'
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

    /** Claim the next queued job (worker loop). */
    public function claimNextPending(): ?array
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->query(
                "SELECT * FROM ai_generation_jobs WHERE status = 'pending' ORDER BY id ASC LIMIT 1 FOR UPDATE"
            );
            $row = $stmt->fetch();
            if (!$row) {
                $this->db->commit();
                return null;
            }
            $upd = $this->db->prepare("UPDATE ai_generation_jobs SET status = 'processing' WHERE id = ?");
            $upd->execute([$row['id']]);
            $this->db->commit();
            $row['status'] = 'processing';
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

    private function hydrate(array $row): array
    {
        $row['options'] = $row['options'] ? json_decode($row['options'], true) : [];
        return $row;
    }
}
