<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';

use App\Core\Database;

$pdo = Database::getConnection();

$checks = [
    'quiz_attempts' => 'SELECT COUNT(*) FROM quiz_attempts',
    'quiz_submitted' => "SELECT COUNT(*) FROM quiz_attempts WHERE status IN ('submitted','evaluated')",
    'quiz_all_status' => null,
    'quiz_attempt_answers' => 'SELECT COUNT(*) FROM quiz_attempt_answers',
    'assignments' => 'SELECT COUNT(*) FROM assignments',
    'assignment_submissions' => 'SELECT COUNT(*) FROM assignment_submissions',
    'mcq_attempts' => 'SELECT COUNT(*) FROM mcq_attempts',
    'mcq_answers' => 'SELECT COUNT(*) FROM mcq_attempt_answers',
    'student_question_history' => 'SELECT COUNT(*) FROM student_question_history',
    'daily_challenge_sets' => 'SELECT COUNT(*) FROM daily_challenge_sets',
];

foreach ($checks as $label => $sql) {
    if ($sql === null) {
        continue;
    }
    try {
        echo $label . '=' . $pdo->query($sql)->fetchColumn() . PHP_EOL;
    } catch (Throwable $e) {
        echo $label . '=ERR ' . $e->getMessage() . PHP_EOL;
    }
}

echo "--- quiz_attempts by status ---\n";
try {
    foreach ($pdo->query('SELECT status, COUNT(*) c FROM quiz_attempts GROUP BY status') as $row) {
        echo ($row['status'] ?? 'null') . '=' . $row['c'] . PHP_EOL;
    }
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
}

echo "--- sample student quiz history ---\n";
$sql = "SELECT qa.student_id, u.email, COUNT(*) attempts,
               SUM(qa.status IN ('submitted','evaluated')) submitted,
               ROUND(AVG(qa.percentage),1) avg_pct
        FROM quiz_attempts qa
        JOIN users u ON u.id = qa.student_id
        GROUP BY qa.student_id, u.email
        ORDER BY attempts DESC LIMIT 8";
try {
    foreach ($pdo->query($sql) as $row) {
        echo "id={$row['student_id']} {$row['email']} attempts={$row['attempts']} submitted={$row['submitted']} avg={$row['avg_pct']}\n";
    }
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
}

echo "--- quizzes courses ---\n";
try {
    foreach ($pdo->query('SELECT q.id, q.course_id, q.title, q.status FROM quizzes q ORDER BY q.id DESC LIMIT 10') as $row) {
        echo "#{$row['id']} course={$row['course_id']} status={$row['status']} {$row['title']}\n";
    }
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
}

echo "--- assignment subs sample ---\n";
try {
    $sql = "SELECT s.student_id, u.email, COUNT(*) c, GROUP_CONCAT(DISTINCT s.status) statuses
            FROM assignment_submissions s JOIN users u ON u.id = s.student_id
            GROUP BY s.student_id, u.email ORDER BY c DESC LIMIT 8";
    foreach ($pdo->query($sql) as $row) {
        echo "id={$row['student_id']} {$row['email']} subs={$row['c']} statuses={$row['statuses']}\n";
    }
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
}

echo "--- teacher 6 courses ---\n";
try {
    $sql = "SELECT c.id, c.title FROM courses c
            WHERE c.deleted_at IS NULL AND (c.teacher_id = 6 OR EXISTS (
              SELECT 1 FROM course_teachers ct WHERE ct.course_id = c.id AND ct.teacher_id = 6
            ))";
    foreach ($pdo->query($sql) as $row) {
        echo "#{$row['id']} {$row['title']}\n";
    }
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
}
