<?php
header('Content-Type: text/html; charset=utf-8');

$key = isset($_GET['key']) ? $_GET['key'] : (isset($_POST['key']) ? $_POST['key'] : '');
if ($key !== 'ngm-debug-2026') {
    http_response_code(403);
    echo 'Forbidden. Open with ?key=ngm-debug-2026';
    exit;
}

$email = '';
if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
}
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Show form
if ($email === '' || $password === '') {
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Login debug</title></head><body>';
    echo '<h1>Login debug (form)</h1>';
    echo '<p>If you see this page, PHP is working.</p>';
    echo '<form method="post" action="?key=ngm-debug-2026">';
    echo '<p>Email<br><input name="email" type="email" style="width:320px" required></p>';
    echo '<p>Password<br><input name="password" type="password" style="width:320px" required></p>';
    echo '<p><button type="submit">Test login</button></p>';
    echo '</form></body></html>';
    exit;
}

// Run checks after form submit
header('Content-Type: application/json; charset=utf-8');
$out = array('ok' => true, 'checks' => array());

try {
    require dirname(__DIR__) . '/bootstrap.php';
    $app = new App\Core\Application();
    $out['checks']['application_boot'] = 'OK';

    $cacheDir = dirname(__DIR__) . '/storage/cache';
    $rateDir = $cacheDir . '/rate_limit';
    if (!is_dir($rateDir)) {
        @mkdir($rateDir, 0755, true);
    }
    $out['checks']['rate_limit_dir_writable'] = is_dir($rateDir) && is_writable($rateDir);

    $jwt = new App\Core\Jwt();
    $out['checks']['jwt_encode'] = $jwt->encode(array('sub' => 1, 'role' => 'student')) ? 'OK' : 'FAIL';

    $auth = new App\Services\AuthService(
        new App\Repositories\UserRepository(),
        new App\Repositories\AuthRepository(),
        $jwt,
        new App\Repositories\ActivityLogRepository()
    );
    $result = $auth->login($email, $password, '127.0.0.1');
    if (!$result) {
        $out['checks']['auth_login'] = 'INVALID_CREDENTIALS';
    } elseif (isset($result['error'])) {
        $out['checks']['auth_login'] = 'ACCOUNT_ERROR: ' . $result['error'];
    } else {
        $out['checks']['auth_login'] = 'OK';
        $out['checks']['auth_user_id'] = isset($result['user']['id']) ? $result['user']['id'] : null;
        $out['checks']['auth_role'] = isset($result['user']['role']) ? $result['user']['role'] : null;
    }
} catch (Throwable $e) {
    $out['ok'] = false;
    $out['checks']['fatal'] = $e->getMessage();
    $out['checks']['fatal_file'] = $e->getFile() . ':' . $e->getLine();
}

echo json_encode($out, JSON_PRETTY_PRINT);
