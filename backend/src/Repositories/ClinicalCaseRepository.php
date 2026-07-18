<?php
namespace App\Repositories;

class ClinicalCaseRepository extends BaseRepository
{
    public function replaceForLecture(int $lectureId, ?int $courseId, ?int $userId, array $cases): int
    {
        $this->db->prepare('DELETE FROM clinical_cases WHERE lecture_id = ? AND source = ?')
            ->execute([$lectureId, 'ai']);
        if (!$cases) {
            return 0;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO clinical_cases
                (lecture_id, course_id, title, scenario, questions, diagnosis, discussion,
                 status, source, sort_order, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $n = 0;
        foreach ($cases as $c) {
            $title = trim((string) ($c['title'] ?? 'Case'));
            $scenario = trim((string) ($c['scenario'] ?? ''));
            if ($scenario === '') {
                continue;
            }
            $stmt->execute([
                $lectureId, $courseId, $title, $scenario,
                json_encode($c['questions'] ?? [], JSON_UNESCAPED_UNICODE),
                $c['diagnosis'] ?? null,
                $c['discussion'] ?? null,
                'draft', 'ai', $n, $userId,
            ]);
            $n++;
        }
        return $n;
    }

    public function insertMany(int $lectureId, ?int $courseId, ?int $userId, array $cases): int
    {
        if (!$cases) {
            return 0;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO clinical_cases
                (lecture_id, course_id, title, scenario, questions, diagnosis, discussion,
                 status, source, sort_order, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $order = $this->maxSort($lectureId);
        $n = 0;
        foreach ($cases as $c) {
            $scenario = trim((string) ($c['scenario'] ?? ''));
            if ($scenario === '') {
                continue;
            }
            $order++;
            $stmt->execute([
                $lectureId, $courseId,
                trim((string) ($c['title'] ?? 'Case')),
                $scenario,
                json_encode($c['questions'] ?? [], JSON_UNESCAPED_UNICODE),
                $c['diagnosis'] ?? null,
                $c['discussion'] ?? null,
                'draft', 'ai', $order, $userId,
            ]);
            $n++;
        }
        return $n;
    }

    public function listByLecture(int $lectureId, ?string $status = null): array
    {
        $sql = 'SELECT * FROM clinical_cases WHERE lecture_id = ?';
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

    public function countByLecture(int $lectureId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM clinical_cases WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn();
    }

    public function setStatusForLecture(int $lectureId, string $status): void
    {
        $this->db->prepare('UPDATE clinical_cases SET status = ? WHERE lecture_id = ?')
            ->execute([$status, $lectureId]);
    }

    private function maxSort(int $lectureId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(sort_order),0) FROM clinical_cases WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        return (int) $stmt->fetchColumn();
    }

    private function hydrate(array $row): array
    {
        $row['questions'] = $row['questions'] ? (json_decode($row['questions'], true) ?: []) : [];
        return $row;
    }
}
