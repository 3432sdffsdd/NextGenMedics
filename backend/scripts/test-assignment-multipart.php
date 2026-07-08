<?php
$base = 'http://127.0.0.1:8080';

function api(string $method, string $path, ?string $token = null, ?string $multipart = null, ?string $boundary = null): array
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
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($body, true);
    return ['code' => $code, 'body' => $body, 'json' => $json, 'json_ok' => json_last_error() === JSON_ERROR_NONE];
}

$login = api('POST', '/auth/login', null, json_encode(['email' => 'admin@nextgenmedics.com', 'password' => 'Admin@123']));
// login needs JSON - use simple curl
$ch = curl_init($base . '/auth/login');
curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode(['email' => 'admin@nextgenmedics.com', 'password' => 'Admin@123'])]);
$loginBody = curl_exec($ch);
curl_close($ch);
$loginJson = json_decode($loginBody, true);
$token = $loginJson['token'] ?? '';

$pdf = __DIR__ . '/../storage/uploads/test-sample.pdf';
if (!is_file($pdf)) {
    file_put_contents($pdf, "%PDF-1.4 test\n");
}

$boundary = '----test' . uniqid();
$fields = [
    'course_id' => '3',
    'title' => 'Multipart test ' . date('H:i:s'),
    'description' => 'desc',
    'due_date' => '2026-12-31T18:00',
    'max_marks' => '100',
    'assignment_type' => 'file',
    'external_url' => '',
];
$body = '';
foreach ($fields as $k => $v) {
    $body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$k}\"\r\n\r\n{$v}\r\n";
}
$body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"files\"; filename=\"test.pdf\"\r\nContent-Type: application/pdf\r\n\r\n" . file_get_contents($pdf) . "\r\n";
$body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"file_titles\"\r\n\r\nSample PDF\r\n";
$body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"expected_files\"\r\n\r\n1\r\n";
$body .= "--{$boundary}--\r\n";

$result = api('POST', '/assignments', $token, $body, $boundary);
echo 'HTTP ' . $result['code'] . PHP_EOL;
echo 'JSON parse ok: ' . ($result['json_ok'] ? 'yes' : 'no') . PHP_EOL;
echo 'Has warning HTML: ' . (str_contains($result['body'], '<b>Warning</b>') ? 'yes' : 'no') . PHP_EOL;
echo 'Success: ' . (($result['json']['success'] ?? false) ? 'yes' : 'no') . PHP_EOL;
echo 'Message: ' . ($result['json']['message'] ?? 'n/a') . PHP_EOL;
echo 'Assignment id: ' . ($result['json']['data']['id'] ?? 'n/a') . PHP_EOL;
