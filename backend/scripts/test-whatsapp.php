<?php
/**
 * Test WhatsApp configuration
 * Usage: php scripts/test-whatsapp.php 03218902931
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$phone = $argv[1] ?? '';
if (!$phone) {
    echo "Usage: php test-whatsapp.php <phone>\n";
    echo "Example: php test-whatsapp.php 03218902931\n";
    exit(1);
}

// Reload .env into getenv
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value, " \t\n\r\0\x0B\"'"));
    }
}

$config = require __DIR__ . '/../config/config.php';
$wa = $config['whatsapp'] ?? [];

echo "WhatsApp enabled: " . ($wa['enabled'] ? 'yes' : 'no') . "\n";
echo "Phone Number ID: " . ($wa['phone_number_id'] ?: '(empty)') . "\n";
echo "Token set: " . (!empty($wa['access_token']) ? 'yes (' . strlen($wa['access_token']) . ' chars)' : 'no') . "\n";
echo "Sending test to: {$phone}\n\n";

$service = new \App\Services\WhatsAppService();
$message = "✅ NextGen Medics LMS\n\nWhatsApp is configured correctly! You will receive class reminders 10 minutes before each lecture.";
$ok = $service->send($phone, $message);

if ($ok) {
    echo "SUCCESS: Message sent! Check your WhatsApp.\n";
    exit(0);
}

echo "FAILED: Message not sent via API.\n";
echo "Check backend/storage/logs/whatsapp.log for details.\n";
echo "\nCommon fixes:\n";
echo "- Add your phone as a test recipient in Meta > WhatsApp > API Setup\n";
echo "- Token may have expired (temporary tokens last ~24 hours)\n";
echo "- WHATSAPP_ENABLED must be true in .env\n";
exit(1);
