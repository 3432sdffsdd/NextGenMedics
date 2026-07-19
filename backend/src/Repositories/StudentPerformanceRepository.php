<?php
namespace App\Repositories;

/**
 * Teacher-facing student performance aggregates (read-only).
 */
class StudentPerformanceRepository extends BaseRepository
{
    public function teacherCourseIds(int $teacherId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id FROM courses c
             WHERE c.deleted_at IS NULL
               AND (c.teacher_id = ? OR EXISTS (
                    SELECT 1 FROM course_teachers ct WHERE ct.course_id = c.id AND ct.teacher_id = ?
               ))'
        );
        $stmt->execute([$teacherId, $teacherId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }

    public function teacherCanAccessStudent(int $teacherId, int $studentId): bool
    {
        $courseIds = $this->teacherCourseIds($teacherId);
        if (!$courseIds) {
            return false;
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $stmt = $this->db->prepare(
            "SELECT 1 FROM course_enrollments
             WHERE student_id = ? AND status = 'active' AND course_id IN ({$in})
             LIMIT 1"
        );
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /** Unique enrolled students across all teacher courses. */
    public function listStudentsForTeacher(int $teacherId, ?int $courseId = null, string $q = ''): array
    {
        $courseIds = $this->teacherCourseIds($teacherId);
        if ($courseId) {
            if (!in_array($courseId, $courseIds, true)) {
                return [];
            }
            $courseIds = [$courseId];
        }
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = $courseIds;
        $search = '';
        if (trim($q) !== '') {
            $search = ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR CONCAT(u.first_name, " ", u.last_name) LIKE ?)';
            $like = '%' . trim($q) . '%';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        $stmt = $this->db->prepare(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.status,
                    GROUP_CONCAT(DISTINCT c.title ORDER BY c.title SEPARATOR ', ') AS courses,
                    COUNT(DISTINCT ce.course_id) AS course_count,
                    MAX(ce.enrolled_at) AS last_enrolled_at
             FROM course_enrollments ce
             JOIN users u ON u.id = ce.student_id
             JOIN courses c ON c.id = ce.course_id
             WHERE ce.status = 'active' AND u.deleted_at IS NULL AND ce.course_id IN ({$in})
             {$search}
             GROUP BY u.id, u.first_name, u.last_name, u.email, u.status
             ORDER BY u.first_name, u.last_name"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function studentCoursesForTeacher(int $teacherId, int $studentId): array
    {
        $courseIds = $this->teacherCourseIds($teacherId);
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $stmt = $this->db->prepare(
            "SELECT c.id, c.title, ce.enrolled_at, ce.progress
             FROM course_enrollments ce
             JOIN courses c ON c.id = ce.course_id
             WHERE ce.student_id = ? AND ce.status = 'active' AND ce.course_id IN ({$in})
             ORDER BY c.title"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findStudent(int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, first_name, last_name, email, status, created_at
             FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: null;
    }

    /** Completed course-quiz attempts (past history only — no teacher entry). */
    private function completedAttemptClause(): string
    {
        return "(qa.status IN ('submitted','evaluated') OR qa.submitted_at IS NOT NULL)";
    }

    public function quizStats(int $studentId, array $courseIds): array
    {
        if (!$courseIds) {
            return [
                'attempts' => 0, 'avg_score' => 0, 'passed' => 0, 'failed' => 0,
                'total_questions_answered' => 0, 'correct' => 0, 'incorrect' => 0,
                'accuracy' => 0,
            ];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $done = $this->completedAttemptClause();
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS attempts,
                    ROUND(COALESCE(AVG(qa.percentage),0),1) AS avg_score,
                    SUM(qa.passed = 1) AS passed,
                    SUM(qa.passed = 0 OR qa.passed IS NULL) AS failed
             FROM quiz_attempts qa
             JOIN quizzes q ON q.id = qa.quiz_id
             WHERE qa.student_id = ?
               AND {$done}
               AND q.course_id IN ({$in})"
        );
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];

        $ans = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(qaa.is_correct = 1) AS correct,
                    SUM(qaa.is_correct = 0) AS incorrect
             FROM quiz_attempt_answers qaa
             JOIN quiz_attempts qa ON qa.id = qaa.attempt_id
             JOIN quizzes q ON q.id = qa.quiz_id
             WHERE qa.student_id = ?
               AND {$done}
               AND q.course_id IN ({$in})"
        );
        $ans->execute($params);
        $a = $ans->fetch() ?: [];
        $total = (int) ($a['total'] ?? 0);
        $correct = (int) ($a['correct'] ?? 0);

        return [
            'attempts' => (int) ($row['attempts'] ?? 0),
            'avg_score' => (float) ($row['avg_score'] ?? 0),
            'passed' => (int) ($row['passed'] ?? 0),
            'failed' => (int) ($row['failed'] ?? 0),
            'total_questions_answered' => $total,
            'correct' => $correct,
            'incorrect' => (int) ($a['incorrect'] ?? 0),
            'accuracy' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
        ];
    }

    public function recentQuizAttempts(int $studentId, array $courseIds, int $limit = 10): array
    {
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $done = $this->completedAttemptClause();
        $stmt = $this->db->prepare(
            "SELECT qa.id, qa.score, qa.percentage, qa.passed, qa.submitted_at, qa.status,
                    q.title AS quiz_title, c.title AS course_title,
                    (SELECT COUNT(*) FROM quiz_attempt_answers x WHERE x.attempt_id = qa.id AND x.is_correct = 1) AS correct,
                    (SELECT COUNT(*) FROM quiz_attempt_answers x WHERE x.attempt_id = qa.id AND x.is_correct = 0) AS incorrect,
                    (SELECT COUNT(*) FROM quiz_attempt_answers x WHERE x.attempt_id = qa.id) AS answered
             FROM quiz_attempts qa
             JOIN quizzes q ON q.id = qa.quiz_id
             JOIN courses c ON c.id = q.course_id
             WHERE qa.student_id = ?
               AND {$done}
               AND q.course_id IN ({$in})
             ORDER BY COALESCE(qa.submitted_at, qa.started_at) DESC
             LIMIT {$limit}"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Weak areas from past course-quiz answers (by course). */
    public function quizAccuracyByCourse(int $studentId, array $courseIds): array
    {
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $done = $this->completedAttemptClause();
        $stmt = $this->db->prepare(
            "SELECT c.title AS subject,
                    COUNT(*) AS total,
                    SUM(qaa.is_correct = 1) AS correct,
                    ROUND(100 * SUM(qaa.is_correct = 1) / COUNT(*)) AS accuracy
             FROM quiz_attempt_answers qaa
             JOIN quiz_attempts qa ON qa.id = qaa.attempt_id
             JOIN quizzes q ON q.id = qa.quiz_id
             JOIN courses c ON c.id = q.course_id
             WHERE qa.student_id = ?
               AND {$done}
               AND q.course_id IN ({$in})
             GROUP BY c.id, c.title
             HAVING total >= 1
             ORDER BY accuracy ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Weak areas from past course-quiz answers (by quiz title as topic). */
    public function quizAccuracyByQuiz(int $studentId, array $courseIds): array
    {
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $done = $this->completedAttemptClause();
        $stmt = $this->db->prepare(
            "SELECT q.title AS topic,
                    c.title AS subject,
                    COUNT(*) AS total,
                    SUM(qaa.is_correct = 1) AS correct,
                    ROUND(100 * SUM(qaa.is_correct = 1) / COUNT(*)) AS accuracy
             FROM quiz_attempt_answers qaa
             JOIN quiz_attempts qa ON qa.id = qaa.attempt_id
             JOIN quizzes q ON q.id = qa.quiz_id
             JOIN courses c ON c.id = q.course_id
             WHERE qa.student_id = ?
               AND {$done}
               AND q.course_id IN ({$in})
             GROUP BY q.id, q.title, c.title
             HAVING total >= 1
             ORDER BY accuracy ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function mcqPracticeStats(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS attempts,
                    COALESCE(SUM(total_questions),0) AS total_questions,
                    COALESCE(SUM(correct_count),0) AS correct,
                    ROUND(COALESCE(AVG(score),0),1) AS avg_score
             FROM mcq_attempts WHERE student_id = ? AND submitted_at IS NOT NULL'
        );
        $stmt->execute([$studentId]);
        $row = $stmt->fetch() ?: [];
        $total = (int) ($row['total_questions'] ?? 0);
        $correct = (int) ($row['correct'] ?? 0);
        return [
            'attempts' => (int) ($row['attempts'] ?? 0),
            'total_questions' => $total,
            'correct' => $correct,
            'incorrect' => max(0, $total - $correct),
            'avg_score' => (float) ($row['avg_score'] ?? 0),
            'accuracy' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
        ];
    }

    public function attendanceSummary(int $studentId, array $courseIds): array
    {
        if (!$courseIds) {
            return ['sessions' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'attendance_pct' => 0, 'by_course' => []];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $stmt = $this->db->prepare(
            "SELECT r.status, COUNT(*) AS cnt
             FROM attendance_records r
             JOIN attendance_sessions s ON s.id = r.session_id
             WHERE r.student_id = ? AND s.course_id IN ({$in})
             GROUP BY r.status"
        );
        $stmt->execute($params);
        $counts = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
        }
        $attended = $counts['present'] + $counts['late'];
        $sessions = array_sum($counts);
        $byCourse = [];
        $cstmt = $this->db->prepare(
            "SELECT c.id, c.title,
                    COUNT(*) AS marked,
                    SUM(r.status IN ('present','late')) AS attended
             FROM attendance_records r
             JOIN attendance_sessions s ON s.id = r.session_id
             JOIN courses c ON c.id = s.course_id
             WHERE r.student_id = ? AND s.course_id IN ({$in})
             GROUP BY c.id, c.title
             ORDER BY c.title"
        );
        $cstmt->execute($params);
        foreach ($cstmt->fetchAll() as $row) {
            $marked = (int) $row['marked'];
            $att = (int) $row['attended'];
            $byCourse[] = [
                'course_id' => (int) $row['id'],
                'course_title' => $row['title'],
                'marked' => $marked,
                'attended' => $att,
                'attendance_pct' => $marked > 0 ? round(($att / $marked) * 100, 1) : 0,
            ];
        }
        return [
            'sessions' => $sessions,
            'present' => $counts['present'],
            'absent' => $counts['absent'],
            'late' => $counts['late'],
            'excused' => $counts['excused'],
            'attendance_pct' => $sessions > 0 ? round(($attended / $sessions) * 100, 1) : 0,
            'by_course' => $byCourse,
        ];
    }

    /**
     * Incorrect answers from past quiz_attempt_answers (authoritative history).
     * Resolves option letters from quiz_question_options.
     */
    public function wrongQuizQuestions(int $studentId, array $courseIds, int $limit = 25): array
    {
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $done = $this->completedAttemptClause();
        $stmt = $this->db->prepare(
            "SELECT qaa.question_id, qaa.selected_option_ids,
                    COALESCE(qa.submitted_at, qa.started_at) AS attempt_date,
                    qq.question_text, qq.explanation,
                    q.title AS quiz_title, c.title AS course_title
             FROM quiz_attempt_answers qaa
             JOIN quiz_attempts qa ON qa.id = qaa.attempt_id
             JOIN quizzes q ON q.id = qa.quiz_id
             JOIN courses c ON c.id = q.course_id
             JOIN quiz_questions qq ON qq.id = qaa.question_id
             WHERE qa.student_id = ?
               AND {$done}
               AND qaa.is_correct = 0
               AND q.course_id IN ({$in})
             ORDER BY COALESCE(qa.submitted_at, qa.started_at) DESC, qaa.id DESC
             LIMIT {$limit}"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $letters = $this->optionLettersForQuestion((int) $r['question_id']);
            $selected = $this->selectedOptionLetter($r['selected_option_ids'] ?? null, $letters);
            $correct = null;
            foreach ($letters as $letter => $meta) {
                if (!empty($meta['is_correct'])) {
                    $correct = $letter;
                    break;
                }
            }
            $out[] = [
                'question_id' => (int) $r['question_id'],
                'selected_option' => $selected,
                'selected_option_text' => $selected && isset($letters[$selected])
                    ? (string) ($letters[$selected]['text'] ?? '')
                    : null,
                'correct_option' => $correct,
                'correct_option_text' => $correct && isset($letters[$correct])
                    ? (string) ($letters[$correct]['text'] ?? '')
                    : null,
                'attempt_date' => $r['attempt_date'],
                'source' => 'quiz',
                'question_text' => $r['question_text'],
                'explanation' => $r['explanation'],
                'quiz_title' => $r['quiz_title'],
                'course_title' => $r['course_title'],
            ];
        }
        return $out;
    }

    /** @return array<string, array{id:int,is_correct:bool,text:string}> letter => meta */
    private function optionLettersForQuestion(int $questionId): array
    {
        $opts = $this->db->prepare(
            'SELECT id, is_correct, option_text FROM quiz_question_options
             WHERE question_id = ? ORDER BY sort_order, id LIMIT 5'
        );
        $opts->execute([$questionId]);
        $letters = ['A', 'B', 'C', 'D', 'E'];
        $map = [];
        foreach ($opts->fetchAll() as $i => $opt) {
            $letter = $letters[$i] ?? null;
            if ($letter === null) {
                break;
            }
            $map[$letter] = [
                'id' => (int) $opt['id'],
                'is_correct' => (int) $opt['is_correct'] === 1,
                'text' => (string) ($opt['option_text'] ?? ''),
            ];
        }
        return $map;
    }

    private function selectedOptionLetter(mixed $selectedJson, array $letters): ?string
    {
        if ($selectedJson === null || $selectedJson === '') {
            return null;
        }
        // Already a letter (A–E)
        if (is_string($selectedJson) && preg_match('/^[A-Ea-e]$/', trim($selectedJson))) {
            return strtoupper(trim($selectedJson));
        }
        $ids = $selectedJson;
        if (is_string($selectedJson)) {
            $decoded = json_decode($selectedJson, true);
            if ($decoded === null && is_numeric(trim($selectedJson))) {
                $ids = [(int) trim($selectedJson)];
            } elseif (is_array($decoded)) {
                $ids = $decoded;
            } elseif (is_int($decoded) || is_float($decoded)) {
                $ids = [(int) $decoded];
            } else {
                $ids = [];
            }
        } elseif (is_int($selectedJson) || is_float($selectedJson)) {
            $ids = [(int) $selectedJson];
        } elseif (!is_array($selectedJson)) {
            $ids = [];
        }
        if (!$ids) {
            return null;
        }
        // Prefer first numeric option id
        $selectedId = 0;
        foreach ($ids as $v) {
            if (is_numeric($v)) {
                $selectedId = (int) $v;
                break;
            }
            if (is_string($v) && preg_match('/^[A-Ea-e]$/', trim($v))) {
                return strtoupper(trim($v));
            }
        }
        if ($selectedId <= 0) {
            return null;
        }
        foreach ($letters as $letter => $meta) {
            if ((int) $meta['id'] === $selectedId) {
                return $letter;
            }
        }
        return null;
    }

    /**
     * Assignment status for one student across teacher courses.
     * given / submitted / pending / overdue / graded + item list.
     */
    public function assignmentStats(int $studentId, array $courseIds): array
    {
        $empty = [
            'given' => 0,
            'submitted' => 0,
            'pending' => 0,
            'overdue' => 0,
            'graded' => 0,
            'avg_marks' => null,
            'items' => [],
        ];
        if (!$courseIds) {
            return $empty;
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = array_merge([$studentId], $courseIds);
        $stmt = $this->db->prepare(
            "SELECT a.id, a.title, a.due_date, a.max_marks, a.status AS assignment_status,
                    c.id AS course_id, c.title AS course_title,
                    s.status AS submission_status, s.marks, s.percentage, s.submitted_at, s.graded_at
             FROM assignments a
             JOIN courses c ON c.id = a.course_id
             LEFT JOIN assignment_submissions s ON s.assignment_id = a.id AND s.student_id = ?
             WHERE a.status = 'published' AND a.course_id IN ({$in})
             ORDER BY a.due_date IS NULL, a.due_date ASC, a.id DESC"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $now = time();
        $given = count($rows);
        $submitted = 0;
        $pending = 0;
        $overdue = 0;
        $graded = 0;
        $marksSum = 0.0;
        $marksN = 0;
        $items = [];

        foreach ($rows as $r) {
            $subStatus = $r['submission_status'] ?? null;
            $dueTs = !empty($r['due_date']) ? strtotime($r['due_date']) : null;
            $isPastDue = $dueTs !== null && $dueTs < $now;
            $hasSubmission = in_array($subStatus, ['submitted', 'graded', 'late'], true);

            if ($subStatus === 'graded') {
                $state = 'graded';
                $graded++;
                $submitted++;
                if ($r['marks'] !== null && $r['marks'] !== '') {
                    $marksSum += (float) $r['marks'];
                    $marksN++;
                }
            } elseif ($hasSubmission) {
                $state = 'submitted';
                $submitted++;
            } elseif ($isPastDue) {
                $state = 'overdue';
                $overdue++;
            } else {
                $state = 'pending';
                $pending++;
            }

            $items[] = [
                'id' => (int) $r['id'],
                'title' => $r['title'],
                'course_id' => (int) $r['course_id'],
                'course_title' => $r['course_title'],
                'due_date' => $r['due_date'],
                'max_marks' => $r['max_marks'] !== null ? (float) $r['max_marks'] : null,
                'marks' => $r['marks'] !== null ? (float) $r['marks'] : null,
                'percentage' => $r['percentage'] !== null ? (float) $r['percentage'] : null,
                'submitted_at' => $r['submitted_at'],
                'state' => $state,
            ];
        }

        return [
            'given' => $given,
            'submitted' => $submitted,
            'pending' => $pending,
            'overdue' => $overdue,
            'graded' => $graded,
            'avg_marks' => $marksN > 0 ? round($marksSum / $marksN, 1) : null,
            'items' => $items,
        ];
    }

    public function coursesByIds(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT id, title FROM courses WHERE id IN ({$in}) AND deleted_at IS NULL ORDER BY title"
        );
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }

    public function summaryCardsForList(int $studentId, array $courseIds): array
    {

        $quiz = $this->quizStats($studentId, $courseIds);
        $att = $this->attendanceSummary($studentId, $courseIds);
        $video = ['completed' => 0, 'average_watch_pct' => 0, 'total_videos' => 0];
        if ($courseIds) {
            // lightweight video counts
            $in = implode(',', array_fill(0, count($courseIds), '?'));
            $params = array_merge([$studentId], $courseIds);
            $stmt = $this->db->prepare(
                "SELECT
                    COUNT(DISTINCT lr.id) AS total_videos,
                    COUNT(DISTINCT CASE WHEN p.status = 'completed' THEN lr.id END) AS completed,
                    ROUND(COALESCE(AVG(p.completion_pct),0),1) AS average_watch_pct
                 FROM lecture_resources lr
                 JOIN lectures l ON l.id = lr.lecture_id
                 JOIN chapters ch ON ch.id = l.chapter_id
                 JOIN modules m ON m.id = ch.module_id
                 LEFT JOIN vt_video_progress p ON p.resource_id = lr.id AND p.student_id = ?
                 WHERE lr.type = 'video' AND m.course_id IN ({$in})"
            );
            $stmt->execute($params);
            $video = $stmt->fetch() ?: $video;
        }
        $mcq = $this->mcqPracticeStats($studentId);
        $quizWrong = (int) ($quiz['incorrect'] ?? 0);
        $mcqWrong = (int) ($mcq['incorrect'] ?? 0);
        // Mistakes = all wrong answers from course quizzes + MCQ practice
        $mistakes = $quizWrong + $mcqWrong;
        $asg = $this->assignmentStats($studentId, $courseIds);

        return [
            'quiz_avg_score' => (float) ($quiz['avg_score'] ?? 0),
            'quiz_attempts' => (int) ($quiz['attempts'] ?? 0),
            'quiz_correct' => (int) ($quiz['correct'] ?? 0),
            'quiz_incorrect' => $quizWrong,
            'quiz_answered' => (int) ($quiz['total_questions_answered'] ?? 0),
            'attendance_pct' => (float) ($att['attendance_pct'] ?? 0),
            'videos_completed' => (int) ($video['completed'] ?? 0),
            'videos_total' => (int) ($video['total_videos'] ?? 0),
            'avg_watch_pct' => (float) ($video['average_watch_pct'] ?? 0),
            'active_mistakes' => $mistakes,
            'mistakes' => $mistakes,
            'assignments_given' => (int) $asg['given'],
            'assignments_submitted' => (int) $asg['submitted'],
            'assignments_pending' => (int) $asg['pending'],
            'assignments_overdue' => (int) $asg['overdue'],
        ];
    }
}
