<?php
/**
 * Safe schema updater — adds new tables/columns only.
 * Does NOT delete, truncate, or re-seed users (teachers/students stay intact).
 *
 * Run: php database/migrate.php
 */
require_once __DIR__ . '/../bootstrap.php';

$config = require __DIR__ . '/../config/config.php';
$db = $config['db'];

echo "NextGen Medics LMS — Safe migrations\n";
echo "====================================\n";
echo "Your existing teachers, students, and enrollments are NOT modified.\n\n";

try {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']);
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS schema_migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB'
    );

    $applied = $pdo->query('SELECT migration FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    $applied = array_flip($applied);

    $dir = __DIR__ . '/migrations';
    $files = glob($dir . '/*.sql') ?: [];
    sort($files);

    if (!$files) {
        echo "No migration files found.\n";
        exit(0);
    }

    $ran = 0;
    foreach ($files as $path) {
        $name = basename($path);
        if (isset($applied[$name])) {
            echo "[skip] {$name} (already applied)\n";
            continue;
        }

        // Idempotent guard for migration 004 (column may exist from manual run).
        if ($name === '004_material_uploader.sql') {
            $exists = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'lecture_resources'
                   AND COLUMN_NAME = 'uploaded_by'"
            )->fetchColumn();
            if ($exists) {
                $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (?)')->execute([$name]);
                echo "[skip] {$name} (column uploaded_by already exists)\n";
                continue;
            }
        }

        if ($name === '005_schedule_month_uploads.sql') {
            $exists = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'schedule_month_uploads'"
            )->fetchColumn();
            if ($exists) {
                $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (?)')->execute([$name]);
                echo "[skip] {$name} (table already exists)\n";
                continue;
            }
        }

        if ($name === '006_quiz_show_review.sql') {
            $exists = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'quizzes'
                   AND COLUMN_NAME = 'show_review'"
            )->fetchColumn();
            if ($exists) {
                $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (?)')->execute([$name]);
                echo "[skip] {$name} (column show_review already exists)\n";
                continue;
            }
        }

        if ($name === '007_premium_study_features.sql') {
            $exists = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'daily_challenge_sets'"
            )->fetchColumn();
            if ($exists) {
                $pdo->prepare('INSERT IGNORE INTO schema_migrations (migration) VALUES (?)')->execute([$name]);
                echo "[skip] {$name} (tables already exist)\n";
                continue;
            }
        }

        if ($name === '008_interactive_assignments.sql') {
            $exists = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'assignments'
                   AND COLUMN_NAME = 'assignment_type'"
            )->fetchColumn();
            if ($exists) {
                $pdo->prepare('INSERT IGNORE INTO schema_migrations (migration) VALUES (?)')->execute([$name]);
                echo "[skip] {$name} (columns already exist)\n";
                continue;
            }
        }

        if ($name === '009_assignment_multi_files.sql') {
            $exists = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'assignment_attachments'"
            )->fetchColumn();
            if ($exists) {
                $pdo->prepare('INSERT IGNORE INTO schema_migrations (migration) VALUES (?)')->execute([$name]);
                echo "[skip] {$name} (tables already exist)\n";
                continue;
            }
        }

        if ($name === '010_assignment_files_backfill.sql') {
            // Safe to re-run: INSERT uses NOT EXISTS guards.
        }

        $sql = file_get_contents($path);
        if ($sql === false || trim($sql) === '') {
            echo "[warn] {$name} is empty, skipping\n";
            continue;
        }

        echo "[run]  {$name} ... ";
        $pdo->exec($sql);
        $pdo->prepare('INSERT IGNORE INTO schema_migrations (migration) VALUES (?)')->execute([$name]);
        echo "OK\n";
        $ran++;
    }

    $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    echo "\nDone. Applied {$ran} new migration(s).\n";
    echo "Users in database: {$userCount} (unchanged by this script).\n";
} catch (PDOException $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    echo "No data was deleted. Fix the error and run migrate.php again.\n";
    exit(1);
}
