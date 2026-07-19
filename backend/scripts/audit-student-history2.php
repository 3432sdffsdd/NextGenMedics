<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
$pdo = App\Core\Database::getConnection();

echo "=== answers for student 7 ===\n";
$sql = "SELECT qa.id, qa.status, qa.percentage, qa.submitted_at,
               COUNT(qaa.id) answers,
               SUM(qaa.is_correct=1) correct,
               SUM(qaa.is_correct=0) wrong,
               q.course_id, q.title
        FROM quiz_attempts qa
        JOIN quizzes q ON q.id = qa.quiz_id
        LEFT JOIN quiz_attempt_answers qaa ON qaa.attempt_id = qa.id
        WHERE qa.student_id = 7
        GROUP BY qa.id";
foreach ($pdo->query($sql) as $r) {
    echo json_encode($r) . PHP_EOL;
}

echo "=== assignments ===\n";
foreach ($pdo->query('SELECT id, course_id, title, status, due_date FROM assignments') as $r) {
    echo json_encode($r) . PHP_EOL;
}

echo "=== mcq practice student 7 ===\n";
$sql = "SELECT COUNT(*) attempts, COALESCE(SUM(total_questions),0) q, COALESCE(SUM(correct_count),0) c
        FROM mcq_attempts WHERE student_id=7 AND submitted_at IS NOT NULL";
echo json_encode($pdo->query($sql)->fetch()) . PHP_EOL;

echo "=== enrollments student 7 ===\n";
foreach ($pdo->query("SELECT course_id, status FROM course_enrollments WHERE student_id=7") as $r) {
    echo json_encode($r) . PHP_EOL;
}
