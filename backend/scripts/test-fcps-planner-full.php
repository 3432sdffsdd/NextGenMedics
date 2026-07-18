<?php
/**
 * Full functional test of FCPS Study Planner (PHP-only).
 * php scripts/test-fcps-planner-full.php
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

echo "FCPS Study Planner — FULL FUNCTIONAL TEST\n";
echo str_repeat('=', 64) . PHP_EOL;

// Confirm AI routes gone
$aiGone = req('GET', "{$base}/student/ai-planner/dashboard");
check('AI planner route removed', $aiGone['code'] === 401 || $aiGone['code'] === 404 || ($aiGone['code'] === 200 && empty($aiGone['json']['success'])));
// With auth it should 404 from router
$login = req('POST', "{$base}/auth/login", ['email' => 'student@nextgenmedics.com', 'password' => 'Student@123']);
$token = $login['json']['token'] ?? null;
check('Student login', $login['code'] === 200 && (bool) $token);
if (!$token) {
    exit(1);
}
$aiAuth = req('GET', "{$base}/student/ai-planner/dashboard", null, $token);
check('AI planner 404 when authenticated', $aiAuth['code'] === 404, 'HTTP ' . $aiAuth['code']);

// Reset clean
$r = req('POST', "{$base}/student/fcps-planner/reset", [], $token);
check('Reset plan', $r['code'] === 200);

$empty = req('GET', "{$base}/student/fcps-planner/dashboard", null, $token);
check('Dashboard empty (no plan)', $empty['code'] === 200 && ($empty['json']['data']['has_plan'] ?? true) === false);

$basePayload = [
    'exam_date' => (new DateTimeImmutable('today'))->modify('+45 days')->format('Y-m-d'),
    'start_date' => date('Y-m-d'),
    'hours_per_day' => 4,
    'preferred_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
    'sessions_per_day' => 3,
    'preferred_time' => 'evening',
    'subjects_completed' => ['Community Medicine'],
    'subjects_remaining' => ['Anatomy', 'Physiology', 'Pathology', 'Pharmacology', 'Biochemistry', 'Microbiology'],
    'subjects_weak' => ['Pathology', 'Pharmacology'],
    'subjects_strong' => ['Anatomy'],
    'daily_mcq_target' => 50,
    'daily_flashcard_target' => 35,
    'revision_preference' => 'every_5_days',
];

$gen = req('POST', "{$base}/student/fcps-planner/generate", $basePayload, $token);
$plan = $gen['json']['data']['plan'] ?? null;
$dash = $gen['json']['data']['dashboard'] ?? null;
check('Generate (all inputs)', $gen['code'] === 200 && !empty($plan['id']), 'HTTP ' . $gen['code']);
check('Weak subjects first', ($plan['subject_order'][0] ?? '') === 'Pathology', implode(' > ', $plan['subject_order'] ?? []));
check('Strong later in order', in_array('Anatomy', $plan['subject_order'] ?? [], true)
    && array_search('Anatomy', $plan['subject_order'], true) > array_search('Pathology', $plan['subject_order'], true));
check('Sessions=3 saved', (int) ($plan['sessions_per_day'] ?? 0) === 3);
check('Evening saved', ($plan['preferred_time'] ?? '') === 'evening');
check('Revision every_5_days', ($plan['revision_preference'] ?? '') === 'every_5_days');
check('MCQ/FC targets', (int) $plan['daily_mcq_target'] === 50 && (int) $plan['daily_flashcard_target'] === 35);
check('Dashboard after generate', is_array($dash) && isset($dash['exam_countdown_days']));
check('Countdown > 0', (int) ($dash['exam_countdown_days'] ?? 0) > 0, 'days=' . ($dash['exam_countdown_days'] ?? '?'));
check('Weekly goals present', !empty($plan['weekly_goals']));
check('Monthly goals present', !empty($plan['monthly_goals']));
check('Strategy notes present', !empty($plan['strategy_notes']));

// GET plan
$get = req('GET', "{$base}/student/fcps-planner", null, $token);
check('GET plan', $get['code'] === 200 && !empty($get['json']['data']['plan']['id']));

// Dashboard
$d2 = req('GET', "{$base}/student/fcps-planner/dashboard", null, $token);
$d = $d2['json']['data']['dashboard'] ?? [];
check('Dashboard has_plan', ($d2['json']['data']['has_plan'] ?? false) === true);
check('Today study/mcq/fc/revision keys', isset($d['today']['study'], $d['today']['mcqs'], $d['today']['flashcards'], $d['today']['revision']));
check('Completed/pending/missed counters', isset($d['completed_tasks'], $d['pending_tasks'], $d['missed_tasks']));
check('Streak + completion%', isset($d['study_streak'], $d['completion_pct']));
check('Weekly/monthly progress', isset($d['weekly_progress'], $d['monthly_progress']));
check('Stats: hours/topics/mcqs', isset($d['study_hours_remaining'], $d['topics_remaining'], $d['mcqs_completed']));
check('Badges array', isset($d['badges']) && is_array($d['badges']));

// Calendar
$month = date('Y-m');
$cal = req('GET', "{$base}/student/fcps-planner/calendar?month={$month}", null, $token);
$days = $cal['json']['data']['days'] ?? [];
check('Calendar month', $cal['code'] === 200 && count($days) > 0, 'days=' . count($days));
$sample = $days[0]['plan_date'] ?? date('Y-m-d');
check('Calendar day has topics/mcq/status', !empty($days[0]['topics']) && isset($days[0]['mcq_target'], $days[0]['day_status']));

// Day detail
$day = req('GET', "{$base}/student/fcps-planner/day?date={$sample}", null, $token);
$tasks = $day['json']['data']['tasks'] ?? [];
$sessions = $day['json']['data']['sessions'] ?? [];
check('Day detail tasks', $day['code'] === 200 && count($tasks) >= 3, 'tasks=' . count($tasks));
check('Day sessions (3)', count($sessions) === 3, 'sessions=' . count($sessions));
$types = array_unique(array_column($tasks, 'task_type'));
check('Task types study+mcq+flashcard', count(array_intersect($types, ['study', 'mcq', 'flashcard'])) >= 3, implode(',', $types));

// Mark completed
$taskId = (int) $tasks[0]['id'];
$done = req('PATCH', "{$base}/student/fcps-planner/tasks/{$taskId}", ['status' => 'completed'], $token);
check('Mark task completed', $done['code'] === 200);
$pending = req('PATCH', "{$base}/student/fcps-planner/tasks/{$taskId}", ['status' => 'pending'], $token);
check('Mark task pending again', $pending['code'] === 200);

// Skip
$skipId = (int) ($tasks[1]['id'] ?? $taskId);
$skip = req('PATCH', "{$base}/student/fcps-planner/tasks/{$skipId}", ['status' => 'skipped'], $token);
$skipStatus = null;
foreach ($skip['json']['data']['tasks'] ?? [] as $t) {
    if ((int) $t['id'] === $skipId) {
        $skipStatus = $t['status'];
    }
}
check('Skip task', $skip['code'] === 200 && $skipStatus === 'skipped', 'status=' . ($skipStatus ?? '?'));

// Reschedule to tomorrow
$tomorrow = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
$moveId = (int) ($tasks[2]['id'] ?? $taskId);
$res = req('POST', "{$base}/student/fcps-planner/tasks/{$moveId}/reschedule", ['date' => $tomorrow], $token);
check('Reschedule task', $res['code'] === 200, 'HTTP ' . $res['code']);
$tm = req('GET', "{$base}/student/fcps-planner/day?date={$tomorrow}", null, $token);
$movedOk = false;
foreach ($tm['json']['data']['tasks'] ?? [] as $t) {
    if ((int) $t['id'] === $moveId) {
        $movedOk = true;
    }
}
check('Rescheduled task on new date', $movedOk);

// Reset today
$rt = req('POST', "{$base}/student/fcps-planner/reset-today", [], $token);
check('Reset today', $rt['code'] === 200);

// Search
$search = req('GET', "{$base}/student/fcps-planner/search?q=Study", null, $token);
check('Search tasks', $search['code'] === 200 && count($search['json']['data']['items'] ?? []) > 0, 'hits=' . count($search['json']['data']['items'] ?? []));

// Missed redistribution (inject via DB)
require __DIR__ . '/../bootstrap.php';
new App\Core\Application();
$pdo = App\Core\Database::getConnection();
$planId = (int) $plan['id'];
$yesterday = (new DateTimeImmutable('yesterday'))->format('Y-m-d');
$pdo->prepare('DELETE FROM fcps_study_tasks WHERE plan_id=? AND plan_date=?')->execute([$planId, $yesterday]);
$pdo->prepare('DELETE FROM fcps_study_plan_days WHERE plan_id=? AND plan_date=?')->execute([$planId, $yesterday]);
$pdo->prepare(
    "INSERT INTO fcps_study_plan_days (plan_id, plan_date, is_study_day, day_status, topics, mcq_target, flashcard_target, completed_pct)
     VALUES (?,?,1,'upcoming',?,40,30,0)"
)->execute([$planId, $yesterday, json_encode(['Pathology'])]);
$yday = (int) $pdo->lastInsertId();
for ($i = 1; $i <= 3; $i++) {
    $pdo->prepare(
        "INSERT INTO fcps_study_tasks (plan_id, day_id, plan_date, task_type, subject, title, session_number, status, sort_order)
         VALUES (?,?,?,'study','Pathology',?,1,'pending',?)"
    )->execute([$planId, $yday, $yesterday, "Forced missed {$i}", $i]);
}
$missed = req('POST', "{$base}/student/fcps-planner/missed", [], $token);
$mr = $missed['json']['data']['reschedule'] ?? [];
check('Missed handler', $missed['code'] === 200 && (int) ($mr['moved_count'] ?? 0) >= 3, json_encode($mr));
check('Missed explanation', !empty($missed['json']['data']['explanation']));
$future = (int) $pdo->query(
    "SELECT COUNT(*) FROM fcps_study_tasks WHERE plan_id={$planId} AND title LIKE 'Forced missed%' AND plan_date >= CURDATE()"
)->fetchColumn();
check('Missed tasks redistributed forward', $future >= 3, 'future=' . $future);

// Export JSON
$exp = req('GET', "{$base}/student/fcps-planner/export", null, $token);
check('Export JSON', $exp['code'] === 200 && count($exp['json']['data']['calendar'] ?? []) > 0, 'days=' . count($exp['json']['data']['calendar'] ?? []));

// Export CSV
$csv = req('GET', "{$base}/student/fcps-planner/export/csv", null, $token);
check('Export CSV', $csv['code'] === 200 && str_contains($csv['raw'], 'Date,Status'), 'len=' . strlen($csv['raw']));

// Export print HTML
$print = req('GET', "{$base}/student/fcps-planner/export/print", null, $token);
check('Export print HTML', $print['code'] === 200 && str_contains($print['raw'], 'FCPS'), 'len=' . strlen($print['raw']));

// Regenerate
$regen = req('POST', "{$base}/student/fcps-planner/regenerate", [], $token);
check('Regenerate', $regen['code'] === 200 && !empty($regen['json']['data']['plan']['id']));

// Alternate revision styles + sessions + times
$alts = [
    ['every_3_days', 1, 'morning'],
    ['every_7_days', 2, 'afternoon'],
    ['every_sunday', 1, 'night'],
    ['after_each_subject', 3, 'evening'],
];
foreach ($alts as [$rev, $sess, $time]) {
    req('POST', "{$base}/student/fcps-planner/reset", [], $token);
    $p = $basePayload;
    $p['revision_preference'] = $rev;
    $p['sessions_per_day'] = $sess;
    $p['preferred_time'] = $time;
    $p['preferred_days'] = ['monday', 'wednesday', 'friday', 'sunday'];
    $g = req('POST', "{$base}/student/fcps-planner/generate", $p, $token);
    $pl = $g['json']['data']['plan'] ?? [];
    check(
        "Generate alt: {$rev} / {$sess} sess / {$time}",
        $g['code'] === 200
        && ($pl['revision_preference'] ?? '') === $rev
        && (int) ($pl['sessions_per_day'] ?? 0) === $sess
        && ($pl['preferred_time'] ?? '') === $time,
        'HTTP ' . $g['code']
    );
}

// Validation errors
req('POST', "{$base}/student/fcps-planner/reset", [], $token);
$bad = req('POST', "{$base}/student/fcps-planner/generate", ['exam_date' => '', 'subjects_remaining' => []], $token);
check('Validation rejects empty exam', $bad['code'] === 422 || $bad['code'] === 500);

// Frontend route
$fe = @file_get_contents('http://localhost:5173/student/fcps-planner');
check('Frontend page loads', is_string($fe) && $fe !== '', 'bytes=' . strlen((string) $fe));

echo str_repeat('=', 64) . PHP_EOL;
echo "Passed: {$pass}  Failed: {$fail}\n";
exit($fail > 0 ? 1 : 0);
