<?php
/**
 * Restore default dev accounts and reset passwords (local only).
 */
require_once __DIR__ . '/../bootstrap.php';

$config = require __DIR__ . '/../config/config.php';
if (($config['app']['env'] ?? '') === 'production') {
    fwrite(STDERR, "Refusing to run in production.\n");
    exit(1);
}

$pdo = App\Core\Database::getConnection();
$emails = ['admin@nextgenmedics.com', 'teacher@nextgenmedics.com', 'student@nextgenmedics.com'];
$passwords = [
    'admin@nextgenmedics.com'   => 'Admin@123',
    'teacher@nextgenmedics.com' => 'Teacher@123',
    'student@nextgenmedics.com' => 'Student@123',
];

$restore = $pdo->prepare('UPDATE users SET deleted_at = NULL, status = "active", password = ? WHERE email = ?');
foreach ($emails as $email) {
    $hash = password_hash($passwords[$email], PASSWORD_BCRYPT, ['cost' => 12]);
    $restore->execute([$hash, $email]);
    echo "Restored: {$email} / {$passwords[$email]}\n";
}

echo "\nDone. Log in at http://127.0.0.1:5173\n";
