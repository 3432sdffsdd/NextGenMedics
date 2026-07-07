<?php
namespace App\Repositories;

class CourseRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug,
                    CONCAT(t.first_name, " ", t.last_name) AS teacher_name,
                    (SELECT GROUP_CONCAT(CONCAT(u.first_name, " ", u.last_name) ORDER BY u.first_name SEPARATOR ", ")
                       FROM course_teachers ct JOIN users u ON u.id = ct.teacher_id
                       WHERE ct.course_id = c.id AND u.deleted_at IS NULL) AS teacher_names,
                    (SELECT GROUP_CONCAT(ct.teacher_id ORDER BY ct.assigned_at)
                       FROM course_teachers ct JOIN users u ON u.id = ct.teacher_id
                       WHERE ct.course_id = c.id AND u.deleted_at IS NULL) AS teacher_ids
             FROM courses c
             LEFT JOIN course_categories cat ON cat.id = c.category_id
             LEFT JOIN users t ON t.id = c.teacher_id
             WHERE c.id = ? AND c.deleted_at IS NULL'
        );
        $stmt->execute([$id]);
        $course = $stmt->fetch() ?: null;
        if ($course) {
            $course['teacher_ids'] = $this->parseTeacherIds($course['teacher_ids'] ?? null);
        }
        return $course;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, cat.name AS category_name, cat.slug AS category_slug,
                    CONCAT(t.first_name, " ", t.last_name) AS teacher_name
             FROM courses c
             LEFT JOIN course_categories cat ON cat.id = c.category_id
             LEFT JOIN users t ON t.id = c.teacher_id
             WHERE c.slug = ? AND c.deleted_at IS NULL'
        );
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM courses WHERE slug = ? AND deleted_at IS NULL';
        $params = [$slug];
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function listPublished(int $page, int $perPage, ?string $category = null): array
    {
        $params = [];
        $where = "c.status = 'published' AND c.deleted_at IS NULL";

        if ($category) {
            $where .= ' AND cat.slug = ?';
            $params[] = $category;
        }

        $sql = "SELECT c.id, c.title, c.slug, c.subtitle, c.short_description, c.thumbnail, c.banner,
                       c.duration, c.level, c.fee, c.certificate_available, c.enrollment_status,
                       cat.name AS category_name, cat.slug AS category_slug,
                       CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
                FROM courses c
                LEFT JOIN course_categories cat ON cat.id = c.category_id
                LEFT JOIN users t ON t.id = c.teacher_id
                WHERE {$where} ORDER BY c.sort_order, c.created_at DESC";

        return $this->paginate($sql, $params, $page, $perPage);
    }

    public function listAll(int $page, int $perPage, ?string $status = null): array
    {
        $params = [];
        $where = 'c.deleted_at IS NULL';
        if ($status) {
            $where .= ' AND c.status = ?';
            $params[] = $status;
        }

        $sql = "SELECT c.*, cat.name AS category_name,
                       CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
                       (SELECT GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) ORDER BY u.first_name SEPARATOR ', ')
                          FROM course_teachers ct JOIN users u ON u.id = ct.teacher_id
                          WHERE ct.course_id = c.id AND u.deleted_at IS NULL) AS teacher_names,
                       (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) AS enrolled_count
                FROM courses c
                LEFT JOIN course_categories cat ON cat.id = c.category_id
                LEFT JOIN users t ON t.id = c.teacher_id
                WHERE {$where} ORDER BY c.created_at DESC";

        return $this->paginate($sql, $params, $page, $perPage);
    }

    public function listByTeacher(int $teacherId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, cat.name AS category_name,
                    (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id AND ce.status = 'active') AS enrolled_count
             FROM courses c
             LEFT JOIN course_categories cat ON cat.id = c.category_id
             WHERE c.deleted_at IS NULL
               AND c.status != 'archived'
               AND (c.teacher_id = ? OR EXISTS (
                    SELECT 1 FROM course_teachers ct WHERE ct.course_id = c.id AND ct.teacher_id = ?
               ))
             ORDER BY c.created_at DESC"
        );
        $stmt->execute([$teacherId, $teacherId]);
        return $stmt->fetchAll();
    }

    public function listByStudent(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, ce.progress, ce.status AS enrollment_status, ce.enrolled_at,
                    cat.name AS category_name,
                    CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
             FROM course_enrollments ce
             JOIN courses c ON c.id = ce.course_id
             LEFT JOIN course_categories cat ON cat.id = c.category_id
             LEFT JOIN users t ON t.id = c.teacher_id
             WHERE ce.student_id = ? AND ce.status = 'active' AND c.deleted_at IS NULL
             ORDER BY ce.enrolled_at DESC"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO courses (category_id, teacher_id, title, slug, subtitle, short_description, description,
             thumbnail, banner, duration, start_date, end_date, fee, level, prerequisites, learning_outcomes,
             status, max_students, certificate_available, enrollment_status, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['category_id'] ?? null,
            $data['teacher_id'] ?? null,
            $data['title'],
            $data['slug'],
            $data['subtitle'] ?? null,
            $data['short_description'] ?? null,
            $data['description'] ?? null,
            $data['thumbnail'] ?? null,
            $data['banner'] ?? null,
            $data['duration'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['fee'] ?? 0,
            $data['level'] ?? 'beginner',
            $data['prerequisites'] ?? null,
            $data['learning_outcomes'] ?? null,
            $data['status'] ?? 'draft',
            $data['max_students'] ?? null,
            $data['certificate_available'] ?? 0,
            $data['enrollment_status'] ?? 'open',
            $data['created_by'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare('UPDATE courses SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE courses SET deleted_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function duplicate(int $id, string $newSlug, int $createdBy): int
    {
        $course = $this->findById($id);
        if (!$course) {
            return 0;
        }
        unset($course['id'], $course['category_name'], $course['category_slug'], $course['teacher_name']);
        $course['title'] .= ' (Copy)';
        $course['slug'] = $newSlug;
        $course['status'] = 'draft';
        $course['created_by'] = $createdBy;
        return $this->create($course);
    }

    public function enrollStudent(int $courseId, int $studentId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO course_enrollments (course_id, student_id) VALUES (?, ?)'
        );
        return $stmt->execute([$courseId, $studentId]);
    }

    public function assignTeacher(int $courseId, int $teacherId): void
    {
        $this->setTeachers($courseId, [$teacherId]);
    }

    /**
     * Replace the set of teachers assigned to a course (max 2).
     * The first teacher is kept on courses.teacher_id as the primary teacher.
     *
     * @param int[] $teacherIds
     */
    public function setTeachers(int $courseId, array $teacherIds): void
    {
        $teacherIds = array_values(array_unique(array_filter(array_map('intval', $teacherIds))));
        $teacherIds = array_slice($teacherIds, 0, 2);

        $primary = $teacherIds[0] ?? null;
        $this->db->prepare('UPDATE courses SET teacher_id = ? WHERE id = ?')->execute([$primary, $courseId]);

        $this->db->prepare('DELETE FROM course_teachers WHERE course_id = ?')->execute([$courseId]);
        $insert = $this->db->prepare('INSERT IGNORE INTO course_teachers (course_id, teacher_id) VALUES (?, ?)');
        foreach ($teacherIds as $teacherId) {
            $insert->execute([$courseId, $teacherId]);
        }
    }

    /** @return int[] */
    public function getTeacherIds(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ct.teacher_id FROM course_teachers ct
             JOIN users u ON u.id = ct.teacher_id
             WHERE ct.course_id = ? AND u.deleted_at IS NULL
             ORDER BY ct.assigned_at'
        );
        $stmt->execute([$courseId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** Normalize a GROUP_CONCAT teacher id string into an int array. */
    private function parseTeacherIds(?string $concat): array
    {
        if ($concat === null || $concat === '') {
            return [];
        }
        return array_map('intval', explode(',', $concat));
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM courses WHERE status = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public function countTotal(): int
    {
        return (int) $this->db->query(
            'SELECT COUNT(*) FROM courses WHERE deleted_at IS NULL'
        )->fetchColumn();
    }

    public function isTeacherAssigned(int $courseId, int $teacherId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM courses c
             LEFT JOIN course_teachers ct ON ct.course_id = c.id
             WHERE c.id = ? AND (c.teacher_id = ? OR ct.teacher_id = ?)'
        );
        $stmt->execute([$courseId, $teacherId, $teacherId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function isStudentEnrolled(int $courseId, int $studentId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM course_enrollments WHERE course_id = ? AND student_id = ? AND status = "active"'
        );
        $stmt->execute([$courseId, $studentId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function getCategories(): array
    {
        return $this->db->query(
            'SELECT * FROM course_categories WHERE is_active = 1 ORDER BY sort_order'
        )->fetchAll();
    }

    public function listEnrollments(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.status AS user_status,
                    ce.enrolled_at, ce.status AS enrollment_status, ce.progress
             FROM course_enrollments ce
             JOIN users u ON u.id = ce.student_id
             WHERE ce.course_id = ? AND u.deleted_at IS NULL
             ORDER BY ce.enrolled_at DESC'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function unenrollStudent(int $courseId, int $studentId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM course_enrollments WHERE course_id = ? AND student_id = ?'
        );
        return $stmt->execute([$courseId, $studentId]);
    }

    /** @return int[] All teacher user IDs for a course (primary + assigned). */
    public function getTeacherIdsForNotify(int $courseId): array
    {
        $ids = $this->getTeacherIds($courseId);
        $course = $this->findById($courseId);
        if ($course && !empty($course['teacher_id'])) {
            $ids[] = (int) $course['teacher_id'];
        }
        return array_values(array_unique(array_filter($ids)));
    }

    public function getEnrolledStudents(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.first_name, u.last_name, u.email
             FROM course_enrollments ce
             JOIN users u ON u.id = ce.student_id
             WHERE ce.course_id = ? AND ce.status = "active" AND u.deleted_at IS NULL
             ORDER BY u.last_name, u.first_name'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }
}
