<?php
require_once __DIR__ . '/../bootstrap.php';
$app = new App\Core\Application();

// Use reflection to get container or just instantiate repos after Application loads .env
$users = new App\Repositories\UserRepository();
$courses = new App\Repositories\CourseRepository();
$auth = new App\Services\AuthService(
    $users,
    new App\Repositories\AuthRepository(),
    new App\Core\Jwt(),
    new App\Repositories\ActivityLogRepository()
);

$email = 'admin3@nextgenmedics.info';
$user = $users->findByEmail($email);
echo 'findByEmail: ' . ($user ? "id={$user['id']} role=" . ($user['role'] ?? '?') : 'NULL') . PHP_EOL;

$result = $auth->login($email, 'Admin@123', '127.0.0.1');
if ($result === null) {
    echo "login: FAILED (bad password or user)\n";
} elseif (isset($result['error'])) {
    echo 'login: ERROR ' . $result['error'] . PHP_EOL;
} else {
    echo 'login: OK user_id=' . ($result['user']['id'] ?? '?') . PHP_EOL;
}

$list = $users->listByRole('student', 1, 100);
echo 'students listed: ' . count($list['items'] ?? []) . PHP_EOL;
$listT = $users->listByRole('teacher', 1, 100);
echo 'teachers listed: ' . count($listT['items'] ?? []) . PHP_EOL;
$c = $courses->listAll(1, 50);
echo 'courses listed: ' . count($c['items'] ?? []) . PHP_EOL;
foreach (($c['items'] ?? []) as $row) {
    echo ' - ' . $row['id'] . ' ' . $row['title'] . PHP_EOL;
}
