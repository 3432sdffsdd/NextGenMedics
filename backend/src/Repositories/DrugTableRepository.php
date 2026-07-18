<?php
namespace App\Repositories;

class DrugTableRepository extends BaseRepository
{
    public function replaceForLecture(int $lectureId, ?int $courseId, ?int $userId, array $drugs): int
    {
        $this->db->prepare('DELETE FROM drug_tables WHERE lecture_id = ? AND source = ?')
            ->execute([$lectureId, 'ai']);
        if (!$drugs) {
            return 0;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO drug_tables
                (lecture_id, course_id, drug_name, drug_class, mechanism, indications,
                 adverse_effects, notes, status, source, sort_order, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $n = 0;
        foreach ($drugs as $d) {
            $name = trim((string) ($d['drug_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $stmt->execute([
                $lectureId, $courseId, $name,
                $d['drug_class'] ?? null,
                $d['mechanism'] ?? null,
                $d['indications'] ?? null,
                $d['adverse_effects'] ?? null,
                $d['notes'] ?? null,
                'draft', 'ai', $n, $userId,
            ]);
            $n++;
        }
        return $n;
    }

    public function listByLecture(int $lectureId, ?string $status = null): array
    {
        $sql = 'SELECT * FROM drug_tables WHERE lecture_id = ?';
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
        $this->db->prepare('UPDATE drug_tables SET status = ? WHERE lecture_id = ?')
            ->execute([$status, $lectureId]);
    }
}
