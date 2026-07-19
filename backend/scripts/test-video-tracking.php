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

echo "Video Tracking smoke test\n" . str_repeat('=', 50) . PHP_EOL;
$login = req('POST', "$base/auth/login", ['email' => 'student@nextgenmedics.com', 'password' => 'Student@123']);
$token = $login['json']['token'] ?? $login['json']['data']['token'] ?? null;
check('Login', (bool) $token);
if (!$token) exit(1);

$dash = req('GET', "$base/student/video-tracking/dashboard", null, $token);
check('Student dashboard', $dash['code'] === 200, 'videos=' . count($dash['json']['data']['videos'] ?? []));

$videos = $dash['json']['data']['videos'] ?? [];
$rid = (int) ($videos[0]['resource_id'] ?? 0);
if (!$rid) {
    $mat = req('GET', "$base/student/study-material", null, $token);
    $rid = (int) (($mat['json']['data']['videos'][0]['id'] ?? 0));
}
check('Has video resource', $rid > 0, "id=$rid");

if ($rid) {
    $track = req('POST', "$base/student/video-tracking/track", [
        'resource_id' => $rid,
        'event_type' => 'started',
        'position' => 0,
        'duration' => 600,
        'watched_delta' => 0,
        'playback_speed' => 1,
        'client' => ['browser' => 'Test', 'os' => 'Windows', 'device_type' => 'desktop'],
    ], $token);
    check('Track started', $track['code'] === 200);

    $hb = req('POST', "$base/student/video-tracking/track", [
        'resource_id' => $rid,
        'event_type' => 'heartbeat',
        'position' => 120,
        'duration' => 600,
        'watched_delta' => 10,
        'segment_start' => 110,
        'playback_speed' => 1.25,
    ], $token);
    $pct = (float) ($hb['json']['data']['progress']['completion_pct'] ?? 0);
    check('Track heartbeat', $hb['code'] === 200 && $pct > 0, "pct=$pct");

    $resume = req('GET', "$base/student/video-tracking/resume?resource_id=$rid", null, $token);
    check('Resume info', $resume['code'] === 200);

    // Simulate near-complete
    for ($i = 0; $i < 10; $i++) {
        req('POST', "$base/student/video-tracking/track", [
            'resource_id' => $rid,
            'event_type' => 'heartbeat',
            'position' => 50 + $i * 55,
            'duration' => 600,
            'watched_delta' => 12,
            'segment_start' => 40 + $i * 55,
            'playback_speed' => 1,
        ], $token);
    }
    $end = req('POST', "$base/student/video-tracking/track", [
        'resource_id' => $rid,
        'event_type' => 'ended',
        'position' => 600,
        'duration' => 600,
        'watched_delta' => 10,
        'segment_start' => 580,
        'playback_speed' => 1,
    ], $token);
    $st = $end['json']['data']['progress']['status'] ?? '';
    check('Can reach completed/watching', $end['code'] === 200 && in_array($st, ['completed', 'watching'], true), "status=$st");
}

echo str_repeat('=', 50) . PHP_EOL . "Passed: $pass Failed: $fail\n";
exit($fail > 0 ? 1 : 0);
