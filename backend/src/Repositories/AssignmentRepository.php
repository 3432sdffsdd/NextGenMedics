<?php
namespace App\Repositories;

class AssignmentRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, c.title AS course_title,
                    CONCAT(t.first_name, " ", t.last_name) AS teacher_name
             FROM assignments a
             JOIN courses c ON c.id = a.course_id
             JOIN users t ON t.id = a.teacher_id
             WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*,
                    (SELECT COUNT(*) FROM assignment_submissions s WHERE s.assignment_id = a.id) AS submission_count,
                    (SELECT COUNT(*) FROM assignment_questions q WHERE q.assignment_id = a.id) AS question_count
             FROM assignments a WHERE a.course_id = ? ORDER BY a.due_date DESC'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function listByStudent(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, c.title AS course_title, s.status AS submission_status, s.marks, s.submitted_at
             FROM assignments a
             JOIN courses c ON c.id = a.course_id
             JOIN course_enrollments ce ON ce.course_id = c.id AND ce.student_id = ?
             LEFT JOIN assignment_submissions s ON s.assignment_id = a.id AND s.student_id = ?
             WHERE a.status = 'published' ORDER BY a.due_date ASC"
        );
        $stmt->execute([$studentId, $studentId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        if ($this->columnExists('assignments', 'assignment_type')) {
            $stmt = $this->db->prepare(
                'INSERT INTO assignments (course_id, teacher_id, title, description, instructions, due_date, max_marks, attachment_path, assignment_type, external_url, status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $data['course_id'], $data['teacher_id'], $data['title'],
                $data['description'] ?? null, $data['instructions'] ?? null,
                $data['due_date'], $data['max_marks'] ?? 100,
                $data['attachment_path'] ?? null,
                $data['assignment_type'] ?? 'file',
                $data['external_url'] ?? null,
                $data['status'] ?? 'draft',
            ]);
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO assignments (course_id, teacher_id, title, description, instructions, due_date, max_marks, attachment_path, status)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $data['course_id'], $data['teacher_id'], $data['title'],
                $data['description'] ?? null, $data['instructions'] ?? null,
                $data['due_date'], $data['max_marks'] ?? 100,
                $data['attachment_path'] ?? null,
                $data['status'] ?? 'draft',
            ]);
        }
        return (int) $this->db->lastInsertId();
    }

    public function supportsAttachments(): bool
    {
        return $this->tableExists('assignment_attachments');
    }

    public function hasAssignmentTypeColumn(): bool
    {
        return $this->columnExists('assignments', 'assignment_type');
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
        $stmt = $this->db->prepare('UPDATE assignments SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM assignments WHERE id = ?')->execute([$id]);
    }

    public function submit(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO assignment_submissions (assignment_id, student_id, file_path, original_filename, submission_text, answers_json, marks, percentage, passed, status)
             VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE
             file_path = VALUES(file_path), original_filename = VALUES(original_filename),
             submission_text = VALUES(submission_text), answers_json = VALUES(answers_json),
             marks = VALUES(marks), percentage = VALUES(percentage), passed = VALUES(passed),
             status = VALUES(status), submitted_at = NOW()'
        );
        $answersJson = isset($data['answers_json'])
            ? (is_string($data['answers_json']) ? $data['answers_json'] : json_encode($data['answers_json']))
            : null;
        $stmt->execute([
            $data['assignment_id'], $data['student_id'],
            $data['file_path'] ?? null, $data['original_filename'] ?? null,
            $data['submission_text'] ?? null, $answersJson,
            $data['marks'] ?? null, $data['percentage'] ?? null,
            isset($data['passed']) ? (int) (bool) $data['passed'] : null,
            $data['status'] ?? 'submitted',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteQuestions(int $assignmentId): void
    {
        $stmt = $this->db->prepare('SELECT id FROM assignment_questions WHERE assignment_id = ?');
        $stmt->execute([$assignmentId]);
        $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $this->db->prepare("DELETE FROM assignment_question_options WHERE question_id IN ({$in})")->execute($ids);
        }
        $this->db->prepare('DELETE FROM assignment_questions WHERE assignment_id = ?')->execute([$assignmentId]);
    }

    public function addQuestion(int $assignmentId, array $q, int $sortOrder): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO assignment_questions (assignment_id, question_text, explanation, sort_order) VALUES (?,?,?,?)'
        );
        $stmt->execute([
            $assignmentId,
            $q['question_text'],
            $q['explanation'] ?? null,
            $sortOrder,
        ]);
        $questionId = (int) $this->db->lastInsertId();
        $optStmt = $this->db->prepare(
            'INSERT INTO assignment_question_options (question_id, option_text, sort_order, is_correct) VALUES (?,?,?,?)'
        );
        foreach ($q['options'] as $i => $opt) {
            $optStmt->execute([
                $questionId,
                $opt['option_text'],
                $i + 1,
                !empty($opt['is_correct']) ? 1 : 0,
            ]);
        }
        return $questionId;
    }

    public function getQuestions(int $assignmentId, bool $includeCorrect = false): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM assignment_questions WHERE assignment_id = ? ORDER BY sort_order, id'
        );
        $stmt->execute([$assignmentId]);
        $questions = $stmt->fetchAll();
        $cols = $includeCorrect
            ? 'id, option_text, sort_order, is_correct'
            : 'id, option_text, sort_order';
        foreach ($questions as &$q) {
            $opts = $this->db->prepare("SELECT {$cols} FROM assignment_question_options WHERE question_id = ? ORDER BY sort_order, id");
            $opts->execute([$q['id']]);
            $q['options'] = $opts->fetchAll();
        }
        return $questions;
    }

    public function getQuestionCount(int $assignmentId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM assignment_questions WHERE assignment_id = ?');
        $stmt->execute([$assignmentId]);
        return (int) $stmt->fetchColumn();
    }

    public function getSubmission(int $assignmentId, int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, CONCAT(u.first_name, " ", u.last_name) AS student_name
             FROM assignment_submissions s JOIN users u ON u.id = s.student_id
             WHERE s.assignment_id = ? AND s.student_id = ?'
        );
        $stmt->execute([$assignmentId, $studentId]);
        return $stmt->fetch() ?: null;
    }

    public function listSubmissions(int $assignmentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.*, CONCAT(u.first_name, " ", u.last_name) AS student_name, u.email AS student_email
             FROM assignment_submissions s JOIN users u ON u.id = s.student_id
             WHERE s.assignment_id = ? ORDER BY s.submitted_at DESC'
        );
        $stmt->execute([$assignmentId]);
        return $stmt->fetchAll();
    }

    public function gradeSubmission(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE assignment_submissions SET marks = ?, remarks = ?, status = ?, graded_at = NOW(), graded_by = ? WHERE id = ?'
        );
        return $stmt->execute([
            $data['marks'], $data['remarks'] ?? null,
            $data['status'] ?? 'graded', $data['graded_by'], $id,
        ]);
    }

    public function getSubmissionById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM assignment_submissions WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function deleteSubmission(int $id): bool
    {
        return $this->db->prepare('DELETE FROM assignment_submissions WHERE id = ?')->execute([$id]);
    }

    public function listAttachments(int $assignmentId): array
    {
        if (!$this->supportsAttachments()) {
            return [];
        }
        $stmt = $this->db->prepare(
            'SELECT * FROM assignment_attachments WHERE assignment_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$assignmentId]);
        return $stmt->fetchAll();
    }

    public function addAttachment(int $assignmentId, array $data): int
    {
        if (!$this->supportsAttachments()) {
            throw new \RuntimeException(
                'Multi-file attachments require database migration 009. Run backend/database/live-update-all.sql on the server.'
            );
        }
        $stmt = $this->db->prepare(
            'INSERT INTO assignment_attachments (assignment_id, title, file_path, original_filename, mime_type, file_size, sort_order)
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $assignmentId,
            $data['title'],
            $data['file_path'],
            $data['original_filename'] ?? null,
            $data['mime_type'] ?? null,
            $data['file_size'] ?? null,
            $data['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findAttachment(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM assignment_attachments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function deleteAttachment(int $id): bool
    {
        return $this->db->prepare('DELETE FROM assignment_attachments WHERE id = ?')->execute([$id]);
    }

    public function listSubmissionFiles(int $submissionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM assignment_submission_files WHERE submission_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$submissionId]);
        return $stmt->fetchAll();
    }

    public function deleteSubmissionFiles(int $submissionId): void
    {
        $this->db->prepare('DELETE FROM assignment_submission_files WHERE submission_id = ?')->execute([$submissionId]);
    }

    public function addSubmissionFile(int $submissionId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO assignment_submission_files (submission_id, title, file_path, original_filename, mime_type, file_size, sort_order)
             VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $submissionId,
            $data['title'],
            $data['file_path'],
            $data['original_filename'] ?? null,
            $data['mime_type'] ?? null,
            $data['file_size'] ?? null,
            $data['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getSubmissionId(int $assignmentId, int $studentId): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?'
        );
        $stmt->execute([$assignmentId, $studentId]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : null;
    }

    public function countPending(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM assignment_submissions WHERE status = 'submitted'"
        )->fetchColumn();
    }
}
