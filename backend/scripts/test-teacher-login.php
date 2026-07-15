<?php
require_once __DIR__ . '/../bootstrap.php';

$pdo = App\Core\Database::getConnection();
$row = $pdo->query("SELECT id, email, username, status, deleted_at FROM users WHERE email='teacher@nextgenmedics.com'")->fetch(PDO::FETCH_ASSOC);
echo "DB: " . json_encode($row) . PHP_EOL;

$ch = curl_init('http://127.0.0.1:8080/auth/login');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode(['email' => 'teacher@nextgenmedics.com', 'password' => 'Teacher@123']),
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);
echo "API HTTP {$code}" . ($err ? " curl: {$err}" : '') . PHP_EOL;
echo $body . PHP_EOL;
