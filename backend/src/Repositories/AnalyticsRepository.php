<?php
namespace App\Repositories;

/**
 * Read-only aggregation queries powering the student performance dashboard.
 */
class AnalyticsRepository extends BaseRepository
{
    public function overallMcqStats(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS attempts,
                    COALESCE(SUM(total_questions),0) AS total_questions,
                    COALESCE(SUM(correct_count),0) AS correct,
                    COALESCE(AVG(score),0) AS avg_score,
                    COALESCE(SUM(time_spent_seconds),0) AS mcq_time
             FROM mcq_attempts WHERE student_id = ? AND submitted_at IS NOT NULL'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: [];
    }

    public function accuracyByTopic(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(NULLIF(m.topic,''),'General') AS topic,
                    COUNT(*) AS total,
                    SUM(aa.is_correct) AS correct,
                    ROUND(100 * SUM(aa.is_correct) / COUNT(*)) AS accuracy
             FROM mcq_attempt_answers aa
             JOIN mcq_attempts a ON a.id = aa.attempt_id
             JOIN mcqs m ON m.id = aa.mcq_id
             WHERE a.student_id = ?
             GROUP BY topic
             HAVING total >= 1
             ORDER BY accuracy ASC"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function accuracyBySubject(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT mo.title AS subject,
                    COUNT(*) AS total,
                    SUM(aa.is_correct) AS correct,
                    ROUND(100 * SUM(aa.is_correct) / COUNT(*)) AS accuracy
             FROM mcq_attempt_answers aa
             JOIN mcq_attempts a ON a.id = aa.attempt_id
             JOIN mcqs m ON m.id = aa.mcq_id
             JOIN lectures l ON l.id = m.lecture_id
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules mo ON mo.id = ch.module_id
             WHERE a.student_id = ?
             GROUP BY mo.title
             HAVING total >= 2
             ORDER BY accuracy ASC"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function weakAreasWithResources(int $studentId, int $limit = 5): array
    {
        $areas = $this->accuracyBySubject($studentId);
        $out = [];
        foreach (array_slice($areas, 0, $limit) as $area) {
            $subject = $area['subject'];
            $stmt = $this->db->prepare(
                "SELECT l.id AS lecture_id, l.title AS lecture_title
                 FROM lectures l
                 JOIN chapters ch ON ch.id = l.chapter_id
                 JOIN modules mo ON mo.id = ch.module_id
                 JOIN ai_lecture_content ac ON ac.lecture_id = l.id AND ac.status = 'published'
                 WHERE mo.title = ?
                 ORDER BY l.sort_order ASC LIMIT 1"
            );
            $stmt->execute([$subject]);
            $lecture = $stmt->fetch() ?: null;
            $flashCount = 0;
            $mcqCount = 0;
            if ($lecture) {
                $fc = $this->db->prepare("SELECT COUNT(*) FROM flashcards WHERE lecture_id = ? AND status = 'approved'");
                $fc->execute([(int) $lecture['lecture_id']]);
                $flashCount = (int) $fc->fetchColumn();
                $mc = $this->db->prepare("SELECT COUNT(*) FROM mcqs WHERE lecture_id = ? AND status = 'published'");
                $mc->execute([(int) $lecture['lecture_id']]);
                $mcqCount = (int) $mc->fetchColumn();
            }
            $out[] = [
                'subject'       => $subject,
                'accuracy'      => (int) $area['accuracy'],
                'message'       => "You need more practice in {$subject}.",
                'lecture_id'    => $lecture['lecture_id'] ?? null,
                'lecture_title' => $lecture['lecture_title'] ?? null,
                'flashcards'    => $flashCount,
                'mcqs'          => $mcqCount,
                'has_notes'     => (bool) $lecture,
            ];
        }
        return $out;
    }

    public function accuracyByDifficulty(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.difficulty,
                    COUNT(*) AS total,
                    SUM(aa.is_correct) AS correct,
                    ROUND(100 * SUM(aa.is_correct) / COUNT(*)) AS accuracy
             FROM mcq_attempt_answers aa
             JOIN mcq_attempts a ON a.id = aa.attempt_id
             JOIN mcqs m ON m.id = aa.mcq_id
             WHERE a.student_id = ?
             GROUP BY m.difficulty"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /** Daily study activity for the last N days (for the activity graph). */
    public function dailyActivity(int $studentId, int $days = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT activity_date, SUM(count) AS total
             FROM study_activity_log
             WHERE student_id = ? AND activity_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY activity_date ORDER BY activity_date ASC"
        );
        $stmt->execute([$studentId, $days]);
        return $stmt->fetchAll();
    }

    /** Weekly average score for the last N weeks. */
    public function weeklyProgress(int $studentId, int $weeks = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT YEARWEEK(submitted_at, 3) AS yw,
                    MIN(DATE(submitted_at)) AS week_start,
                    COUNT(*) AS attempts,
                    ROUND(AVG(score)) AS avg_score
             FROM mcq_attempts
             WHERE student_id = ? AND submitted_at IS NOT NULL
               AND submitted_at >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
             GROUP BY yw ORDER BY yw ASC"
        );
        $stmt->execute([$studentId, $weeks]);
        return $stmt->fetchAll();
    }

    public function flashcardStats(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                SUM(status = 'mastered') AS mastered,
                SUM(status = 'learning') AS learning,
                SUM(last_reviewed_at IS NOT NULL) AS reviewed,
                COUNT(*) AS tracked
             FROM student_flashcard_progress WHERE student_id = ?"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: [];
    }

    public function lecturesCompleted(int $studentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM lecture_progress WHERE student_id = ? AND completed = 1'
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    public function lectureWatchTime(int $studentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(watch_time_seconds),0) FROM lecture_progress WHERE student_id = ?'
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    public function activityTypeCount(int $studentId, string $type): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(count),0) FROM study_activity_log WHERE student_id = ? AND activity_type = ?'
        );
        $stmt->execute([$studentId, $type]);
        return (int) $stmt->fetchColumn();
    }
}
