<?php
namespace App\Repositories;

class FcpsStudyPlannerRepository extends BaseRepository
{
    public function getActivePlan(int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM fcps_study_plans WHERE student_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$studentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getPlanById(int $planId, int $studentId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM fcps_study_plans WHERE id = ? AND student_id = ? LIMIT 1');
        $stmt->execute([$planId, $studentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function archiveActive(int $studentId): void
    {
        $this->db->prepare("UPDATE fcps_study_plans SET status = 'archived' WHERE student_id = ? AND status = 'active'")
            ->execute([$studentId]);
    }

    public function createPlan(int $studentId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO fcps_study_plans (
                student_id, exam_date, start_date, hours_per_day, preferred_days, sessions_per_day,
                preferred_time, subjects_completed, subjects_remaining, subjects_weak, subjects_strong,
                subject_order, daily_mcq_target, daily_flashcard_target, revision_preference,
                weekly_goals, monthly_goals, strategy_notes, status
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,\'active\')'
        );
        $stmt->execute([
            $studentId,
            $data['exam_date'],
            $data['start_date'],
            $data['hours_per_day'],
            json_encode($data['preferred_days'] ?? []),
            $data['sessions_per_day'],
            $data['preferred_time'],
            json_encode($data['subjects_completed'] ?? []),
            json_encode($data['subjects_remaining'] ?? []),
            json_encode($data['subjects_weak'] ?? []),
            json_encode($data['subjects_strong'] ?? []),
            json_encode($data['subject_order'] ?? []),
            $data['daily_mcq_target'],
            $data['daily_flashcard_target'],
            $data['revision_preference'],
            json_encode($data['weekly_goals'] ?? []),
            json_encode($data['monthly_goals'] ?? []),
            $data['strategy_notes'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function insertDay(int $planId, array $day): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO fcps_study_plan_days (
                plan_id, plan_date, is_study_day, day_status, topics, mcq_target, flashcard_target,
                revision_subject, weekly_goal, completed_pct, notes
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $planId,
            $day['plan_date'],
            $day['is_study_day'] ?? 1,
            $day['day_status'] ?? 'upcoming',
            json_encode($day['topics'] ?? []),
            $day['mcq_target'] ?? 0,
            $day['flashcard_target'] ?? 0,
            $day['revision_subject'] ?? null,
            $day['weekly_goal'] ?? null,
            $day['completed_pct'] ?? 0,
            $day['notes'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function insertTask(int $planId, int $dayId, array $task): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO fcps_study_tasks (
                plan_id, day_id, plan_date, task_type, subject, title, session_number,
                target_count, status, sort_order
             ) VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $planId, $dayId, $task['plan_date'], $task['task_type'], $task['subject'] ?? null,
            $task['title'], $task['session_number'] ?? 1, $task['target_count'] ?? null,
            $task['status'] ?? 'pending', $task['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function insertSession(int $planId, int $dayId, array $session): void
    {
        $this->db->prepare(
            'INSERT INTO fcps_study_sessions (
                plan_id, day_id, session_number, time_label, subject, focus, duration_minutes, status
             ) VALUES (?,?,?,?,?,?,?,?)'
        )->execute([
            $planId, $dayId, $session['session_number'] ?? 1, $session['time_label'] ?? 'Session',
            $session['subject'] ?? null, $session['focus'] ?? null,
            $session['duration_minutes'] ?? 60, $session['status'] ?? 'pending',
        ]);
    }

    public function getDay(int $planId, string $date): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM fcps_study_plan_days WHERE plan_id = ? AND plan_date = ? LIMIT 1');
        $stmt->execute([$planId, $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function daysBetween(int $planId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM fcps_study_plan_days WHERE plan_id = ? AND plan_date BETWEEN ? AND ? ORDER BY plan_date ASC'
        );
        $stmt->execute([$planId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function allDays(int $planId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM fcps_study_plan_days WHERE plan_id = ? ORDER BY plan_date ASC');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function tasksForDate(int $planId, string $date): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM fcps_study_tasks WHERE plan_id = ? AND plan_date = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$planId, $date]);
        return $stmt->fetchAll();
    }

    public function tasksBetween(int $planId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM fcps_study_tasks WHERE plan_id = ? AND plan_date BETWEEN ? AND ? ORDER BY plan_date ASC, sort_order ASC'
        );
        $stmt->execute([$planId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function allTasks(int $planId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM fcps_study_tasks WHERE plan_id = ? ORDER BY plan_date ASC, sort_order ASC');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function searchTasks(int $planId, string $q, ?string $status = null, ?string $type = null): array
    {
        $sql = 'SELECT * FROM fcps_study_tasks WHERE plan_id = ? AND title LIKE ?';
        $params = [$planId, '%' . $q . '%'];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        if ($type) {
            $sql .= ' AND task_type = ?';
            $params[] = $type;
        }
        $sql .= ' ORDER BY plan_date ASC, sort_order ASC LIMIT 100';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTask(int $taskId, int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.* FROM fcps_study_tasks t
             JOIN fcps_study_plans p ON p.id = t.plan_id
             WHERE t.id = ? AND p.student_id = ? LIMIT 1'
        );
        $stmt->execute([$taskId, $studentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function setTaskStatus(int $taskId, string $status): void
    {
        $this->db->prepare(
            "UPDATE fcps_study_tasks SET status = ?, completed_at = IF(? = 'completed', NOW(), NULL) WHERE id = ?"
        )->execute([$status, $status, $taskId]);
    }

    public function resetDayTasks(int $planId, string $date): void
    {
        $this->db->prepare(
            "UPDATE fcps_study_tasks SET status = 'pending', completed_at = NULL
             WHERE plan_id = ? AND plan_date = ?"
        )->execute([$planId, $date]);
        $day = $this->getDay($planId, $date);
        if ($day) {
            $this->refreshDayCompletion((int) $day['id']);
        }
    }

    public function refreshDayCompletion(int $dayId): void
    {
        $stmt = $this->db->prepare('SELECT status FROM fcps_study_tasks WHERE day_id = ?');
        $stmt->execute([$dayId]);
        $rows = $stmt->fetchAll();
        $n = count($rows);
        $done = count(array_filter($rows, static fn($r) => $r['status'] === 'completed'));
        $skipped = count(array_filter($rows, static fn($r) => $r['status'] === 'skipped'));
        $pct = $n ? round(($done / $n) * 100, 1) : 0;
        $status = 'upcoming';
        if ($n && $done + $skipped === $n && $done > 0) {
            $status = 'completed';
        } elseif ($done > 0) {
            $status = 'partial';
        }
        $this->db->prepare('UPDATE fcps_study_plan_days SET completed_pct = ?, day_status = ? WHERE id = ?')
            ->execute([$pct, $status, $dayId]);
    }

    public function markMissedBefore(int $planId, string $beforeDate): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM fcps_study_tasks WHERE plan_id = ? AND plan_date < ? AND status = 'pending'"
        );
        $stmt->execute([$planId, $beforeDate]);
        $tasks = $stmt->fetchAll();
        if ($tasks) {
            $ids = array_column($tasks, 'id');
            $in = implode(',', array_fill(0, count($ids), '?'));
            $this->db->prepare("UPDATE fcps_study_tasks SET status = 'missed' WHERE id IN ({$in})")->execute($ids);
            $this->db->prepare(
                "UPDATE fcps_study_plan_days SET day_status = 'missed'
                 WHERE plan_id = ? AND plan_date < ? AND day_status IN ('upcoming','partial')"
            )->execute([$planId, $beforeDate]);
        }
        return $tasks;
    }

    public function moveTaskToDate(int $taskId, int $newDayId, string $newDate): void
    {
        $this->db->prepare(
            "UPDATE fcps_study_tasks SET day_id = ?, plan_date = ?, status = 'pending', completed_at = NULL WHERE id = ?"
        )->execute([$newDayId, $newDate, $taskId]);
    }

    public function ensureDay(int $planId, string $date): int
    {
        $day = $this->getDay($planId, $date);
        if ($day) {
            return (int) $day['id'];
        }
        return $this->insertDay($planId, [
            'plan_date' => $date,
            'is_study_day' => 1,
            'day_status' => 'upcoming',
            'topics' => [],
            'mcq_target' => 0,
            'flashcard_target' => 0,
        ]);
    }

    public function upcomingStudyDates(int $planId, string $fromDate, int $limit = 40): array
    {
        $stmt = $this->db->prepare(
            "SELECT plan_date FROM fcps_study_plan_days
             WHERE plan_id = ? AND plan_date >= ? AND is_study_day = 1
             ORDER BY plan_date ASC LIMIT ?"
        );
        $stmt->bindValue(1, $planId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $fromDate);
        $stmt->bindValue(3, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return array_column($stmt->fetchAll(), 'plan_date');
    }

    public function countByStatus(int $planId, string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM fcps_study_tasks WHERE plan_id = ? AND status = ?');
        $stmt->execute([$planId, $status]);
        return (int) $stmt->fetchColumn();
    }

    public function countByTypeStatus(int $planId, string $type, string $status): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM fcps_study_tasks WHERE plan_id = ? AND task_type = ? AND status = ?'
        );
        $stmt->execute([$planId, $type, $status]);
        return (int) $stmt->fetchColumn();
    }

    public function sumTargetCompleted(int $planId, string $type): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(target_count),0) FROM fcps_study_tasks
             WHERE plan_id = ? AND task_type = ? AND status = 'completed'"
        );
        $stmt->execute([$planId, $type]);
        return (int) $stmt->fetchColumn();
    }

    public function subjectProgress(int $planId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM fcps_study_progress WHERE plan_id = ? ORDER BY completion_pct ASC, subject ASC');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function upsertSubjectProgress(int $planId, int $studentId, string $subject, int $total, int $done): void
    {
        $pct = $total > 0 ? round(($done / $total) * 100, 1) : 0;
        $this->db->prepare(
            'INSERT INTO fcps_study_progress (plan_id, student_id, subject, total_tasks, completed_tasks, completion_pct, last_studied_at)
             VALUES (?,?,?,?,?,?, IF(? > 0, NOW(), NULL))
             ON DUPLICATE KEY UPDATE total_tasks = VALUES(total_tasks), completed_tasks = VALUES(completed_tasks),
               completion_pct = VALUES(completion_pct),
               last_studied_at = IF(VALUES(completed_tasks) > completed_tasks, NOW(), last_studied_at)'
        )->execute([$planId, $studentId, $subject, $total, $done, $pct, $done]);
    }

    public function rebuildSubjectProgress(int $planId, int $studentId): void
    {
        $stmt = $this->db->prepare(
            "SELECT subject, COUNT(*) AS total_tasks,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks
             FROM fcps_study_tasks
             WHERE plan_id = ? AND subject IS NOT NULL AND subject != ''
             GROUP BY subject"
        );
        $stmt->execute([$planId]);
        foreach ($stmt->fetchAll() as $row) {
            $this->upsertSubjectProgress($planId, $studentId, $row['subject'], (int) $row['total_tasks'], (int) $row['completed_tasks']);
        }
    }

    public function updatePlanStats(int $planId, float $pct, int $streak): void
    {
        $this->db->prepare('UPDATE fcps_study_plans SET completion_pct = ?, streak_days = ? WHERE id = ?')
            ->execute([$pct, $streak, $planId]);
    }

    public function updateExamDate(int $planId, ?string $examDate): void
    {
        $this->db->prepare('UPDATE fcps_study_plans SET exam_date = ? WHERE id = ?')
            ->execute([$examDate, $planId]);
    }

    public function updateDay(int $dayId, int $planId, array $fields): void
    {
        $allowed = [
            'topics', 'mcq_target', 'flashcard_target', 'revision_subject',
            'notes', 'is_study_day', 'weekly_goal', 'day_status',
        ];
        $sets = [];
        $vals = [];
        foreach ($allowed as $key) {
            if (!array_key_exists($key, $fields)) {
                continue;
            }
            $sets[] = "{$key} = ?";
            $val = $fields[$key];
            if ($key === 'topics') {
                $val = json_encode(is_array($val) ? array_values($val) : []);
            } elseif ($key === 'is_study_day') {
                $val = $val ? 1 : 0;
            }
            $vals[] = $val;
        }
        if (!$sets) {
            return;
        }
        $vals[] = $dayId;
        $vals[] = $planId;
        $this->db->prepare(
            'UPDATE fcps_study_plan_days SET ' . implode(', ', $sets) . ' WHERE id = ? AND plan_id = ?'
        )->execute($vals);
    }

    public function updateTaskTargetsForDay(int $dayId, string $taskType, int $targetCount): void
    {
        $this->db->prepare(
            'UPDATE fcps_study_tasks SET target_count = ? WHERE day_id = ? AND task_type = ?'
        )->execute([$targetCount, $dayId, $taskType]);
    }

    public function upsertDailyStat(int $planId, int $studentId, string $date, array $stats): void
    {
        $this->db->prepare(
            'INSERT INTO fcps_study_statistics
                (plan_id, student_id, stat_date, tasks_total, tasks_completed, mcqs_done, flashcards_done, study_minutes)
             VALUES (?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                tasks_total = VALUES(tasks_total), tasks_completed = VALUES(tasks_completed),
                mcqs_done = VALUES(mcqs_done), flashcards_done = VALUES(flashcards_done),
                study_minutes = VALUES(study_minutes)'
        )->execute([
            $planId, $studentId, $date,
            $stats['tasks_total'] ?? 0, $stats['tasks_completed'] ?? 0,
            $stats['mcqs_done'] ?? 0, $stats['flashcards_done'] ?? 0, $stats['study_minutes'] ?? 0,
        ]);
    }

    public function addHistory(?int $planId, int $studentId, string $type, string $message, ?array $meta = null): void
    {
        $this->db->prepare(
            'INSERT INTO fcps_study_history (plan_id, student_id, event_type, message, meta) VALUES (?,?,?,?,?)'
        )->execute([$planId, $studentId, $type, $message, $meta ? json_encode($meta) : null]);
    }

    public function recentHistory(int $studentId, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM fcps_study_history WHERE student_id = ? ORDER BY id DESC LIMIT ?'
        );
        $stmt->bindValue(1, $studentId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function awardBadge(int $studentId, ?int $planId, string $key, string $title, string $desc): bool
    {
        try {
            $this->db->prepare(
                'INSERT INTO fcps_study_badges (student_id, plan_id, badge_key, title, description) VALUES (?,?,?,?,?)'
            )->execute([$studentId, $planId, $key, $title, $desc]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function badges(int $studentId, ?int $planId = null): array
    {
        if ($planId) {
            $stmt = $this->db->prepare('SELECT * FROM fcps_study_badges WHERE student_id = ? AND plan_id = ? ORDER BY earned_at DESC');
            $stmt->execute([$studentId, $planId]);
        } else {
            $stmt = $this->db->prepare('SELECT * FROM fcps_study_badges WHERE student_id = ? ORDER BY earned_at DESC');
            $stmt->execute([$studentId]);
        }
        return $stmt->fetchAll();
    }

    public function sessionsForDay(int $dayId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM fcps_study_sessions WHERE day_id = ? ORDER BY session_number ASC');
        $stmt->execute([$dayId]);
        return $stmt->fetchAll();
    }

    public function deletePlan(int $planId, int $studentId): void
    {
        if ($this->getPlanById($planId, $studentId)) {
            $this->db->prepare('DELETE FROM fcps_study_plans WHERE id = ?')->execute([$planId]);
        }
    }

    public function setDayStatus(int $dayId, string $status): void
    {
        $this->db->prepare('UPDATE fcps_study_plan_days SET day_status = ? WHERE id = ?')->execute([$status, $dayId]);
    }
}
