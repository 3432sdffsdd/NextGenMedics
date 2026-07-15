<?php
require_once __DIR__ . '/../bootstrap.php';
new App\Core\Application();

$config = require __DIR__ . '/../config/config.php';
$ai = $config['ai'] ?? [];
$key = $ai['api_key'] ?? '';
$base = $ai['base_url'] ?? '';
$model = $ai['model'] ?? '';

echo 'Provider: ' . ($ai['provider'] ?? '?') . PHP_EOL;
echo 'Base URL: ' . $base . PHP_EOL;
echo 'Model: ' . $model . PHP_EOL;
echo 'Key set: ' . ($key !== '' ? 'yes (' . strlen($key) . ' chars, starts with ' . substr($key, 0, 4) . '...)' : 'NO') . PHP_EOL;

if ($key === '' && $base === '') {
    echo "ERROR: No AI config\n";
    exit(1);
}

$endpoint = rtrim($base, '/') . '/chat/completions';
$payload = json_encode([
    'model' => $model,
    'messages' => [
        ['role' => 'user', 'content' => 'Reply with exactly: OK'],
    ],
    'max_tokens' => 10,
]);

$ch = curl_init($endpoint);
$headers = ['Content-Type: application/json'];
if ($key !== '') {
    $headers[] = 'Authorization: Bearer ' . $key;
}
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 30,
]);
$raw = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$decoded = json_decode($raw ?: '', true);
echo "HTTP {$code}\n";
echo ($decoded['error']['message'] ?? ($decoded['choices'][0]['message']['content'] ?? substr($raw, 0, 200))) . "\n";
exit($code >= 400 ? 1 : 0);
