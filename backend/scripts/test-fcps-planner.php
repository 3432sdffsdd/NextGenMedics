<?php
declare(strict_types=1);
$base = 'http://127.0.0.1:8080';
function req($m, $url, $body = null, $token = null) {
    $ch = curl_init($url);
    $h = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) $h[] = 'Authorization: Bearer ' . $token;
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $m, CURLOPT_HTTPHEADER => $h, CURLOPT_TIMEOUT => 60]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'json' => json_decode($raw ?: '', true), 'raw' => $raw];
}
$login = req('POST', "$base/auth/login", ['email' => 'student@nextgenmedics.com', 'password' => 'Student@123']);
$token = $login['json']['token'] ?? null;
if (!$token) { echo "LOGIN FAIL\n"; exit(1); }
req('POST', "$base/student/fcps-planner/reset", [], $token);
$exam = (new DateTimeImmutable('today'))->modify('+40 days')->format('Y-m-d');
$gen = req('POST', "$base/student/fcps-planner/generate", [
    'exam_date' => $exam,
    'start_date' => date('Y-m-d'),
    'hours_per_day' => 3,
    'preferred_days' => ['monday','tuesday','wednesday','thursday','friday','saturday'],
    'sessions_per_day' => 2,
    'preferred_time' => 'evening',
    'subjects_remaining' => ['Anatomy','Physiology','Pathology','Pharmacology'],
    'subjects_weak' => ['Pathology'],
    'subjects_strong' => ['Anatomy'],
    'subjects_completed' => [],
    'daily_mcq_target' => 40,
    'daily_flashcard_target' => 30,
    'revision_preference' => 'every_sunday',
], $token);
echo "Generate HTTP {$gen['code']}\n";
$plan = $gen['json']['data']['plan'] ?? null;
$order = $plan['subject_order'] ?? [];
echo 'Order: ' . implode(' > ', $order) . "\n";
echo ($gen['code'] === 200 && ($order[0] ?? '') === 'Pathology') ? "[PASS] weak first\n" : "[FAIL] weak first\n";
$dash = req('GET', "$base/student/fcps-planner/dashboard", null, $token);
echo (($dash['json']['data']['has_plan'] ?? false) ? '[PASS]' : '[FAIL]') . " dashboard\n";
$cal = req('GET', "$base/student/fcps-planner/calendar?month=" . date('Y-m'), null, $token);
echo (count($cal['json']['data']['days'] ?? []) > 0 ? '[PASS]' : '[FAIL]') . ' calendar days=' . count($cal['json']['data']['days'] ?? []) . "\n";
$day = req('GET', "$base/student/fcps-planner/day?date=" . date('Y-m-d'), null, $token);
$tasks = $day['json']['data']['tasks'] ?? [];
echo (count($tasks) > 0 ? '[PASS]' : '[FAIL]') . ' today tasks=' . count($tasks) . "\n";
if ($tasks) {
    $t = req('PATCH', "$base/student/fcps-planner/tasks/{$tasks[0]['id']}", ['status' => 'completed'], $token);
    echo ($t['code'] === 200 ? '[PASS]' : '[FAIL]') . " toggle\n";
}
$miss = req('POST', "$base/student/fcps-planner/missed", [], $token);
echo ($miss['code'] === 200 ? '[PASS]' : '[FAIL]') . " missed\n";
$exp = req('GET', "$base/student/fcps-planner/export", null, $token);
echo (($exp['code'] === 200 && !empty($exp['json']['data']['calendar'])) ? '[PASS]' : '[FAIL]') . " export\n";
echo "DONE\n";
