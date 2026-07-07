<?php
namespace App\Repositories;

use PDO;

class ScheduleRepository extends BaseRepository
{
    // Effective (auto) status expression: honours a manual lock, otherwise
    // derives Upcoming / Live / Completed from the current date & time.
    private const EFFECTIVE_STATUS = "
        CASE
            WHEN cs.is_status_locked = 1 THEN cs.status
            WHEN TIMESTAMP(cs.class_date, cs.end_time) < NOW() THEN 'completed'
            WHEN TIMESTAMP(cs.class_date, cs.start_time) <= NOW()
                 AND TIMESTAMP(cs.class_date, cs.end_time) >= NOW() THEN 'live'
            ELSE 'upcoming'
        END";

    private function baseSelect(): string
    {
        return 'SELECT cs.*, ' . self::EFFECTIVE_STATUS . ' AS effective_status,
                       c.title AS course_title,
                       b.name AS batch_name,
                       CONCAT(t.first_name, " ", t.last_name) AS teacher_name
                FROM class_schedule cs
                JOIN courses c ON c.id = cs.course_id
                LEFT JOIN batches b ON b.id = cs.batch_id
                LEFT JOIN users t ON t.id = cs.teacher_id';
    }

    /** Move the computed status onto `status` and keep the stored value as `raw_status`. */
    private function map(?array $row): ?array
    {
        if (!$row) return null;
        $row['raw_status'] = $row['status'] ?? null;
        $row['status'] = $row['effective_status'] ?? $row['status'] ?? 'upcoming';
        unset($row['effective_status']);
        $row['is_status_locked'] = (int) ($row['is_status_locked'] ?? 0);
        return $row;
    }

    private function mapAll(array $rows): array
    {
        return array_map(fn($r) => $this->map($r), $rows);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE cs.id = ?');
        $stmt->execute([$id]);
        return $this->map($stmt->fetch() ?: null);
    }

    /**
     * List schedules with optional filters, scoped to the requesting user's role.
     * Filters: course_id, batch_id, teacher_id, subject, date_from, date_to, status.
     * @param int[] $studentBatchIds Batches the student belongs to (student role only)
     */
    public function listFiltered(array $user, array $filters, array $studentBatchIds = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['course_id'])) { $where[] = 'cs.course_id = ?'; $params[] = (int) $filters['course_id']; }
        if (!empty($filters['batch_id']))  { $where[] = 'cs.batch_id = ?';  $params[] = (int) $filters['batch_id']; }
        if (!empty($filters['teacher_id'])){ $where[] = 'cs.teacher_id = ?';$params[] = (int) $filters['teacher_id']; }
        if (!empty($filters['subject']))   { $where[] = 'cs.subject LIKE ?';$params[] = '%' . $filters['subject'] . '%'; }
        if (!empty($filters['date_from'])) { $where[] = 'cs.class_date >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to']))   { $where[] = 'cs.class_date <= ?'; $params[] = $filters['date_to']; }

        // Role scoping
        $role = $user['role'] ?? null;
        $uid = (int) ($user['id'] ?? 0);
        if ($role === 'teacher') {
            $where[] = '(cs.teacher_id = ? OR c.teacher_id = ? OR EXISTS
                        (SELECT 1 FROM course_teachers ct WHERE ct.course_id = c.id AND ct.teacher_id = ?))';
            array_push($params, $uid, $uid, $uid);
        } elseif ($role === 'student') {
            $where[] = 'EXISTS (SELECT 1 FROM course_enrollments ce
                        WHERE ce.course_id = c.id AND ce.student_id = ? AND ce.status = "active")';
            $params[] = $uid;
            // A batch-specific lecture is only visible to that batch's students.
            if ($studentBatchIds) {
                $ph = implode(',', array_fill(0, count($studentBatchIds), '?'));
                $where[] = "(cs.batch_id IS NULL OR cs.batch_id IN ($ph))";
                $params = array_merge($params, $studentBatchIds);
            } else {
                $where[] = 'cs.batch_id IS NULL';
            }
        }

        $sql = $this->baseSelect() . ' WHERE ' . implode(' AND ', $where)
             . ' ORDER BY cs.class_date ASC, cs.start_time ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $this->mapAll($stmt->fetchAll());

        // Status filter applies to the *computed* status.
        if (!empty($filters['status'])) {
            $rows = array_values(array_filter($rows, fn($r) => $r['status'] === $filters['status']));
        }
        return $rows;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO class_schedule
             (course_id, batch_id, teacher_id, subject, lecture_title, lecture_number, topic_covered,
              description, class_date, start_time, end_time, duration_minutes, meeting_link, recording_link,
              attachment_path, attachment_name, remarks, status, is_status_locked, rescheduled_from_id, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['course_id'],
            $data['batch_id'] ?? null,
            $data['teacher_id'] ?? null,
            $data['subject'] ?? null,
            $data['lecture_title'],
            $data['lecture_number'] ?? null,
            $data['topic_covered'] ?? null,
            $data['description'] ?? null,
            $data['class_date'],
            $data['start_time'],
            $data['end_time'],
            $data['duration_minutes'] ?? null,
            $data['meeting_link'] ?? null,
            $data['recording_link'] ?? null,
            $data['attachment_path'] ?? null,
            $data['attachment_name'] ?? null,
            $data['remarks'] ?? null,
            $data['status'] ?? 'upcoming',
            $data['is_status_locked'] ?? 0,
            $data['rescheduled_from_id'] ?? null,
            $data['created_by'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'batch_id', 'teacher_id', 'subject', 'lecture_title', 'lecture_number', 'topic_covered',
            'description', 'class_date', 'start_time', 'end_time', 'duration_minutes', 'meeting_link',
            'recording_link', 'attachment_path', 'attachment_name', 'remarks', 'status', 'is_status_locked',
        ];
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
        $stmt = $this->db->prepare('UPDATE class_schedule SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM class_schedule WHERE id = ?')->execute([$id]);
    }

    public function setStatus(int $id, string $status, bool $locked = true): bool
    {
        return $this->db->prepare('UPDATE class_schedule SET status = ?, is_status_locked = ? WHERE id = ?')
            ->execute([$status, $locked ? 1 : 0, $id]);
    }

    /**
     * Find scheduling conflicts for a teacher or batch on a given date/time window.
     * Overlap rule: existing.start < new.end AND existing.end > new.start.
     */
    public function findConflicts(?int $teacherId, ?int $batchId, string $date, string $start, string $end, ?int $excludeId = null): array
    {
        if (!$teacherId && !$batchId) return [];

        $where = ['cs.class_date = ?', 'cs.start_time < ?', 'cs.end_time > ?', "cs.status NOT IN ('cancelled','rescheduled')"];
        $params = [$date, $end, $start];

        $who = [];
        if ($teacherId) { $who[] = 'cs.teacher_id = ?'; $params[] = $teacherId; }
        if ($batchId)   { $who[] = 'cs.batch_id = ?';   $params[] = $batchId; }
        $where[] = '(' . implode(' OR ', $who) . ')';

        if ($excludeId) { $where[] = 'cs.id != ?'; $params[] = $excludeId; }

        $sql = 'SELECT cs.id, cs.lecture_title, cs.class_date, cs.start_time, cs.end_time, cs.teacher_id, cs.batch_id,
                       CONCAT(t.first_name, " ", t.last_name) AS teacher_name
                FROM class_schedule cs
                LEFT JOIN users t ON t.id = cs.teacher_id
                WHERE ' . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return int[] active enrolled student ids for a course */
    public function enrolledStudentIds(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT student_id FROM course_enrollments WHERE course_id = ? AND status = "active"'
        );
        $stmt->execute([$courseId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Auto-complete rows whose end time has passed (used by cron/reconcile). */
    public function reconcileCompleted(): int
    {
        $stmt = $this->db->prepare(
            "UPDATE class_schedule
             SET status = 'completed'
             WHERE is_status_locked = 0
               AND status = 'upcoming'
               AND TIMESTAMP(class_date, end_time) < NOW()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    /** Delete all lectures for a course within a calendar month (YYYY-MM). */
    public function deleteByCourseMonth(int $courseId, string $monthYear): int
    {
        $start = $monthYear . '-01';
        $end = date('Y-m-t', strtotime($start));
        $stmt = $this->db->prepare(
            'DELETE FROM class_schedule WHERE course_id = ? AND class_date >= ? AND class_date <= ?'
        );
        $stmt->execute([$courseId, $start, $end]);
        return $stmt->rowCount();
    }

    /** @return array<int, array<string, mixed>> */
    public function listMonthUploads(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT smu.*, CONCAT(u.first_name, " ", u.last_name) AS uploader_name
             FROM schedule_month_uploads smu
             LEFT JOIN users u ON u.id = smu.uploaded_by
             WHERE smu.course_id = ?
             ORDER BY smu.month_year DESC'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function getMonthUpload(int $courseId, string $monthYear): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT smu.*, CONCAT(u.first_name, " ", u.last_name) AS uploader_name
             FROM schedule_month_uploads smu
             LEFT JOIN users u ON u.id = smu.uploaded_by
             WHERE smu.course_id = ? AND smu.month_year = ?'
        );
        $stmt->execute([$courseId, $monthYear]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function upsertMonthUpload(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO schedule_month_uploads
             (course_id, month_year, file_path, file_name, row_count, uploaded_by)
             VALUES (?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
               file_path = VALUES(file_path),
               file_name = VALUES(file_name),
               row_count = VALUES(row_count),
               uploaded_by = VALUES(uploaded_by),
               updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            $data['course_id'],
            $data['month_year'],
            $data['file_path'] ?? null,
            $data['file_name'] ?? null,
            $data['row_count'] ?? 0,
            $data['uploaded_by'] ?? null,
        ]);
    }

    public function deleteMonthUpload(int $courseId, string $monthYear): void
    {
        $this->db->prepare(
            'DELETE FROM schedule_month_uploads WHERE course_id = ? AND month_year = ?'
        )->execute([$courseId, $monthYear]);
    }

    public function findTeacherIdByName(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT u.id FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE r.slug IN ("teacher", "admin")
               AND LOWER(CONCAT(u.first_name, " ", u.last_name)) = LOWER(?)
             LIMIT 1'
        );
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }

        $stmt = $this->db->prepare(
            'SELECT u.id FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE r.slug IN ("teacher", "admin")
               AND CONCAT(u.first_name, " ", u.last_name) LIKE ?
             LIMIT 1'
        );
        $stmt->execute(['%' . $name . '%']);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollBack(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }
}
