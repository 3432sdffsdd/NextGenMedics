<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class RateLimitMiddleware
{
    private string $storageDir;

    public function __construct(private int $maxRequests = 60, private int $windowSeconds = 60)
    {
        $this->storageDir = __DIR__ . '/../../storage/cache/rate_limit';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function handle(Request $request): bool
    {
        // Skip rate limit for media streaming and document preview
        $uri = $request->uri();
        if (str_contains($uri, '/media')) {
            return true;
        }

        $key = md5($request->ip() . ':' . $request->uri());
        $file = $this->storageDir . '/' . $key . '.json';
        $now = time();

        $data = ['count' => 0, 'start' => $now];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: $data;
            if ($now - $data['start'] > $this->windowSeconds) {
                $data = ['count' => 0, 'start' => $now];
            }
        }

        $data['count']++;
        file_put_contents($file, json_encode($data));

        if ($data['count'] > $this->maxRequests) {
            Response::error('Too many requests. Please try again later.', 429);
            return false;
        }

        return true;
    }
}
