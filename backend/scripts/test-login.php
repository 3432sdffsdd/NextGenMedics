<?php
$ch = curl_init('http://127.0.0.1:8080/auth/login');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode(['email' => 'admin@nextgenmedics.com', 'password' => 'Admin@123']),
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);
echo "HTTP {$code}\n";
if ($err) echo "CURL: {$err}\n";
echo $body . "\n";
