<?php
namespace App\Repositories;

class BatchRepository extends BaseRepository
{
    public function listByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*,
                    (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id = b.id) AS student_count
             FROM batches b
             WHERE b.course_id = ?
             ORDER BY b.created_at DESC'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.title AS course_title,
                    (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id = b.id) AS student_count
             FROM batches b
             JOIN courses c ON c.id = b.course_id
             WHERE b.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO batches (course_id, name, code, start_date, end_date, is_active, created_by)
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['course_id'],
            $data['name'],
            $data['code'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['is_active'] ?? 1,
            $data['created_by'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'code', 'start_date', 'end_date', 'is_active'];
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
        }
        if (!$fields) return false;
        $values[] = $id;
        $stmt = $this->db->prepare('UPDATE batches SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM batches WHERE id = ?')->execute([$id]);
    }

    public function getStudents(int $batchId): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.first_name, u.last_name, u.email, bs.assigned_at
             FROM batch_students bs
             JOIN users u ON u.id = bs.student_id
             WHERE bs.batch_id = ? AND u.deleted_at IS NULL
             ORDER BY u.last_name, u.first_name'
        );
        $stmt->execute([$batchId]);
        return $stmt->fetchAll();
    }

    /** @return int[] */
    public function getStudentIds(int $batchId): array
    {
        $stmt = $this->db->prepare('SELECT student_id FROM batch_students WHERE batch_id = ?');
        $stmt->execute([$batchId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function assignStudents(int $batchId, array $studentIds): void
    {
        $insert = $this->db->prepare('INSERT IGNORE INTO batch_students (batch_id, student_id) VALUES (?, ?)');
        foreach ($studentIds as $studentId) {
            $insert->execute([$batchId, (int) $studentId]);
        }
    }

    public function removeStudent(int $batchId, int $studentId): bool
    {
        return $this->db->prepare('DELETE FROM batch_students WHERE batch_id = ? AND student_id = ?')
            ->execute([$batchId, $studentId]);
    }

    /** Batch ids a student belongs to (for schedule scoping). @return int[] */
    public function batchIdsForStudent(int $studentId): array
    {
        $stmt = $this->db->prepare('SELECT batch_id FROM batch_students WHERE student_id = ?');
        $stmt->execute([$studentId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }
}
