<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class CorsMiddleware
{
    public function handle(Request $request): bool
    {
        $config = require __DIR__ . '/../../config/config.php';
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        $allowed = in_array($origin, $config['cors']['allowed_origins'], true);

        // In development, allow local network origins (IP may change between networks)
        if (!$allowed && $config['app_env'] === 'development' && $origin !== '') {
            $allowed = (bool) preg_match(
                '#^https?://(localhost|127\.0\.0\.1|192\.168\.\d+\.\d+)(:\d+)?$#',
                $origin
            );
        }

        if ($allowed) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Vary: Origin');
        } elseif ($origin !== '' && preg_match('#^https?://([a-z0-9-]+\.)*nextgenmedics\.(info|com)(:\d+)?$#i', $origin)) {
            // Allow live NextGen Medics hosts even if APP_ENV misconfigured
            header("Access-Control-Allow-Origin: {$origin}");
            header('Vary: Origin');
            $allowed = true;
        } elseif ($config['app_env'] === 'development') {
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        return true;
    }
}
