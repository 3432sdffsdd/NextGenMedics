<?php
/**
 * FRESH database install only — creates schema + demo seed data.
 *
 * BLOCKED if the database already has users, so your real teacher/student
 * records are never overwritten.
 *
 * For an existing database with your data, use instead:
 *   php database/migrate.php
 */
require_once __DIR__ . '/../bootstrap.php';

$config = require __DIR__ . '/../config/config.php';
$db = $config['db'];

echo "NextGen Medics LMS - Database Installer\n";
echo "========================================\n\n";

try {
    $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $db['host'], $db['port'], $db['charset']);
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Safety: refuse to run if users already exist (protects imported teacher/student data).
    try {
        $pdo->exec('USE `' . str_replace('`', '``', $db['name']) . '`');
        $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($userCount > 0) {
            echo "[BLOCKED] This database already has {$userCount} user(s).\n";
            echo "Fresh install was cancelled to protect your teacher and student data.\n\n";
            echo "To add new tables/columns only (safe), run:\n";
            echo "  php database/migrate.php\n\n";
            exit(0);
        }
    } catch (PDOException) {
        // Database or users table does not exist yet — proceed with fresh install.
    }

    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);
    echo "[OK] Schema created.\n";

    $seed = file_get_contents(__DIR__ . '/seed.sql');
    $pdo->exec($seed);
    echo "[OK] Seed data inserted.\n";

    $pdo->exec('USE `' . str_replace('`', '``', $db['name']) . '`');

    $passwords = [
        'admin@nextgenmedics.com'  => 'Admin@123',
        'teacher@nextgenmedics.com' => 'Teacher@123',
        'student@nextgenmedics.com' => 'Student@123',
    ];

    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
    foreach ($passwords as $email => $pass) {
        $stmt->execute([password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]), $email]);
    }
    echo "[OK] Default passwords set.\n\n";

    echo "Default accounts:\n";
    echo "  Admin:   admin@nextgenmedics.com / Admin@123\n";
    echo "  Teacher: teacher@nextgenmedics.com / Teacher@123\n";
    echo "  Student: student@nextgenmedics.com / Student@123\n\n";
    echo "Installation complete!\n";
} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
