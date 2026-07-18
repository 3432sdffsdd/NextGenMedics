<?php
namespace App\Repositories;

class MnemonicRepository extends BaseRepository
{
    public function replaceForLecture(int $lectureId, ?int $courseId, ?int $userId, array $items): int
    {
        $this->db->prepare('DELETE FROM mnemonics WHERE lecture_id = ? AND source = ?')
            ->execute([$lectureId, 'ai']);
        if (!$items) {
            return 0;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO mnemonics
                (lecture_id, course_id, topic, mnemonic, explanation, status, source, sort_order, created_by)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $n = 0;
        foreach ($items as $m) {
            $text = trim((string) ($m['mnemonic'] ?? ''));
            if ($text === '') {
                continue;
            }
            $stmt->execute([
                $lectureId, $courseId,
                $m['topic'] ?? null, $text, $m['explanation'] ?? null,
                'draft', 'ai', $n, $userId,
            ]);
            $n++;
        }
        return $n;
    }

    public function listByLecture(int $lectureId, ?string $status = null): array
    {
        $sql = 'SELECT * FROM mnemonics WHERE lecture_id = ?';
        $params = [$lectureId];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public function setStatusForLecture(int $lectureId, string $status): void
    {
        $this->db->prepare('UPDATE mnemonics SET status = ? WHERE lecture_id = ?')
            ->execute([$status, $lectureId]);
    }
}
