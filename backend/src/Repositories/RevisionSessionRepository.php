<?php
namespace App\Repositories;

class RevisionSessionRepository extends BaseRepository
{
    public function create(int $studentId, array $items): int
    {
        $stmt = $this->db->prepare('INSERT INTO revision_sessions (student_id) VALUES (?)');
        $stmt->execute([$studentId]);
        $sessionId = (int) $this->db->lastInsertId();

        $itemStmt = $this->db->prepare(
            'INSERT INTO revision_session_items (session_id, item_type, item_id) VALUES (?,?,?)'
        );
        foreach ($items as $item) {
            $itemStmt->execute([$sessionId, $item['item_type'], $item['item_id']]);
        }
        return $sessionId;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM revision_sessions WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $items = $this->db->prepare('SELECT * FROM revision_session_items WHERE session_id = ?');
        $items->execute([$id]);
        $row['items'] = $items->fetchAll();
        if (!empty($row['topics_revised'])) {
            $row['topics_revised'] = json_decode($row['topics_revised'], true);
        }
        if (!empty($row['summary'])) {
            $row['summary'] = json_decode($row['summary'], true);
        }
        return $row;
    }

    public function complete(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE revision_sessions SET completed_at = NOW(), duration_seconds = ?, topics_revised = ?,
             mcqs_solved = ?, mcqs_correct = ?, accuracy = ?, summary = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['duration_seconds'] ?? null,
            isset($data['topics_revised']) ? json_encode($data['topics_revised']) : null,
            $data['mcqs_solved'] ?? 0,
            $data['mcqs_correct'] ?? 0,
            $data['accuracy'] ?? null,
            isset($data['summary']) ? json_encode($data['summary']) : null,
            $id,
        ]);
    }

    public function recent(int $studentId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM revision_sessions WHERE student_id = ? AND completed_at IS NOT NULL
             ORDER BY completed_at DESC LIMIT ?'
        );
        $stmt->execute([$studentId, $limit]);
        return $stmt->fetchAll();
    }
}
