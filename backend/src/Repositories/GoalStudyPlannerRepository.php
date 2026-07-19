<?php
namespace App\Repositories;

class GoalStudyPlannerRepository extends BaseRepository
{
    public function getActivePlan(int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM gb_study_plans WHERE student_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$studentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getPlanById(int $planId, int $studentId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM gb_study_plans WHERE id = ? AND student_id = ? LIMIT 1');
        $stmt->execute([$planId, $studentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function archiveActive(int $studentId): void
    {
        $this->db->prepare("UPDATE gb_study_plans SET status = 'archived' WHERE student_id = ? AND status = 'active'")
            ->execute([$studentId]);
    }

    public function createPlan(int $studentId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO gb_study_plans (
                student_id, goal_type, goal_title, start_date, target_date, exam_date,
                hours_per_day, preferred_days, sessions_per_day, preferred_time,
                daily_mcq_target, daily_flashcard_target, revision_preference, status
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,\'active\')'
        );
        $stmt->execute([
            $studentId,
            $data['goal_type'],
            $data['goal_title'],
            $data['start_date'],
            $data['target_date'],
            $data['exam_date'],
            $data['hours_per_day'],
            json_encode($data['preferred_days']),
            $data['sessions_per_day'],
            $data['preferred_time'],
            $data['daily_mcq_target'],
            $data['daily_flashcard_target'],
            $data['revision_preference'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function insertItem(int $planId, array $item, int $sort): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO gb_study_plan_items (
                plan_id, item_type, ref_id, course_id, lecture_id, subject_title, title, estimated_minutes, sort_order
             ) VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $planId, $item['item_type'], $item['ref_id'], $item['course_id'], $item['lecture_id'],
            $item['subject_title'], $item['title'], $item['estimated_minutes'], $sort,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function insertDay(int $planId, array $day): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO gb_study_days (plan_id, plan_date, is_study_day, day_status, completed_pct) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $planId, $day['plan_date'], $day['is_study_day'] ?? 1, $day['day_status'] ?? 'upcoming', $day['completed_pct'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function insertTask(int $planId, int $dayId, ?int $itemId, array $task): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO gb_study_tasks (
                plan_id, day_id, plan_item_id, plan_date, task_type, ref_id, course_id, lecture_id,
                subject_title, title, target_count, status, sort_order
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $planId, $dayId, $itemId, $task['plan_date'], $task['task_type'], $task['ref_id'],
            $task['course_id'], $task['lecture_id'], $task['subject_title'], $task['title'],
            $task['target_count'], $task['status'] ?? 'pending', $task['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function listItems(int $planId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM gb_study_plan_items WHERE plan_id = ? ORDER BY sort_order, id');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function getDay(int $planId, string $date): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM gb_study_days WHERE plan_id = ? AND plan_date = ? LIMIT 1');
        $stmt->execute([$planId, $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function daysBetween(int $planId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM gb_study_days WHERE plan_id = ? AND plan_date BETWEEN ? AND ? ORDER BY plan_date'
        );
        $stmt->execute([$planId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function allDays(int $planId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM gb_study_days WHERE plan_id = ? ORDER BY plan_date');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function tasksForDate(int $planId, string $date): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM gb_study_tasks WHERE plan_id = ? AND plan_date = ? ORDER BY sort_order, id'
        );
        $stmt->execute([$planId, $date]);
        return $stmt->fetchAll();
    }

    public function tasksBetween(int $planId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM gb_study_tasks WHERE plan_id = ? AND plan_date BETWEEN ? AND ? ORDER BY plan_date, sort_order'
        );
        $stmt->execute([$planId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function allTasks(int $planId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM gb_study_tasks WHERE plan_id = ? ORDER BY plan_date, sort_order');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function getTask(int $taskId, int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT t.* FROM gb_study_tasks t JOIN gb_study_plans p ON p.id = t.plan_id
             WHERE t.id = ? AND p.student_id = ? LIMIT 1'
        );
        $stmt->execute([$taskId, $studentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function setTaskStatus(int $taskId, string $status): void
    {
        $this->db->prepare(
            "UPDATE gb_study_tasks SET status = ?, completed_at = IF(? = 'completed', NOW(), NULL) WHERE id = ?"
        )->execute([$status, $status, $taskId]);
    }

    public function refreshDay(int $dayId): void
    {
        $stmt = $this->db->prepare('SELECT status FROM gb_study_tasks WHERE day_id = ?');
        $stmt->execute([$dayId]);
        $rows = $stmt->fetchAll();
        $n = count($rows);
        $done = count(array_filter($rows, static fn($r) => $r['status'] === 'completed'));
        $pct = $n ? round(($done / $n) * 100, 1) : 0;
        $status = 'upcoming';
        if ($n && $done === $n) {
            $status = 'completed';
        } elseif ($done > 0) {
            $status = 'partial';
        }
        $this->db->prepare('UPDATE gb_study_days SET completed_pct = ?, day_status = ? WHERE id = ?')
            ->execute([$pct, $status, $dayId]);
    }

    public function markMissedBefore(int $planId, string $before): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM gb_study_tasks WHERE plan_id = ? AND plan_date < ? AND status = 'pending'"
        );
        $stmt->execute([$planId, $before]);
        $tasks = $stmt->fetchAll();
        if ($tasks) {
            $ids = array_column($tasks, 'id');
            $in = implode(',', array_fill(0, count($ids), '?'));
            $this->db->prepare("UPDATE gb_study_tasks SET status = 'missed' WHERE id IN ({$in})")->execute($ids);
            $this->db->prepare(
                "UPDATE gb_study_days SET day_status = 'missed'
                 WHERE plan_id = ? AND plan_date < ? AND day_status IN ('upcoming','partial')"
            )->execute([$planId, $before]);
        }
        return $tasks;
    }

    public function ensureDay(int $planId, string $date): int
    {
        $day = $this->getDay($planId, $date);
        if ($day) {
            return (int) $day['id'];
        }
        return $this->insertDay($planId, ['plan_date' => $date, 'is_study_day' => 1, 'day_status' => 'upcoming']);
    }

    public function moveTask(int $taskId, int $dayId, string $date): void
    {
        $this->db->prepare(
            "UPDATE gb_study_tasks SET day_id = ?, plan_date = ?, status = 'pending', completed_at = NULL WHERE id = ?"
        )->execute([$dayId, $date, $taskId]);
    }

    public function upcomingDates(int $planId, string $from, int $limit = 40): array
    {
        $stmt = $this->db->prepare(
            'SELECT plan_date FROM gb_study_days WHERE plan_id = ? AND plan_date >= ? AND is_study_day = 1
             ORDER BY plan_date ASC LIMIT ?'
        );
        $stmt->bindValue(1, $planId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $from);
        $stmt->bindValue(3, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return array_column($stmt->fetchAll(), 'plan_date');
    }

    public function countStatus(int $planId, string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM gb_study_tasks WHERE plan_id = ? AND status = ?');
        $stmt->execute([$planId, $status]);
        return (int) $stmt->fetchColumn();
    }

    public function countTypeStatus(int $planId, string $type, string $status): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM gb_study_tasks WHERE plan_id = ? AND task_type = ? AND status = ?'
        );
        $stmt->execute([$planId, $type, $status]);
        return (int) $stmt->fetchColumn();
    }

    public function updatePlanStats(int $planId, float $pct, int $streak): void
    {
        $this->db->prepare('UPDATE gb_study_plans SET completion_pct = ?, streak_days = ? WHERE id = ?')
            ->execute([$pct, $streak, $planId]);
    }

    public function upsertProgress(int $planId, int $studentId, string $subject, array $counts): void
    {
        $total = ($counts['total_lectures'] ?? 0) + ($counts['total_quizzes'] ?? 0)
            + ($counts['total_notes'] ?? 0) + ($counts['total_flashcards'] ?? 0) + ($counts['total_revision'] ?? 0);
        $done = ($counts['completed_lectures'] ?? 0) + ($counts['completed_quizzes'] ?? 0)
            + ($counts['completed_notes'] ?? 0) + ($counts['completed_flashcards'] ?? 0) + ($counts['completed_revision'] ?? 0);
        $pct = $total ? round(($done / $total) * 100, 1) : 0;
        $this->db->prepare(
            'INSERT INTO gb_study_progress (
                plan_id, student_id, course_id, subject_title,
                total_lectures, completed_lectures, total_quizzes, completed_quizzes,
                total_notes, completed_notes, total_flashcards, completed_flashcards,
                total_revision, completed_revision, completion_pct
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                total_lectures=VALUES(total_lectures), completed_lectures=VALUES(completed_lectures),
                total_quizzes=VALUES(total_quizzes), completed_quizzes=VALUES(completed_quizzes),
                total_notes=VALUES(total_notes), completed_notes=VALUES(completed_notes),
                total_flashcards=VALUES(total_flashcards), completed_flashcards=VALUES(completed_flashcards),
                total_revision=VALUES(total_revision), completed_revision=VALUES(completed_revision),
                completion_pct=VALUES(completion_pct)'
        )->execute([
            $planId, $studentId, $counts['course_id'] ?? null, $subject,
            $counts['total_lectures'] ?? 0, $counts['completed_lectures'] ?? 0,
            $counts['total_quizzes'] ?? 0, $counts['completed_quizzes'] ?? 0,
            $counts['total_notes'] ?? 0, $counts['completed_notes'] ?? 0,
            $counts['total_flashcards'] ?? 0, $counts['completed_flashcards'] ?? 0,
            $counts['total_revision'] ?? 0, $counts['completed_revision'] ?? 0, $pct,
        ]);
    }

    public function subjectProgress(int $planId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM gb_study_progress WHERE plan_id = ? ORDER BY completion_pct ASC');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function upsertStat(int $planId, int $studentId, string $date, array $s): void
    {
        $this->db->prepare(
            'INSERT INTO gb_study_statistics (plan_id, student_id, stat_date, tasks_total, tasks_completed, study_minutes)
             VALUES (?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE tasks_total=VALUES(tasks_total), tasks_completed=VALUES(tasks_completed),
               study_minutes=VALUES(study_minutes)'
        )->execute([
            $planId, $studentId, $date, $s['tasks_total'] ?? 0, $s['tasks_completed'] ?? 0, $s['study_minutes'] ?? 0,
        ]);
    }

    public function createChallenge(int $studentId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO gb_study_challenges (
                student_id, plan_id, title, challenge_type, target_value, current_value, start_date, end_date, status
             ) VALUES (?,?,?,?,?,?,?,?,\'active\')'
        );
        $stmt->execute([
            $studentId, $data['plan_id'], $data['title'], $data['challenge_type'],
            $data['target_value'], $data['current_value'] ?? 0, $data['start_date'], $data['end_date'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function listChallenges(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM gb_study_challenges WHERE student_id = ? AND status = 'active' ORDER BY end_date ASC"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function updateChallengeProgress(int $id, int $current, ?string $status = null): void
    {
        if ($status) {
            $this->db->prepare('UPDATE gb_study_challenges SET current_value = ?, status = ? WHERE id = ?')
                ->execute([$current, $status, $id]);
        } else {
            $this->db->prepare('UPDATE gb_study_challenges SET current_value = ? WHERE id = ?')
                ->execute([$current, $id]);
        }
    }

    public function deletePlan(int $planId, int $studentId): void
    {
        if ($this->getPlanById($planId, $studentId)) {
            $this->db->prepare('DELETE FROM gb_study_plans WHERE id = ?')->execute([$planId]);
        }
    }

    public function resetDay(int $planId, string $date): void
    {
        $this->db->prepare(
            "UPDATE gb_study_tasks SET status = 'pending', completed_at = NULL WHERE plan_id = ? AND plan_date = ?"
        )->execute([$planId, $date]);
        $day = $this->getDay($planId, $date);
        if ($day) {
            $this->refreshDay((int) $day['id']);
        }
    }
}
