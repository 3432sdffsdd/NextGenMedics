<?php
/**
 * Smoke test for Goal-Based Study Planner (LMS content, PHP only).
 * php scripts/test-goal-study-planner.php
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

echo "Goal Study Planner — SMOKE TEST\n";
echo str_repeat('=', 64) . PHP_EOL;

$login = req('POST', "{$base}/auth/login", ['email' => 'student@nextgenmedics.com', 'password' => 'Student@123']);
$token = $login['json']['token'] ?? $login['json']['data']['token'] ?? null;
check('Student login', $login['code'] === 200 && (bool) $token);
if (!$token) {
    fwrite(STDERR, $login['raw'] . PHP_EOL);
    exit(1);
}

$r = req('POST', "{$base}/student/study-planner/reset", [], $token);
check('Reset plan', $r['code'] === 200, 'HTTP ' . $r['code']);

$cat = req('GET', "{$base}/student/study-planner/catalog", null, $token);
$subjects = $cat['json']['data']['subjects'] ?? [];
$goalTypes = $cat['json']['data']['goal_types'] ?? [];
check('Catalog OK', $cat['code'] === 200 && is_array($subjects), count($subjects) . ' subjects, ' . count($goalTypes) . ' goals');
check('Goal types present', count($goalTypes) >= 8);

$dashEmpty = req('GET', "{$base}/student/study-planner/dashboard", null, $token);
check('Dashboard empty', $dashEmpty['code'] === 200 && ($dashEmpty['json']['data']['has_plan'] ?? true) === false);

$selection = [];
if ($subjects) {
    $sub = $subjects[0];
    $selection[] = ['type' => 'subject', 'ref_id' => (int) $sub['course_id'], 'course_id' => (int) $sub['course_id']];
}

$payload = [
    'goal_type' => $selection ? 'selected_subjects' : 'full_syllabus',
    'goal_title' => 'Smoke Test Plan',
    'selection' => $selection,
    'start_date' => date('Y-m-d'),
    'target_date' => (new DateTimeImmutable('today'))->modify('+21 days')->format('Y-m-d'),
    'exam_date' => (new DateTimeImmutable('today'))->modify('+45 days')->format('Y-m-d'),
    'hours_per_day' => 3,
    'preferred_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
    'sessions_per_day' => 2,
    'preferred_time' => 'evening',
    'daily_mcq_target' => 40,
    'daily_flashcard_target' => 30,
    'revision_preference' => 'every_7_days',
];

$gen = req('POST', "{$base}/student/study-planner/generate", $payload, $token);
$hasPlan = ($gen['json']['data']['has_plan'] ?? false) === true;
$tasksToday = count($gen['json']['data']['dashboard']['today']['all_tasks'] ?? []);
check(
    'Generate plan',
    $gen['code'] === 200 && $hasPlan,
    'HTTP ' . $gen['code'] . ($hasPlan ? ", today tasks={$tasksToday}" : ' — ' . substr($gen['raw'], 0, 240))
);

$dash = req('GET', "{$base}/student/study-planner/dashboard", null, $token);
$d = $dash['json']['data']['dashboard'] ?? [];
check('Dashboard has plan', $dash['code'] === 200 && ($dash['json']['data']['has_plan'] ?? false));
check('Subject progress array', isset($d['subject_progress']) && is_array($d['subject_progress']));
check('Weekly review present', isset($d['weekly_review']));
check('Monthly report present', isset($d['monthly_report']));

$cal = req('GET', "{$base}/student/study-planner/calendar?month=" . date('Y-m'), null, $token);
$days = $cal['json']['data']['days'] ?? [];
check('Calendar month', $cal['code'] === 200 && is_array($days), count($days) . ' study days');

$day = req('GET', "{$base}/student/study-planner/day?date=" . date('Y-m-d'), null, $token);
$dayTasks = $day['json']['data']['tasks'] ?? $day['json']['data']['all_tasks'] ?? [];
check('Day details', $day['code'] === 200, 'tasks=' . count(is_array($dayTasks) ? $dayTasks : []));

$taskId = null;
foreach ($dayTasks as $t) {
    if (($t['status'] ?? '') === 'pending') {
        $taskId = (int) $t['id'];
        break;
    }
}
if (!$taskId) {
    foreach (($d['today']['all_tasks'] ?? []) as $t) {
        if (($t['status'] ?? '') === 'pending') {
            $taskId = (int) $t['id'];
            break;
        }
    }
}

if ($taskId) {
    $done = req('PATCH', "{$base}/student/study-planner/tasks/{$taskId}", ['status' => 'completed'], $token);
    check('Complete task', $done['code'] === 200, "task #{$taskId}");
} else {
    check('Complete task', true, 'skipped (no pending task today)');
}

$ch = req('POST', "{$base}/student/study-planner/challenges", [
    'title' => 'Smoke Challenge',
    'challenge_type' => 'deadline',
    'start_date' => date('Y-m-d'),
    'end_date' => (new DateTimeImmutable('today'))->modify('+14 days')->format('Y-m-d'),
    'target_value' => 20,
], $token);
check('Create challenge', $ch['code'] === 200, 'HTTP ' . $ch['code']);

$missed = req('POST', "{$base}/student/study-planner/missed", [], $token);
check('Handle missed', $missed['code'] === 200, 'HTTP ' . $missed['code']);

$exp = req('GET', "{$base}/student/study-planner/export", null, $token);
check('Export schedule', $exp['code'] === 200 && isset($exp['json']['data']['plan']));

echo str_repeat('=', 64) . PHP_EOL;
echo "Passed: {$pass}  Failed: {$fail}\n";
exit($fail > 0 ? 1 : 0);
