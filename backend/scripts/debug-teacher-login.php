<?php
require_once __DIR__ . '/../bootstrap.php';

// Simulate API bootstrap (loads .env)
new App\Core\Application();

$pdo = App\Core\Database::getConnection();
$user = $pdo->query("SELECT id, email, deleted_at, status FROM users WHERE email='teacher@nextgenmedics.com'")->fetch(PDO::FETCH_ASSOC);
echo "User row: " . json_encode($user) . PHP_EOL;

$repo = new App\Repositories\UserRepository();
$found = $repo->findByEmail('teacher@nextgenmedics.com');
echo "Repo findByEmail: " . ($found ? 'found id='.$found['id'] : 'NOT FOUND') . PHP_EOL;

if ($found) {
    echo "Password verify: " . (App\Helpers\PasswordHelper::verify('Teacher@123', $found['password']) ? 'yes' : 'no') . PHP_EOL;
}

$auth = new App\Services\AuthService(
    new App\Repositories\UserRepository(),
    new App\Repositories\AuthRepository(),
    new App\Core\Jwt(),
    new App\Repositories\ActivityLogRepository()
);
$result = $auth->login('teacher@nextgenmedics.com', 'Teacher@123', '127.0.0.1');
echo "Auth login: " . ($result && !isset($result['error']) ? 'OK token=' . substr($result['token'], 0, 20) . '...' : json_encode($result)) . PHP_EOL;
