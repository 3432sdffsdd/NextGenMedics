<?php
require_once __DIR__ . '/../bootstrap.php';

$config = require __DIR__ . '/../config/config.php';
$db = $config['db'];
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']);
$pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = (int) $pdo->query('SELECT COUNT(*) FROM assignments')->fetchColumn();
echo "Assignments before: {$before}\n";

$stmt = $pdo->prepare(
    "INSERT INTO assignments (course_id, teacher_id, title, description, instructions, due_date, max_marks, attachment_path, assignment_type, external_url, status)
     VALUES (?,?,?,?,?,?,?,?,?,?,?)"
);
$stmt->execute([3, 6, 'Test auto ' . time(), null, null, '2026-12-01 12:00:00', 100, null, 'file', null, 'published']);
$newId = (int) $pdo->lastInsertId();
echo "Inserted id: {$newId}\n";

$after = (int) $pdo->query('SELECT COUNT(*) FROM assignments')->fetchColumn();
echo "Assignments after: {$after}\n";

$list = $pdo->prepare('SELECT id, title FROM assignments WHERE course_id = 3 ORDER BY id DESC');
$list->execute();
foreach ($list->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  course 3: #{$row['id']} {$row['title']}\n";
}

$pdo->prepare('DELETE FROM assignments WHERE id = ?')->execute([$newId]);
echo "Cleaned up test row {$newId}\n";
