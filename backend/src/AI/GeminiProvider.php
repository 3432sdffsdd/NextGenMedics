<?php
namespace App\AI;

/**
 * Native Google Gemini provider (generateContent REST API).
 *
 * Talks to https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent.
 * Implements the metered interface so the generation engine can log tokens,
 * latency and retries, and estimate cost.
 *
 * The API key is supplied through configuration (config/config.php → 'gemini'),
 * which itself reads it ONLY from the GEMINI_API_KEY environment variable /
 * User Secrets / .env. The key is never hardcoded here.
 */
class GeminiProvider implements MeteredAiProviderInterface
{
    /**
     * @param array $config The 'gemini' config block: api_key, model, base_url,
     *                       temperature, top_p, top_k, max_output_tokens, timeout,
     *                       max_retries, pricing.
     */
    public function __construct(private array $config) {}

    public function isConfigured(): bool
    {
        return trim((string) ($this->config['api_key'] ?? '')) !== ''
            && trim((string) ($this->config['base_url'] ?? '')) !== '';
    }

    public function name(): string
    {
        return 'gemini:' . ($this->config['model'] ?? 'unknown');
    }

    public function model(): string
    {
        return (string) ($this->config['model'] ?? 'gemini-3.5-flash');
    }

    public function complete(string $system, string $user, array $options = []): string
    {
        return $this->completeWithMeta($system, $user, $options)['text'];
    }

    public function completeWithMeta(string $system, string $user, array $options = []): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException(
                'Gemini is not configured. Set GEMINI_API_KEY in the environment or backend/.env.'
            );
        }

        $model    = (string) ($options['model'] ?? $this->config['model'] ?? 'gemini-3.5-flash');
        $timeout  = (int) ($options['timeout'] ?? $this->config['timeout'] ?? 120);
        $maxRetry = max(0, (int) ($this->config['max_retries'] ?? 3));

        $generationConfig = [
            'temperature'     => (float) ($options['temperature'] ?? $this->config['temperature'] ?? 0.4),
            'topP'            => (float) ($options['top_p'] ?? $this->config['top_p'] ?? 0.95),
            'topK'            => (int) ($options['top_k'] ?? $this->config['top_k'] ?? 40),
            'maxOutputTokens' => (int) ($options['max_output_tokens'] ?? $this->config['max_output_tokens'] ?? 8192),
        ];
        if (!empty($options['json'])) {
            $generationConfig['responseMimeType'] = 'application/json';
            // Keep thinking minimal for JSON stages — thought summaries often
            // pollute / truncate structured output on Gemini 3.x.
            $generationConfig['thinkingConfig'] = ['thinkingLevel' => 'minimal'];
        }

        $payload = [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents'          => [['role' => 'user', 'parts' => [['text' => $user]]]],
            'generationConfig'  => $generationConfig,
            // Do not let default safety blocks silently drop medical content.
            'safetySettings'    => $this->safetySettings(),
        ];

        $endpoint = sprintf(
            '%s/models/%s:generateContent',
            rtrim((string) $this->config['base_url'], '/'),
            rawurlencode($model)
        );

        $lastError = 'Gemini request failed';
        $retries   = 0;
        $start     = microtime(true);

        for ($attempt = 0; $attempt <= $maxRetry; $attempt++) {
            if ($attempt > 0) {
                $retries = $attempt;
                $this->backoff($attempt, $lastError);
            }

            [$code, $raw, $errno, $err] = $this->post($endpoint, $payload, $timeout);

            if ($errno) {
                // Transport error (timeout, DNS, TLS) — retry.
                $lastError = $err !== '' ? $err : 'network error';
                if ($attempt < $maxRetry) {
                    continue;
                }
                throw new \RuntimeException("Gemini request failed: {$lastError}");
            }

            $decoded = json_decode($raw ?: '', true);

            if ($code === 429 || $code === 500 || $code === 503) {
                // Rate limit / transient server error — retry.
                $lastError = $decoded['error']['message'] ?? ('HTTP ' . $code);
                if ($attempt < $maxRetry) {
                    continue;
                }
                throw new \RuntimeException("Gemini API error: {$lastError}");
            }

            if ($code >= 400) {
                $msg = $decoded['error']['message'] ?? ('HTTP ' . $code);
                // Some model/API combos reject thinkingConfig — retry once without it.
                if (
                    !empty($options['json'])
                    && isset($payload['generationConfig']['thinkingConfig'])
                    && stripos($msg, 'thinking') !== false
                ) {
                    unset($payload['generationConfig']['thinkingConfig']);
                    if ($attempt < $maxRetry) {
                        $lastError = $msg;
                        continue;
                    }
                }
                throw new \RuntimeException("Gemini API error: {$msg}");
            }

            $text = $this->extractText($decoded);
            $finish = $decoded['candidates'][0]['finishReason'] ?? null;
            if (($text === null || $text === '') && $finish === 'SAFETY') {
                throw new \RuntimeException('Gemini blocked the response for safety. Try rephrasing the lecture text.');
            }
            if ($text === null || $text === '') {
                // Empty candidate (e.g. MAX_TOKENS with no partial) — retry once more.
                $lastError = 'empty response' . ($finish ? " (finishReason={$finish})" : '');
                if ($attempt < $maxRetry) {
                    continue;
                }
                throw new \RuntimeException('Gemini returned an empty response.');
            }

            $usage = $decoded['usageMetadata'] ?? [];
            $prompt = (int) ($usage['promptTokenCount'] ?? 0);
            $completion = (int) ($usage['candidatesTokenCount'] ?? 0);
            $total = (int) ($usage['totalTokenCount'] ?? ($prompt + $completion));

            return [
                'text'              => $text,
                'prompt_tokens'     => $prompt,
                'completion_tokens' => $completion,
                'total_tokens'      => $total,
                'latency_ms'        => (int) round((microtime(true) - $start) * 1000),
                'retries'           => $retries,
                'model'             => $model,
            ];
        }

        throw new \RuntimeException("Gemini API error: {$lastError}");
    }

    public function estimateCost(int $promptTokens, int $completionTokens): float
    {
        $pricing = $this->config['pricing'] ?? [];
        $model   = $this->model();

        $rate = $pricing['*'] ?? ['input' => 0.0, 'output' => 0.0];
        foreach ($pricing as $prefix => $r) {
            if ($prefix !== '*' && str_starts_with($model, $prefix)) {
                $rate = $r;
                break;
            }
        }

        $in  = ($promptTokens / 1_000_000) * (float) ($rate['input'] ?? 0);
        $out = ($completionTokens / 1_000_000) * (float) ($rate['output'] ?? 0);
        return round($in + $out, 6);
    }

    // ── internals ──────────────────────────────────────────────

    /** @return array{0:int,1:string,2:int,3:string} [httpCode, body, curlErrno, curlError] */
    private function post(string $endpoint, array $payload, int $timeout): array
    {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                // Key sent as a header rather than in the URL/logs.
                'x-goog-api-key: ' . ($this->config['api_key'] ?? ''),
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        $err   = curl_error($ch);
        $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$code, is_string($raw) ? $raw : '', $errno, $err];
    }

    /** Join non-thought text parts of the first candidate. */
    private function extractText(?array $decoded): ?string
    {
        $parts = $decoded['candidates'][0]['content']['parts'] ?? null;
        if (!is_array($parts)) {
            return null;
        }
        $text = '';
        foreach ($parts as $part) {
            // Gemini 3.x may return thought summaries alongside the answer.
            // Mixing them into the text breaks JSON parsing.
            if (!empty($part['thought'])) {
                continue;
            }
            if (isset($part['text'])) {
                $text .= (string) $part['text'];
            }
        }
        // Fallback: if only thought parts existed, try any text (last resort).
        if ($text === '') {
            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    $text .= (string) $part['text'];
                }
            }
        }
        return $text !== '' ? $text : null;
    }

    private function backoff(int $attempt, string $message): void
    {
        $ms = 1500;
        if (preg_match('/retry.*?(\d+)\s*s/i', $message, $m)) {
            $ms = max(1000, ((int) $m[1]) * 1000);
        } else {
            // 1.5s, 3s, 6s …
            $ms = min(15000, 1500 * (2 ** ($attempt - 1)));
        }
        usleep($ms * 1000);
    }

    /** @return list<array{category:string,threshold:string}> */
    private function safetySettings(): array
    {
        $categories = [
            'HARM_CATEGORY_HARASSMENT',
            'HARM_CATEGORY_HATE_SPEECH',
            'HARM_CATEGORY_SEXUALLY_EXPLICIT',
            'HARM_CATEGORY_DANGEROUS_CONTENT',
        ];
        return array_map(
            fn ($c) => ['category' => $c, 'threshold' => 'BLOCK_ONLY_HIGH'],
            $categories
        );
    }
}
