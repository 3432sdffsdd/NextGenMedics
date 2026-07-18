<?php
namespace App\Repositories;

class StudyPlanRepository extends BaseRepository
{
    public function getPlan(int $studentId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM study_plans WHERE student_id = ?');
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: null;
    }

    public function upsert(int $studentId, string $examDate, float $hoursPerDay): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO study_plans (student_id, exam_date, hours_per_day) VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE exam_date = VALUES(exam_date), hours_per_day = VALUES(hours_per_day), updated_at = NOW()'
        );
        $stmt->execute([$studentId, $examDate, $hoursPerDay]);
        $plan = $this->getPlan($studentId);
        return (int) $plan['id'];
    }

    public function clearFutureTasks(int $planId, string $fromDate): void
    {
        $stmt = $this->db->prepare('DELETE FROM study_plan_tasks WHERE plan_id = ? AND task_date >= ?');
        $stmt->execute([$planId, $fromDate]);
    }

    public function addTasks(int $planId, array $tasks): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO study_plan_tasks (plan_id, task_date, task_type, title, lecture_id, target_count, sort_order)
             VALUES (?,?,?,?,?,?,?)'
        );
        foreach ($tasks as $t) {
            $stmt->execute([
                $planId, $t['task_date'], $t['task_type'], $t['title'],
                $t['lecture_id'] ?? null, $t['target_count'] ?? null, $t['sort_order'] ?? 0,
            ]);
        }
    }

    public function tasksForDate(int $planId, string $date): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM study_plan_tasks WHERE plan_id = ? AND task_date = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$planId, $date]);
        return $stmt->fetchAll();
    }

    public function tasksRange(int $planId, string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM study_plan_tasks WHERE plan_id = ? AND task_date BETWEEN ? AND ? ORDER BY task_date ASC, sort_order ASC'
        );
        $stmt->execute([$planId, $from, $to]);
        return $stmt->fetchAll();
    }

    public function updateTaskStatus(int $taskId, int $planId, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE study_plan_tasks SET status = ?, completed_at = IF(? = "completed", NOW(), NULL)
             WHERE id = ? AND plan_id = ?'
        );
        return $stmt->execute([$status, $status, $taskId, $planId]);
    }
}
