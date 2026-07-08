<?php
$base = 'http://127.0.0.1:8080';

$ch = curl_init($base . '/auth/login');
curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode(['email' => 'admin@nextgenmedics.com', 'password' => 'Admin@123'])]);
$loginBody = curl_exec($ch);
curl_close($ch);
$token = json_decode($loginBody, true)['token'] ?? '';

$pdf1 = __DIR__ . '/../storage/uploads/test-a.pdf';
$pdf2 = __DIR__ . '/../storage/uploads/test-b.pdf';
file_put_contents($pdf1, "%PDF-1.4 file A\n");
file_put_contents($pdf2, "%PDF-1.4 file B\n");

$boundary = '----multi' . uniqid();
$fields = [
    'course_id' => '3',
    'title' => 'Multi file test ' . date('H:i:s'),
    'due_date' => '2026-12-31T18:00',
    'assignment_type' => 'file',
    'status' => 'published',
];
$body = '';
foreach ($fields as $k => $v) {
    $body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$k}\"\r\n\r\n{$v}\r\n";
}
foreach ([[$pdf1, 'Doc A'], [$pdf2, 'Doc B']] as [$path, $title]) {
    $name = basename($path);
    $body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"files[]\"; filename=\"{$name}\"\r\nContent-Type: application/pdf\r\n\r\n" . file_get_contents($path) . "\r\n";
    $body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"file_titles[]\"\r\n\r\n{$title}\r\n";
}
$body .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"expected_files\"\r\n\r\n2\r\n";
$body .= "--{$boundary}--\r\n";

$ch = curl_init($base . '/assignments');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Content-Type: multipart/form-data; boundary=' . $boundary],
    CURLOPT_POSTFIELDS => $body,
]);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
$count = count($data['data']['attachments'] ?? []);
echo "HTTP {$code}\n";
echo "Attachments saved: {$count}\n";
foreach ($data['data']['attachments'] ?? [] as $a) {
    echo " - {$a['title']} ({$a['original_filename']})\n";
}
exit($count === 2 ? 0 : 1);
