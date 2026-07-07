<?php
namespace App\Core;

/**
 * Lightweight HS256 JWT implementation (no external dependencies).
 */
class Jwt
{
    private array $config;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->config = $config['jwt'];
    }

    public function encode(array $payload, string $type = 'access'): string
    {
        $secret = $this->getSecret($type);
        $now = time();
        $expiry = $type === 'refresh'
            ? ($this->config['refresh_expiry'] ?? 604800)
            : $this->config['expiry'];

        $token = array_merge($payload, [
            'iss'  => $this->config['issuer'],
            'iat'  => $now,
            'exp'  => $now + $expiry,
            'type' => $type,
        ]);

        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $body = $this->base64UrlEncode(json_encode($token));
        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$body}", $secret, true)
        );

        return "{$header}.{$body}.{$signature}";
    }

    public function decode(string $token, string $type = 'access'): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $body, $signature] = $parts;
        $secret = $this->getSecret($type);

        $expected = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$body}", $secret, true)
        );

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($body), true);
        if (!is_array($payload)) {
            return null;
        }

        if (($payload['exp'] ?? 0) < time()) {
            return null;
        }

        if (($payload['type'] ?? 'access') !== $type) {
            return null;
        }

        return $payload;
    }

    private function getSecret(string $type): string
    {
        return $type === 'refresh'
            ? ($this->config['refresh_secret'] ?? $this->config['secret'] . '_refresh')
            : $this->config['secret'];
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
