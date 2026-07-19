<?php
declare(strict_types=1);
$base = 'http://127.0.0.1:8080';
$pass = 0; $fail = 0;
function req(string $m, string $url, ?array $body = null, ?string $token = null): array {
    $ch = curl_init($url);
    $h = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) $h[] = 'Authorization: Bearer ' . $token;
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $m, CURLOPT_HTTPHEADER => $h, CURLOPT_TIMEOUT => 60]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'json' => json_decode($raw ?: '', true), 'raw' => (string) $raw];
}
function check(string $n, bool $ok, string $d = ''): void {
    global $pass, $fail;
    if ($ok) { $pass++; echo "[PASS] $n" . ($d ? " — $d" : '') . PHP_EOL; }
    else { $fail++; echo "[FAIL] $n" . ($d ? " — $d" : '') . PHP_EOL; }
}

echo "Student Performance smoke test\n" . str_repeat('=', 50) . PHP_EOL;

$login = req('POST', "$base/auth/login", ['email' => 'teacher@nextgenmedics.com', 'password' => 'Teacher@123']);
$token = $login['json']['token'] ?? $login['json']['data']['token'] ?? null;
check('Teacher login', (bool) $token, 'code=' . $login['code']);
if (!$token) {
    echo $login['raw'] . PHP_EOL;
    exit(1);
}

$list = req('GET', "$base/teacher/student-performance", null, $token);
check('List students', $list['code'] === 200, 'total=' . ($list['json']['data']['total'] ?? '?'));
$students = $list['json']['data']['students'] ?? [];
check('Has students array', is_array($students));

if ($students) {
    $sid = (int) $students[0]['id'];
    $detail = req('GET', "$base/teacher/student-performance/{$sid}", null, $token);
    check('Student detail', $detail['code'] === 200, 'student=' . ($detail['json']['data']['student']['name'] ?? '?'));
    $d = $detail['json']['data'] ?? [];
    check('Has overview', isset($d['overview']['quiz_avg_score'], $d['overview']['attendance_pct']));
    check('Has quizzes block', isset($d['quizzes']['summary']));
    check('Has attendance block', isset($d['attendance']['attendance_pct']));
    check('Has video analytics', isset($d['video_analytics']['summary'], $d['video_analytics']['videos']));
    check('Has assignments block', isset($d['assignments']['given'], $d['assignments']['submitted'], $d['assignments']['pending'], $d['assignments']['overdue']));
    check('Has mistakes block', isset($d['mistakes']['stats']));
} else {
    check('Student detail (skipped — no enrollments)', true, 'list empty');
}

$bad = req('GET', "$base/teacher/student-performance/99999999", null, $token);
check('Unknown student 404', $bad['code'] === 404);

$stuLogin = req('POST', "$base/auth/login", ['email' => 'student@nextgenmedics.com', 'password' => 'Student@123']);
$stuToken = $stuLogin['json']['token'] ?? $stuLogin['json']['data']['token'] ?? null;
if ($stuToken) {
    $denied = req('GET', "$base/teacher/student-performance", null, $stuToken);
    check('Student cannot access', $denied['code'] === 403 || $denied['code'] === 401, 'code=' . $denied['code']);
}

echo str_repeat('=', 50) . PHP_EOL;
echo "Result: $pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
