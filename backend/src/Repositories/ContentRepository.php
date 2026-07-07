<?php
namespace App\Repositories;

class ContentRepository extends BaseRepository
{
    public function getCourseStructure(int $courseId): array
    {
        $modules = $this->db->prepare(
            'SELECT * FROM modules WHERE course_id = ? ORDER BY sort_order'
        );
        $modules->execute([$courseId]);
        $result = $modules->fetchAll();

        foreach ($result as &$module) {
            $chapters = $this->db->prepare(
                'SELECT * FROM chapters WHERE module_id = ? ORDER BY sort_order'
            );
            $chapters->execute([$module['id']]);
            $module['chapters'] = $chapters->fetchAll();

            foreach ($module['chapters'] as &$chapter) {
                $lectures = $this->db->prepare(
                    'SELECT * FROM lectures WHERE chapter_id = ? ORDER BY sort_order'
                );
                $lectures->execute([$chapter['id']]);
                $chapter['lectures'] = $lectures->fetchAll();

                foreach ($chapter['lectures'] as &$lecture) {
                    try {
                        $resources = $this->db->prepare(
                            'SELECT r.*, CONCAT(u.first_name, " ", u.last_name) AS uploader_name
                             FROM lecture_resources r
                             LEFT JOIN users u ON u.id = r.uploaded_by
                             WHERE r.lecture_id = ? ORDER BY r.sort_order, r.created_at'
                        );
                        $resources->execute([$lecture['id']]);
                    } catch (\PDOException $e) {
                        // uploaded_by column not migrated yet — fall back gracefully.
                        $resources = $this->db->prepare(
                            'SELECT * FROM lecture_resources WHERE lecture_id = ? ORDER BY sort_order, created_at'
                        );
                        $resources->execute([$lecture['id']]);
                    }
                    $lecture['resources'] = $resources->fetchAll();
                }
            }
        }

        return $result;
    }

    public function createModule(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO modules (course_id, title, description, sort_order, is_published) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $data['course_id'], $data['title'], $data['description'] ?? null,
            $data['sort_order'] ?? 0, $data['is_published'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function createChapter(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO chapters (module_id, title, description, sort_order, is_published) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $data['module_id'], $data['title'], $data['description'] ?? null,
            $data['sort_order'] ?? 0, $data['is_published'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function createLecture(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO lectures (chapter_id, title, description, content_type, duration_minutes, sort_order, is_published, is_free_preview)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['chapter_id'], $data['title'], $data['description'] ?? null,
            $data['content_type'] ?? 'mixed', $data['duration_minutes'] ?? 0,
            $data['sort_order'] ?? 0, $data['is_published'] ?? 0, $data['is_free_preview'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function addLectureResource(array $data): int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO lecture_resources (lecture_id, type, title, file_path, external_url, mime_type, file_size, sort_order, uploaded_by)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $data['lecture_id'], $data['type'], $data['title'],
                $data['file_path'] ?? null, $data['external_url'] ?? null,
                $data['mime_type'] ?? null, $data['size'] ?? null, $data['sort_order'] ?? 0,
                $data['uploaded_by'] ?? null,
            ]);
        } catch (\PDOException $e) {
            // uploaded_by column not migrated yet — insert without it.
            $stmt = $this->db->prepare(
                'INSERT INTO lecture_resources (lecture_id, type, title, file_path, external_url, mime_type, file_size, sort_order)
                 VALUES (?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $data['lecture_id'], $data['type'], $data['title'],
                $data['file_path'] ?? null, $data['external_url'] ?? null,
                $data['mime_type'] ?? null, $data['size'] ?? null, $data['sort_order'] ?? 0,
            ]);
        }
        return (int) $this->db->lastInsertId();
    }

    public function getResource(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM lecture_resources WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateEntity(string $table, int $id, array $data): bool
    {
        $allowed = ['modules', 'chapters', 'lectures', 'lecture_resources'];
        if (!in_array($table, $allowed, true)) {
            return false;
        }
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE {$table} SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public function deleteEntity(string $table, int $id): bool
    {
        $allowed = ['modules', 'chapters', 'lectures', 'lecture_resources'];
        if (!in_array($table, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getLectureCourseId(int $lectureId): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT m.course_id FROM lectures l
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules m ON m.id = ch.module_id
             WHERE l.id = ?'
        );
        $stmt->execute([$lectureId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function getCourseIdFromModule(int $moduleId): ?int
    {
        $stmt = $this->db->prepare('SELECT course_id FROM modules WHERE id = ?');
        $stmt->execute([$moduleId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function getCourseIdFromChapter(int $chapterId): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT m.course_id FROM chapters ch
             JOIN modules m ON m.id = ch.module_id
             WHERE ch.id = ?'
        );
        $stmt->execute([$chapterId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function getLecture(int $lectureId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM lectures WHERE id = ?');
        $stmt->execute([$lectureId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Return the best extractable source file attached to a lecture
     * (PowerPoint / PDF / Word / text), or null if none. Slides & PDFs first.
     */
    public function getLectureSourceFile(int $lectureId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, file_path, mime_type,
                    LOWER(SUBSTRING_INDEX(file_path, '.', -1)) AS ext
             FROM lecture_resources
             WHERE lecture_id = ? AND file_path IS NOT NULL AND file_path <> ''
             ORDER BY FIELD(LOWER(SUBSTRING_INDEX(file_path, '.', -1)), 'pptx','pdf','docx','doc','txt','md') > 0 DESC,
                      FIELD(LOWER(SUBSTRING_INDEX(file_path, '.', -1)), 'pptx','pdf','docx','doc','txt','md') ASC,
                      id ASC"
        );
        $stmt->execute([$lectureId]);
        foreach ($stmt->fetchAll() as $row) {
            if (in_array($row['ext'], ['pptx', 'pdf', 'docx', 'doc', 'txt', 'md'], true)) {
                return $row;
            }
        }
        return null;
    }

    public function getCourseIdFromFilePath(string $path): ?int
    {
        $path = str_replace('\\', '/', $path);
        if (preg_match('#uploads/courses/(\d+)/#', $path, $m)) {
            return (int) $m[1];
        }

        $stmt = $this->db->prepare(
            'SELECT m.course_id FROM lecture_resources lr
             JOIN lectures l ON l.id = lr.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules m ON m.id = ch.module_id
             WHERE lr.file_path = ? LIMIT 1'
        );
        $stmt->execute([$path]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }
}
