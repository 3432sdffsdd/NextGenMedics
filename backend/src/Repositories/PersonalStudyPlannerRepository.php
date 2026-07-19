<?php
namespace App\Repositories;

class PersonalStudyPlannerRepository extends BaseRepository
{
    public function getSettings(int $studentId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM psp_settings WHERE student_id = ? LIMIT 1');
        $stmt->execute([$studentId]);
        $row = $stmt->fetch();
        if ($row && is_string($row['preferred_days'] ?? null)) {
            $row['preferred_days'] = json_decode($row['preferred_days'], true) ?: [];
        }
        return $row ?: null;
    }

    public function saveSettings(int $studentId, array $data): array
    {
        $days = json_encode(array_values($data['preferred_days'] ?? [
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday',
        ]));
        $stmt = $this->db->prepare(
            'INSERT INTO psp_settings (student_id, exam_date, hours_per_day, preferred_days, setup_completed)
             VALUES (?,?,?,?,1)
             ON DUPLICATE KEY UPDATE exam_date = VALUES(exam_date), hours_per_day = VALUES(hours_per_day),
             preferred_days = VALUES(preferred_days), setup_completed = 1, updated_at = NOW()'
        );
        $stmt->execute([
            $studentId,
            $data['exam_date'] ?: null,
            (float) ($data['hours_per_day'] ?? 3),
            $days,
        ]);
        return $this->getSettings($studentId);
    }

    public function createPlan(int $studentId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO psp_plans (student_id, plan_name, plan_mode, duration_days, start_date, end_date, status, notes)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $studentId,
            $data['plan_name'],
            $data['plan_mode'],
            (int) $data['duration_days'],
            $data['start_date'],
            $data['end_date'],
            $data['status'] ?? 'active',
            $data['notes'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updatePlan(int $planId, array $fields): void
    {
        $allowed = ['plan_name', 'status', 'completion_pct', 'notes'];
        $sets = [];
        $vals = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $fields)) {
                $sets[] = "{$k} = ?";
                $vals[] = $fields[$k];
            }
        }
        if (!$sets) {
            return;
        }
        $vals[] = $planId;
        $this->db->prepare('UPDATE psp_plans SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($vals);
    }

    public function getPlan(int $planId, int $studentId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM psp_plans WHERE id = ? AND student_id = ? LIMIT 1');
        $stmt->execute([$planId, $studentId]);
        return $stmt->fetch() ?: null;
    }

    public function getActivePlan(int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM psp_plans WHERE student_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: null;
    }

    public function listPlans(int $studentId, ?string $status = null): array
    {
        if ($status) {
            $stmt = $this->db->prepare(
                'SELECT * FROM psp_plans WHERE student_id = ? AND status = ? ORDER BY id DESC'
            );
            $stmt->execute([$studentId, $status]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT * FROM psp_plans WHERE student_id = ? AND status <> 'draft' ORDER BY FIELD(status,'active','completed','archived'), id DESC"
            );
            $stmt->execute([$studentId]);
        }
        return $stmt->fetchAll();
    }

    public function countPlans(int $studentId, ?string $status = null): int
    {
        if ($status) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM psp_plans WHERE student_id = ? AND status = ?');
            $stmt->execute([$studentId, $status]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM psp_plans WHERE student_id = ? AND status <> 'draft'");
            $stmt->execute([$studentId]);
        }
        return (int) $stmt->fetchColumn();
    }

    public function insertDay(int $planId, int $dayNumber, string $date): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO psp_plan_days (plan_id, day_number, plan_date) VALUES (?,?,?)'
        );
        $stmt->execute([$planId, $dayNumber, $date]);
        return (int) $this->db->lastInsertId();
    }

    public function getDay(int $planId, string $date): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM psp_plan_days WHERE plan_id = ? AND plan_date = ? LIMIT 1');
        $stmt->execute([$planId, $date]);
        return $stmt->fetch() ?: null;
    }

    public function getDayByNumber(int $planId, int $dayNumber): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM psp_plan_days WHERE plan_id = ? AND day_number = ? LIMIT 1');
        $stmt->execute([$planId, $dayNumber]);
        return $stmt->fetch() ?: null;
    }

    public function daysForPlan(int $planId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM psp_plan_days WHERE plan_id = ? ORDER BY day_number ASC');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function daysBetween(int $planId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM psp_plan_days WHERE plan_id = ? AND plan_date BETWEEN ? AND ? ORDER BY plan_date ASC'
        );
        $stmt->execute([$planId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function ensureDay(int $planId, string $date, ?int $dayNumber = null): int
    {
        $existing = $this->getDay($planId, $date);
        if ($existing) {
            return (int) $existing['id'];
        }
        if ($dayNumber === null) {
            $stmt = $this->db->prepare('SELECT COALESCE(MAX(day_number),0)+1 FROM psp_plan_days WHERE plan_id = ?');
            $stmt->execute([$planId]);
            $dayNumber = (int) $stmt->fetchColumn();
        }
        return $this->insertDay($planId, $dayNumber, $date);
    }

    public function insertTask(int $planId, int $dayId, array $task): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO psp_plan_tasks
             (plan_id, day_id, plan_date, day_number, source, task_type, ref_id, course_id, lecture_id,
              subject_title, title, description, target_count, estimated_minutes, status, sort_order)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $planId,
            $dayId,
            $task['plan_date'],
            (int) ($task['day_number'] ?? 1),
            $task['source'] ?? 'lms',
            $task['task_type'],
            $task['ref_id'] ?? null,
            $task['course_id'] ?? null,
            $task['lecture_id'] ?? null,
            $task['subject_title'] ?? null,
            $task['title'],
            $task['description'] ?? null,
            $task['target_count'] ?? null,
            (int) ($task['estimated_minutes'] ?? 30),
            $task['status'] ?? 'pending',
            (int) ($task['sort_order'] ?? 0),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getTask(int $taskId, int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.* FROM psp_plan_tasks t
             JOIN psp_plans p ON p.id = t.plan_id
             WHERE t.id = ? AND p.student_id = ? LIMIT 1'
        );
        $stmt->execute([$taskId, $studentId]);
        return $stmt->fetch() ?: null;
    }

    public function tasksForDate(int $planId, string $date): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM psp_plan_tasks WHERE plan_id = ? AND plan_date = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$planId, $date]);
        return $stmt->fetchAll();
    }

    public function allTasks(int $planId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM psp_plan_tasks WHERE plan_id = ? ORDER BY plan_date ASC, sort_order ASC, id ASC'
        );
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function tasksBetween(int $planId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM psp_plan_tasks WHERE plan_id = ? AND plan_date BETWEEN ? AND ? ORDER BY plan_date, sort_order'
        );
        $stmt->execute([$planId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function setTaskStatus(int $taskId, string $status): void
    {
        $this->db->prepare(
            'UPDATE psp_plan_tasks SET status = ?, completed_at = IF(? = "completed", NOW(), NULL) WHERE id = ?'
        )->execute([$status, $status, $taskId]);
    }

    public function moveTask(int $taskId, int $dayId, string $date, int $dayNumber): void
    {
        $this->db->prepare(
            'UPDATE psp_plan_tasks SET day_id = ?, plan_date = ?, day_number = ?, status = "pending", completed_at = NULL WHERE id = ?'
        )->execute([$dayId, $date, $dayNumber, $taskId]);
    }

    public function refreshDay(int $dayId): void
    {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'completed') AS done,
                SUM(status = 'pending') AS pending
             FROM psp_plan_tasks WHERE day_id = ?"
        );
        $stmt->execute([$dayId]);
        $r = $stmt->fetch() ?: ['total' => 0, 'done' => 0, 'pending' => 0];
        $total = (int) $r['total'];
        $done = (int) $r['done'];
        $pct = $total > 0 ? round(($done / $total) * 100, 2) : 0;
        $status = 'upcoming';
        if ($total > 0 && $done === $total) {
            $status = 'completed';
        } elseif ($done > 0) {
            $status = 'partial';
        } elseif ((int) $r['pending'] > 0) {
            $status = 'in_progress';
        }
        $this->db->prepare('UPDATE psp_plan_days SET completed_pct = ?, day_status = ? WHERE id = ?')
            ->execute([$pct, $status, $dayId]);
    }

    public function countStatus(int $planId, string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM psp_plan_tasks WHERE plan_id = ? AND status = ?');
        $stmt->execute([$planId, $status]);
        return (int) $stmt->fetchColumn();
    }

    public function countTypeStatus(int $planId, string $type, string $status): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM psp_plan_tasks WHERE plan_id = ? AND task_type = ? AND status = ?'
        );
        $stmt->execute([$planId, $type, $status]);
        return (int) $stmt->fetchColumn();
    }

    public function countType(int $planId, string $type): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM psp_plan_tasks WHERE plan_id = ? AND task_type = ?');
        $stmt->execute([$planId, $type]);
        return (int) $stmt->fetchColumn();
    }

    public function upsertProgress(int $planId, int $studentId, array $p): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO psp_plan_progress
             (plan_id, student_id, videos_completed, videos_total, quizzes_completed, quizzes_total,
              flashcards_completed, flashcards_total, notes_completed, notes_total,
              revision_completed, revision_total, manual_completed, manual_total, overall_pct, streak_days)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
              videos_completed=VALUES(videos_completed), videos_total=VALUES(videos_total),
              quizzes_completed=VALUES(quizzes_completed), quizzes_total=VALUES(quizzes_total),
              flashcards_completed=VALUES(flashcards_completed), flashcards_total=VALUES(flashcards_total),
              notes_completed=VALUES(notes_completed), notes_total=VALUES(notes_total),
              revision_completed=VALUES(revision_completed), revision_total=VALUES(revision_total),
              manual_completed=VALUES(manual_completed), manual_total=VALUES(manual_total),
              overall_pct=VALUES(overall_pct), streak_days=VALUES(streak_days)'
        );
        $stmt->execute([
            $planId, $studentId,
            $p['videos_completed'], $p['videos_total'],
            $p['quizzes_completed'], $p['quizzes_total'],
            $p['flashcards_completed'], $p['flashcards_total'],
            $p['notes_completed'], $p['notes_total'],
            $p['revision_completed'], $p['revision_total'],
            $p['manual_completed'], $p['manual_total'],
            $p['overall_pct'], $p['streak_days'],
        ]);
    }

    public function getProgress(int $planId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM psp_plan_progress WHERE plan_id = ? LIMIT 1');
        $stmt->execute([$planId]);
        return $stmt->fetch() ?: null;
    }

    public function upsertStat(int $studentId, string $date, array $s): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO psp_statistics
             (student_id, stat_date, tasks_completed, tasks_pending, tasks_skipped, study_minutes,
              videos_watched, quizzes_attempted, flashcards_reviewed, manual_completed)
             VALUES (?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
              tasks_completed=VALUES(tasks_completed), tasks_pending=VALUES(tasks_pending),
              tasks_skipped=VALUES(tasks_skipped), study_minutes=VALUES(study_minutes),
              videos_watched=VALUES(videos_watched), quizzes_attempted=VALUES(quizzes_attempted),
              flashcards_reviewed=VALUES(flashcards_reviewed), manual_completed=VALUES(manual_completed)'
        );
        $stmt->execute([
            $studentId, $date,
            $s['tasks_completed'], $s['tasks_pending'], $s['tasks_skipped'], $s['study_minutes'],
            $s['videos_watched'], $s['quizzes_attempted'], $s['flashcards_reviewed'], $s['manual_completed'],
        ]);
    }

    public function statsBetween(int $studentId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM psp_statistics WHERE student_id = ? AND stat_date BETWEEN ? AND ? ORDER BY stat_date'
        );
        $stmt->execute([$studentId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function subjectProgress(int $planId): array
    {
        $stmt = $this->db->prepare(
            "SELECT subject_title,
                SUM(task_type IN ('lecture','video')) AS total_lectures,
                SUM(task_type IN ('lecture','video') AND status='completed') AS completed_lectures,
                SUM(task_type='quiz') AS total_quizzes,
                SUM(task_type='quiz' AND status='completed') AS completed_quizzes,
                SUM(task_type='flashcard' AND status='completed') AS completed_flashcards,
                SUM(task_type='flashcard') AS total_flashcards,
                SUM(task_type='note' AND status='completed') AS completed_notes,
                SUM(task_type='note') AS total_notes,
                SUM(task_type='revision' AND status='completed') AS completed_revision,
                SUM(task_type='revision') AS total_revision,
                SUM(status='completed') AS completed_all,
                COUNT(*) AS total_all
             FROM psp_plan_tasks
             WHERE plan_id = ? AND subject_title IS NOT NULL AND subject_title <> ''
             GROUP BY subject_title
             ORDER BY subject_title"
        );
        $stmt->execute([$planId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $total = (int) $r['total_all'];
            $done = (int) $r['completed_all'];
            $r['completion_pct'] = $total > 0 ? round(($done / $total) * 100, 1) : 0;
            $r['remaining_lectures'] = max(0, (int) $r['total_lectures'] - (int) $r['completed_lectures']);
            $r['remaining_quizzes'] = max(0, (int) $r['total_quizzes'] - (int) $r['completed_quizzes']);
        }
        return $rows;
    }

    public function lastPlanDate(int $planId): ?string
    {
        $stmt = $this->db->prepare('SELECT MAX(plan_date) FROM psp_plan_days WHERE plan_id = ?');
        $stmt->execute([$planId]);
        $d = $stmt->fetchColumn();
        return $d ?: null;
    }

    public function deletePlan(int $planId, int $studentId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM psp_plans WHERE id = ? AND student_id = ?');
        return $stmt->execute([$planId, $studentId]);
    }

    public function archiveOtherActive(int $studentId, int $keepPlanId): void
    {
        $this->db->prepare(
            "UPDATE psp_plans SET status = 'archived' WHERE student_id = ? AND status = 'active' AND id <> ?"
        )->execute([$studentId, $keepPlanId]);
    }
}
