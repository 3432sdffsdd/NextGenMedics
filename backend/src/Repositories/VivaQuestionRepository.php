<?php
namespace App\Repositories;

class VivaQuestionRepository extends BaseRepository
{
    public function insertMany(int $lectureId, ?int $courseId, ?int $userId, array $items): int
    {
        if (!$items) {
            return 0;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO viva_questions
                (lecture_id, course_id, question, answer, topic, difficulty, status, source, sort_order, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $order = $this->maxSort($lectureId);
        $n = 0;
        foreach ($items as $q) {
            $question = trim((string) ($q['question'] ?? ''));
            $answer = trim((string) ($q['answer'] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }
            $order++;
            $diff = strtolower((string) ($q['difficulty'] ?? 'moderate'));
            if (!in_array($diff, ['easy', 'moderate', 'difficult'], true)) {
                $diff = 'moderate';
            }
            $stmt->execute([
                $lectureId, $courseId, $question, $answer,
                $q['topic'] ?? null, $diff, 'draft', 'ai', $order, $userId,
            ]);
            $n++;
        }
        return $n;
    }

    public function listByLecture(int $lectureId, ?string $status = null): array
    {
        $sql = 'SELECT * FROM viva_questions WHERE lecture_id = ?';
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

    public function countByLecture(int $lectureId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM viva_questions WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn();
    }

    public function existingQuestions(int $lectureId): array
    {
        $stmt = $this->db->prepare('SELECT question FROM viva_questions WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return array_column($stmt->fetchAll() ?: [], 'question');
    }

    public function deleteAiByLecture(int $lectureId): void
    {
        $this->db->prepare("DELETE FROM viva_questions WHERE lecture_id = ? AND source = 'ai'")
            ->execute([$lectureId]);
    }

    public function setStatusForLecture(int $lectureId, string $status): void
    {
        $this->db->prepare('UPDATE viva_questions SET status = ? WHERE lecture_id = ?')
            ->execute([$status, $lectureId]);
    }

    private function maxSort(int $lectureId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(sort_order),0) FROM viva_questions WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn();
    }
}
