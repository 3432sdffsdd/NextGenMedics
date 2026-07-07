<?php
namespace App\Repositories;

class MistakeRepository extends BaseRepository
{
    public function recordWrong(int $studentId, array $mcq, ?string $selected): void
    {
        $meta = $this->mcqMeta((int) $mcq['lecture_id'], $mcq['topic'] ?? null);
        $stmt = $this->db->prepare(
            'INSERT INTO student_mistakes (student_id, mcq_id, subject, chapter, topic, wrong_count, consecutive_correct, status, last_wrong_at, last_attempt_at)
             VALUES (?,?,?,?,?,1,0,"active",NOW(),NOW())
             ON DUPLICATE KEY UPDATE
                wrong_count = wrong_count + 1,
                consecutive_correct = 0,
                status = "active",
                last_wrong_at = NOW(),
                last_attempt_at = NOW(),
                subject = VALUES(subject),
                chapter = VALUES(chapter),
                topic = VALUES(topic)'
        );
        $stmt->execute([
            $studentId, (int) $mcq['id'],
            $meta['subject'], $meta['chapter'], $meta['topic'] ?: ($mcq['topic'] ?? null),
        ]);
    }

    public function recordAnswer(int $studentId, int $mcqId, bool $isCorrect): void
    {
        if ($isCorrect) {
            $stmt = $this->db->prepare(
                'UPDATE student_mistakes
                 SET consecutive_correct = consecutive_correct + 1,
                     last_attempt_at = NOW(),
                     status = IF(consecutive_correct + 1 >= 3, "mastered", status)
                 WHERE student_id = ? AND mcq_id = ? AND status = "active"'
            );
            $stmt->execute([$studentId, $mcqId]);
            return;
        }
        $stmt = $this->db->prepare('SELECT lecture_id, topic FROM mcqs WHERE id = ?');
        $stmt->execute([$mcqId]);
        $mcq = $stmt->fetch();
        if ($mcq) {
            $this->recordWrong($studentId, $mcq + ['id' => $mcqId], null);
        }
    }

    public function stats(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                SUM(status = "active") AS remaining,
                SUM(status = "mastered") AS mastered,
                COUNT(*) AS total
             FROM student_mistakes WHERE student_id = ?'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: ['remaining' => 0, 'mastered' => 0, 'total' => 0];
    }

    public function listForStudent(int $studentId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['sm.student_id = ?'];
        $params = [$studentId];
        if (($filters['status'] ?? '') === 'active' || ($filters['status'] ?? '') === 'mastered') {
            $where[] = 'sm.status = ?';
            $params[] = $filters['status'];
        } elseif (!($filters['status'] ?? '')) {
            $where[] = 'sm.status = "active"';
        }
        foreach (['subject', 'chapter', 'topic'] as $col) {
            if (!empty($filters[$col])) {
                $where[] = "sm.{$col} LIKE ?";
                $params[] = '%' . $filters[$col] . '%';
            }
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(sm.last_wrong_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(sm.last_wrong_at) <= ?';
            $params[] = $filters['date_to'];
        }
        $sql = 'SELECT sm.*, m.question, m.option_a, m.option_b, m.option_c, m.option_d, m.option_e,
                       m.correct_option, m.explanation, m.difficulty
                FROM student_mistakes sm
                JOIN mcqs m ON m.id = sm.mcq_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY sm.last_wrong_at DESC';
        return $this->paginate($sql, $params, $page, $perPage);
    }

    public function practiceIds(int $studentId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT mcq_id FROM student_mistakes
             WHERE student_id = ? AND status = "active"
             ORDER BY last_wrong_at DESC LIMIT ?'
        );
        $stmt->execute([$studentId, $limit]);
        return array_map('intval', array_column($stmt->fetchAll(), 'mcq_id'));
    }

    private function mcqMeta(int $lectureId, ?string $topic): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.title AS chapter, m.title AS subject
             FROM lectures l
             JOIN chapters c ON c.id = l.chapter_id
             JOIN modules m ON m.id = c.module_id
             WHERE l.id = ?'
        );
        $stmt->execute([$lectureId]);
        $row = $stmt->fetch() ?: [];
        return [
            'subject' => $row['subject'] ?? null,
            'chapter' => $row['chapter'] ?? null,
            'topic'   => $topic,
        ];
    }
}
