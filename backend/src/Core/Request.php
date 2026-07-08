<?php
namespace App\Core;

class Request
{
    private array $params = [];
    private ?array $user = null;
    /** @var array|null Cached JSON body (php://input is read once). */
    private ?array $jsonBody = null;
    private bool $jsonBodyLoaded = false;

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $base = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($base !== '/' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        return '/' . trim($uri, '/');
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function allQuery(): array
    {
        return $_GET;
    }

    public function body(): array
    {
        if ($this->jsonBodyLoaded) {
            return $this->jsonBody ?? [];
        }
        $this->jsonBodyLoaded = true;

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw ?: '{}', true);
            $this->jsonBody = is_array($decoded) ? $decoded : [];
            return $this->jsonBody;
        }

        $this->jsonBody = $_POST;
        return $this->jsonBody;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body()[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';
        if (preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function setUser(?array $user): void
    {
        $this->user = $user;
    }

    public function user(): ?array
    {
        return $this->user;
    }

    public function userId(): ?int
    {
        return $this->user['id'] ?? null;
    }

    public function userRole(): ?string
    {
        return $this->user['role'] ?? null;
    }

    public function file(string $key): ?array
    {
        foreach ([$key, "{$key}[]"] as $candidate) {
            $f = $_FILES[$candidate] ?? null;
            if (!$f) {
                continue;
            }
            if (!is_array($f['name'] ?? null)) {
                if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                return $f;
            }
            foreach ($f['name'] as $i => $name) {
                if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    return [
                        'name'     => $name,
                        'type'     => $f['type'][$i] ?? '',
                        'tmp_name' => $f['tmp_name'][$i] ?? '',
                        'error'    => $f['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                        'size'     => $f['size'][$i] ?? 0,
                    ];
                }
            }
        }
        return null;
    }

    /** @return array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}> */
    public function files(string $key): array
    {
        foreach ([$key, "{$key}[]"] as $candidate) {
            $normalized = $this->normalizeUploadedFiles($_FILES[$candidate] ?? null);
            if ($normalized) {
                return $normalized;
            }
        }
        return [];
    }

    /** @return array<int, string> */
    public function arrayInput(string $key): array
    {
        $body = $this->body();
        foreach ([$key, "{$key}[]"] as $candidate) {
            if (!array_key_exists($candidate, $body)) {
                continue;
            }
            $value = $body[$candidate];
            if (is_array($value)) {
                return array_values(array_map(static fn($v) => (string) $v, $value));
            }
            if ($value !== null && $value !== '') {
                return [(string) $value];
            }
        }
        return [];
    }

    /** @param array<string,mixed>|null $f */
    private function normalizeUploadedFiles(?array $f): array
    {
        if (!$f || !isset($f['name'])) {
            return [];
        }
        if (!is_array($f['name'])) {
            if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                return [];
            }
            return [$f];
        }
        $out = [];
        foreach ($f['name'] as $i => $name) {
            if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $out[] = [
                'name'     => $name,
                'type'     => $f['type'][$i] ?? '',
                'tmp_name' => $f['tmp_name'][$i] ?? '',
                'error'    => $f['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $f['size'][$i] ?? 0,
            ];
        }
        return $out;
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function page(): int
    {
        return max(1, (int) $this->query('page', 1));
    }

    public function perPage(int $default = 20): int
    {
        return min(100, max(1, (int) $this->query('per_page', $default)));
    }
}
