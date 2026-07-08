<?php
$base = 'http://127.0.0.1:8080';

function api(string $method, string $path, ?array $json = null, ?string $token = null, ?string $multipart = null, ?string $boundary = null): array
{
    global $base;
    $ch = curl_init($base . $path);
    $headers = [];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    if ($multipart !== null) {
        $headers[] = 'Content-Type: multipart/form-data; boundary=' . $boundary;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart);
    } elseif ($json !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'json' => json_decode($body, true)];
}

$login = api('POST', '/auth/login', ['email' => 'admin@nextgenmedics.com', 'password' => 'Admin@123']);
echo "LOGIN {$login['code']}\n{$login['body']}\n\n";

$token = $login['json']['data']['token'] ?? $login['json']['token'] ?? '';
if ($token === '') {
    fwrite(STDERR, "No token\n");
    exit(1);
}

$list = api('GET', '/assignments?course_id=3', null, $token);
echo "LIST {$list['code']}\n{$list['body']}\n\n";

$boundary = '----test' . uniqid();
$fields = [
    'course_id' => '3',
    'title' => 'Test API Assignment ' . date('H:i:s'),
    'due_date' => '2026-12-31T23:59',
    'assignment_type' => 'file',
    'status' => 'published',
];
$body = '';
foreach ($fields as $k => $v) {
    $body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$k}\"\r\n\r\n{$v}\r\n";
}
$body .= "--{$boundary}--\r\n";

$create = api('POST', '/assignments', null, $token, $body, $boundary);
echo "CREATE {$create['code']}\n{$create['body']}\n\n";

$list2 = api('GET', '/assignments?course_id=3', null, $token);
echo "LIST AFTER {$list2['code']}\n{$list2['body']}\n";
