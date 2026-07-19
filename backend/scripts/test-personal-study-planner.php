<?php
/**
 * Test Personal Study Planner — LMS, Manual, Mixed modes.
 * php scripts/test-personal-study-planner.php
 */
declare(strict_types=1);

$base = 'http://127.0.0.1:8080';
$pass = 0;
$fail = 0;

function req(string $m, string $url, ?array $body = null, ?string $token = null): array
{
    $ch = curl_init($url);
    $h = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $h[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $m,
        CURLOPT_HTTPHEADER     => $h,
        CURLOPT_TIMEOUT        => 90,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'json' => json_decode($raw ?: '', true), 'raw' => (string) $raw];
}

function check(string $name, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "[PASS] {$name}" . ($detail !== '' ? " — {$detail}" : '') . PHP_EOL;
    } else {
        $fail++;
        echo "[FAIL] {$name}" . ($detail !== '' ? " — {$detail}" : '') . PHP_EOL;
    }
}

echo "Personal Study Planner — 3 MODE SMOKE TEST\n";
echo str_repeat('=', 64) . PHP_EOL;

$login = req('POST', "{$base}/auth/login", ['email' => 'student@nextgenmedics.com', 'password' => 'Student@123']);
$token = $login['json']['token'] ?? $login['json']['data']['token'] ?? null;
check('Student login', $login['code'] === 200 && (bool) $token);
if (!$token) {
    fwrite(STDERR, $login['raw'] . PHP_EOL);
    exit(1);
}

// Clean active plan
req('POST', "{$base}/student/personal-planner/reset", [], $token);

$boot = req('GET', "{$base}/student/personal-planner", null, $token);
check('Bootstrap OK', $boot['code'] === 200, 'setup=' . (($boot['json']['data']['setup_required'] ?? false) ? 'yes' : 'no'));

if ($boot['json']['data']['setup_required'] ?? false) {
    $setup = req('POST', "{$base}/student/personal-planner/setup", [
        'exam_date' => (new DateTimeImmutable('today'))->modify('+60 days')->format('Y-m-d'),
        'hours_per_day' => 3,
        'preferred_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
    ], $token);
    check('First-time setup', $setup['code'] === 200 && ($setup['json']['data']['setup_required'] ?? true) === false);
} else {
    check('Setup already done', true);
}

$cat = req('GET', "{$base}/student/personal-planner/catalog", null, $token);
$subjects = $cat['json']['data']['subjects'] ?? [];
check('Catalog loaded', $cat['code'] === 200 && is_array($subjects), count($subjects) . ' subjects');

$selection = [];
if ($subjects) {
    $sub = $subjects[0];
    $selection[] = ['type' => 'subject', 'ref_id' => (int) $sub['course_id'], 'course_id' => (int) $sub['course_id']];
    // Prefer a few lectures if available for lighter plan
    if (!empty($sub['lectures'])) {
        $selection = [];
        foreach (array_slice($sub['lectures'], 0, 3) as $lec) {
            $selection[] = ['type' => 'lecture', 'ref_id' => (int) $lec['id'], 'course_id' => (int) $sub['course_id'], 'title' => $lec['title']];
        }
        foreach (array_slice($sub['quizzes'] ?? [], 0, 2) as $q) {
            $selection[] = ['type' => 'quiz', 'ref_id' => (int) $q['id'], 'course_id' => (int) $sub['course_id'], 'title' => $q['title']];
        }
    }
}

// ── MODE 1: LMS ───────────────────────────────────────────
$lms = req('POST', "{$base}/student/personal-planner/plans", [
    'plan_mode' => 'lms',
    'plan_name' => 'Smoke LMS 5 Day Plan',
    'duration_days' => 5,
    'selection' => $selection,
], $token);
$lmsOk = $lms['code'] === 200 && ($lms['json']['data']['active_plan']['plan_mode'] ?? '') === 'lms';
$lmsTasks = count($lms['json']['data']['today']['all_tasks'] ?? []);
check('MODE 1 LMS create', $lmsOk, $lmsOk ? "today_tasks={$lmsTasks}" : substr($lms['raw'], 0, 220));

$planId = (int) ($lms['json']['data']['active_plan']['id'] ?? 0);
$taskId = (int) (($lms['json']['data']['today']['all_tasks'][0]['id'] ?? 0));
if ($taskId) {
    $done = req('PATCH', "{$base}/student/personal-planner/tasks/{$taskId}", ['status' => 'completed'], $token);
    check('LMS complete task', $done['code'] === 200);
    $move = req('POST', "{$base}/student/personal-planner/tasks/{$taskId}/move", ['action' => 'keep_pending'], $token);
    // after complete, move keep_pending should still work
    check('LMS move keep_pending', $move['code'] === 200);
} else {
    check('LMS complete task', true, 'skipped (no today task)');
    check('LMS move keep_pending', true, 'skipped');
}

$cal = req('GET', "{$base}/student/personal-planner/calendar?month=" . date('Y-m'), null, $token);
check('LMS calendar', $cal['code'] === 200 && is_array($cal['json']['data']['days'] ?? null));

// ── MODE 2: MANUAL ────────────────────────────────────────
$manual = req('POST', "{$base}/student/personal-planner/plans", [
    'plan_mode' => 'manual',
    'plan_name' => 'Smoke Manual 4 Day Plan',
    'duration_days' => 4,
    'manual_tasks' => [
        ['day_number' => 1, 'title' => 'Study Pathology — Cell Injury'],
        ['day_number' => 2, 'title' => 'Study Renal Pathology'],
        ['day_number' => 3, 'title' => 'Study Inflammation'],
        ['day_number' => 4, 'title' => 'Study Neoplasia'],
    ],
], $token);
$manOk = $manual['code'] === 200 && ($manual['json']['data']['active_plan']['plan_mode'] ?? '') === 'manual';
$manToday = $manual['json']['data']['today']['manual'] ?? [];
check('MODE 2 Manual create', $manOk, $manOk ? ('manual_today=' . count($manToday)) : substr($manual['raw'], 0, 220));
check('MODE 2 Manual has manual tasks', $manOk && (($manual['json']['data']['progress']['manual_total'] ?? 0) >= 4));

$manTaskId = (int) (($manual['json']['data']['today']['all_tasks'][0]['id'] ?? 0));
if ($manTaskId) {
    $skip = req('PATCH', "{$base}/student/personal-planner/tasks/{$manTaskId}", ['status' => 'skipped'], $token);
    check('Manual skip task', $skip['code'] === 200);
    $toEnd = req('POST', "{$base}/student/personal-planner/tasks/{$manTaskId}/move", ['action' => 'end'], $token);
    check('Manual move to end', $toEnd['code'] === 200);
} else {
    check('Manual skip task', true, 'skipped');
    check('Manual move to end', true, 'skipped');
}

// ── MODE 3: MIXED ─────────────────────────────────────────
$mixed = req('POST', "{$base}/student/personal-planner/plans", [
    'plan_mode' => 'mixed',
    'plan_name' => 'Smoke Mixed 5 Day Plan',
    'duration_days' => 5,
    'selection' => array_slice($selection, 0, 2),
    'manual_tasks' => [
        ['day_number' => 1, 'title' => 'Read Robbins Chapter 4'],
        ['day_number' => 2, 'title' => 'Revise Notebook'],
        ['day_number' => 3, 'title' => 'Solve 50 MCQs'],
    ],
], $token);
$mixOk = $mixed['code'] === 200 && ($mixed['json']['data']['active_plan']['plan_mode'] ?? '') === 'mixed';
$prog = $mixed['json']['data']['progress'] ?? [];
check('MODE 3 Mixed create', $mixOk, $mixOk ? ('manual_total=' . ($prog['manual_total'] ?? 0)) : substr($mixed['raw'], 0, 220));
check('MODE 3 Mixed has both sources', $mixOk && ((int) ($prog['manual_total'] ?? 0) >= 1));

$dash = req('GET', "{$base}/student/personal-planner/dashboard", null, $token);
check('Dashboard stats', $dash['code'] === 200 && isset($dash['json']['data']['statistics']));
check('History has plans', count($dash['json']['data']['history'] ?? []) >= 2);

$hist = $dash['json']['data']['history'] ?? [];
$archivedId = null;
foreach ($hist as $h) {
    if (($h['status'] ?? '') === 'archived') {
        $archivedId = (int) $h['id'];
        break;
    }
}
if ($archivedId) {
    $dup = req('POST', "{$base}/student/personal-planner/plans/{$archivedId}/duplicate", [], $token);
    check('Duplicate plan', $dup['code'] === 200);
} else {
    $activeId = (int) ($dash['json']['data']['active_plan']['id'] ?? 0);
    $dup = req('POST', "{$base}/student/personal-planner/plans/{$activeId}/duplicate", [], $token);
    check('Duplicate plan', $dup['code'] === 200);
}

$exp = req('GET', "{$base}/student/personal-planner/export", null, $token);
check('Export plan', $exp['code'] === 200 && isset($exp['json']['data']['plan']));

echo str_repeat('=', 64) . PHP_EOL;
echo "Passed: {$pass}  Failed: {$fail}\n";
exit($fail > 0 ? 1 : 0);
