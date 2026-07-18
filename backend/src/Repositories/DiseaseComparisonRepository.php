<?php
namespace App\Repositories;

class DiseaseComparisonRepository extends BaseRepository
{
    public function replaceForLecture(int $lectureId, ?int $courseId, ?int $userId, array $comparisons): int
    {
        $this->db->prepare('DELETE FROM disease_comparisons WHERE lecture_id = ? AND source = ?')
            ->execute([$lectureId, 'ai']);
        if (!$comparisons) {
            return 0;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO disease_comparisons
                (lecture_id, course_id, title, diseases, comparison_rows, status, source, sort_order, created_by)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $n = 0;
        foreach ($comparisons as $c) {
            $title = trim((string) ($c['title'] ?? 'Comparison'));
            $stmt->execute([
                $lectureId, $courseId, $title,
                json_encode($c['diseases'] ?? [], JSON_UNESCAPED_UNICODE),
                json_encode($c['comparison_rows'] ?? [], JSON_UNESCAPED_UNICODE),
                'draft', 'ai', $n, $userId,
            ]);
            $n++;
        }
        return $n;
    }

    public function listByLecture(int $lectureId, ?string $status = null): array
    {
        $sql = 'SELECT * FROM disease_comparisons WHERE lecture_id = ?';
        $params = [$lectureId];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map([$this, 'hydrate'], $stmt->fetchAll() ?: []);
    }

    public function setStatusForLecture(int $lectureId, string $status): void
    {
        $this->db->prepare('UPDATE disease_comparisons SET status = ? WHERE lecture_id = ?')
            ->execute([$status, $lectureId]);
    }

    private function hydrate(array $row): array
    {
        $row['diseases'] = $row['diseases'] ? (json_decode($row['diseases'], true) ?: []) : [];
        $row['comparison_rows'] = $row['comparison_rows'] ? (json_decode($row['comparison_rows'], true) ?: []) : [];
        return $row;
    }
}
