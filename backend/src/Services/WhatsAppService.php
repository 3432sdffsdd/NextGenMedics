<?php
namespace App\Services;

class WhatsAppService
{
    public function isEnabled(): bool
    {
        $config = $this->config();
        return !empty($config['enabled']) && !empty($config['access_token']) && !empty($config['phone_number_id']);
    }

    public function send(string $phone, string $message): bool
    {
        $phone = $this->normalizePhone($phone);
        if (!$phone) {
            return false;
        }

        if (!$this->isEnabled()) {
            $this->log($phone, $message);
            return false;
        }

        $config = $this->config();
        $url = "https://graph.facebook.com/v21.0/{$config['phone_number_id']}/messages";

        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to'                => $phone,
            'type'              => 'text',
            'text'              => ['preview_url' => false, 'body' => $message],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $config['access_token'],
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return true;
        }

        $this->log($phone, $message, 'API error ' . $code . ': ' . $response);
        return false;
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (!$digits) {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '92' . substr($digits, 1);
        }
        if (!str_starts_with($digits, '92') && strlen($digits) === 10) {
            $digits = '92' . $digits;
        }
        return $digits;
    }

    private function config(): array
    {
        $config = require __DIR__ . '/../../config/config.php';
        return $config['whatsapp'] ?? [];
    }

    private function log(string $phone, string $message, string $note = 'WhatsApp not configured — logged only'): void
    {
        $dir = __DIR__ . '/../../storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $line = date('Y-m-d H:i:s') . " [{$note}] To: {$phone}\n{$message}\n---\n";
        file_put_contents($dir . '/whatsapp.log', $line, FILE_APPEND);
    }
}
