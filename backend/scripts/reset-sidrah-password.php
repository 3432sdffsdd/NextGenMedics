<?php
/** Local-only: reset Sidrah Khan teacher password. */
require_once __DIR__ . '/../bootstrap.php';
new App\Core\Application();

$pdo = App\Core\Database::getConnection();
$email = 'sskhan.pk@gmail.com';
$password = 'Teacher@123';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
$stmt->execute([$hash, $email]);

echo 'updated_rows=' . $stmt->rowCount() . PHP_EOL;

$q = $pdo->prepare('SELECT id, full_name, email, status FROM users WHERE email = ?');
$q->execute([$email]);
$row = $q->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo "Account not found for {$email}\n";
    exit(1);
}
echo "name={$row['full_name']}\n";
echo "email={$row['email']}\n";
echo "status={$row['status']}\n";
echo "password={$password}\n";
