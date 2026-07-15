<?php
require_once __DIR__ . '/../bootstrap.php';
$pdo = App\Core\Database::getConnection();
$row = $pdo->query("SELECT id, email, status, deleted_at FROM users WHERE email='teacher@nextgenmedics.com'")->fetch(PDO::FETCH_ASSOC);
echo json_encode($row) . PHP_EOL;
$role = $pdo->query("SELECT r.slug FROM users u JOIN roles r ON r.id=u.role_id WHERE u.email='teacher@nextgenmedics.com' AND u.deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
echo 'join: ' . json_encode($role) . PHP_EOL;
