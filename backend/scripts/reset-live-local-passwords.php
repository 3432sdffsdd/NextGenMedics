<?php
/**
 * Local-only: set known passwords for live accounts so you can log in after importing the live dump.
 */
require_once __DIR__ . '/../bootstrap.php';

new App\Core\Application(); // load .env

$pdo = App\Core\Database::getConnection();

$accounts = [
    'admin3@nextgenmedics.info' => ['Admin@123', 'admin'],
    'admin@nextgenmedics.com'   => ['Admin@123', 'admin'],
    'talhanazeer3@gmail.com'    => ['Teacher@123', 'teacher'],
    'sskhan.pk@gmail.com'       => ['Teacher@123', 'teacher'],
    'teacher@nextgenmedics.com' => ['Teacher@123', 'teacher'],
    'student@nextgenmedics.com' => ['Student@123', 'student'],
    'mobarra.asim25@gmail.com'  => ['Student@123', 'student'],
];

$stmt = $pdo->prepare(
    'UPDATE users SET password = ?, deleted_at = NULL, status = "active" WHERE email = ?'
);

foreach ($accounts as $email => [$password, $role]) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt->execute([$hash, $email]);
    $ok = $stmt->rowCount() > 0 ? 'OK' : 'not found';
    echo "{$ok}: {$email} / {$password} ({$role})\n";
}

// Point sample courses away from soft-deleted demo teacher if needed
$pdo->exec(
    'UPDATE courses SET teacher_id = 6
     WHERE teacher_id = 2 AND deleted_at IS NULL AND id IN (2, 3)'
);
$pdo->exec('DELETE FROM course_teachers WHERE teacher_id = 2');
$pdo->exec('INSERT IGNORE INTO course_teachers (course_id, teacher_id) VALUES (2, 6), (3, 6), (1, 5), (1, 6)');

echo "\nCounts:\n";
echo 'Users: ' . $pdo->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn() . "\n";
echo 'Teachers: ' . $pdo->query(
    'SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug="teacher" AND u.deleted_at IS NULL'
)->fetchColumn() . "\n";
echo 'Students: ' . $pdo->query(
    'SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug="student" AND u.deleted_at IS NULL'
)->fetchColumn() . "\n";
echo 'Courses: ' . $pdo->query('SELECT COUNT(*) FROM courses WHERE deleted_at IS NULL')->fetchColumn() . "\n";
echo 'Lectures: ' . $pdo->query('SELECT COUNT(*) FROM lectures')->fetchColumn() . "\n";
echo 'Resources: ' . $pdo->query('SELECT COUNT(*) FROM lecture_resources')->fetchColumn() . "\n";
echo "\nDone. Use http://127.0.0.1:5173\n";
