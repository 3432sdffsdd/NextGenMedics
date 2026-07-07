<?php
namespace App\AI;

/**
 * Provider for any OpenAI-compatible /chat/completions endpoint.
 * Covers OpenAI, Azure OpenAI, OpenRouter, Groq, Together, DeepSeek, Mistral,
 * and local runtimes (Ollama, LM Studio) that expose the same schema.
 */
class OpenAiCompatibleProvider implements AiProviderInterface
{
    private const MAX_RETRIES = 6;

    public function __construct(private array $config) {}

    public function isConfigured(): bool
    {
        if (!($this->config['enabled'] ?? true)) {
            return false;
        }
        // Local endpoints don't need a key; remote ones do.
        $base = $this->config['base_url'] ?? '';
        $isLocal = str_contains($base, '127.0.0.1') || str_contains($base, 'localhost');
        return !empty($base) && (!empty($this->config['api_key']) || $isLocal);
    }

    public function name(): string
    {
        return 'openai-compatible:' . ($this->config['model'] ?? 'unknown');
    }

    public function complete(string $system, string $user, array $options = []): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('AI provider is not configured. Set AI_API_KEY and AI_BASE_URL.');
        }

        $payload = [
            'model'       => $this->config['model'] ?? 'gpt-4o-mini',
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.4,
            'max_tokens'  => $options['max_tokens'] ?? $this->config['max_tokens'] ?? 4000,
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
        ];

        if (!empty($options['json'])) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $endpoint = ($this->config['base_url'] ?? '') . '/chat/completions';
        $lastError = 'AI request failed';

        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            if ($attempt > 0) {
                $this->sleepForRateLimit($lastError, $attempt);
            }

            [$code, $raw, $errno, $err] = $this->post($endpoint, $payload);

            if ($errno) {
                throw new \RuntimeException("AI request failed: {$err}");
            }

            $decoded = json_decode($raw ?: '', true);
            $msg = $decoded['error']['message'] ?? ('HTTP ' . $code);

            if ($code === 429 || ($code >= 400 && $this->isRateLimitMessage($msg))) {
                $lastError = $msg;
                if ($attempt < self::MAX_RETRIES) {
                    continue;
                }
                throw new \RuntimeException("AI API error: {$msg}");
            }

            if ($code >= 400) {
                throw new \RuntimeException("AI API error: {$msg}");
            }

            $content = $decoded['choices'][0]['message']['content'] ?? null;
            if ($content === null || $content === '') {
                throw new \RuntimeException('AI returned an empty response.');
            }

            return $content;
        }

        throw new \RuntimeException("AI API error: {$lastError}");
    }

    /** @return array{0:int,1:string,2:int,3:string} */
    private function post(string $endpoint, array $payload): array
    {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => $this->config['timeout'] ?? 120,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . ($this->config['api_key'] ?? ''),
                'HTTP-Referer: https://nextgenmedics.com',
                'X-Title: NextGen Medics LMS',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        $err   = curl_error($ch);
        $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$code, $raw ?: '', $errno, $err];
    }

    private function isRateLimitMessage(string $msg): bool
    {
        $lower = strtolower($msg);
        return str_contains($lower, 'rate limit')
            || str_contains($lower, 'tokens per minute')
            || str_contains($lower, 'too large for model');
    }

    private function sleepForRateLimit(string $message, int $attempt): void
    {
        $ms = 2000;
        if (preg_match('/try again in (\d+)ms/i', $message, $m)) {
            $ms = max(500, (int) $m[1] + 500);
        } else {
            // Back off: 2s, 4s, 6s…
            $ms = min(15000, 2000 * $attempt);
        }
        usleep($ms * 1000);
    }
}
