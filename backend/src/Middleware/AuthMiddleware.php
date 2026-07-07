<?php
namespace App\Middleware;

use App\Core\Jwt;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\UserRepository;

class AuthMiddleware
{
    public function __construct(
        private Jwt $jwt,
        private UserRepository $users
    ) {}

    public function handle(Request $request): bool
    {
        $token = $request->bearerToken();
        if (!$token) {
            Response::error('Unauthorized', 401);
            return false;
        }

        $payload = $this->jwt->decode($token);
        if (!$payload || !isset($payload['sub'])) {
            Response::error('Invalid or expired token', 401);
            return false;
        }

        $user = $this->users->findById((int) $payload['sub']);
        if (!$user || $user['status'] !== 'active') {
            Response::error('Account inactive or not found', 401);
            return false;
        }

        unset($user['password']);
        $request->setUser($user);
        return true;
    }
}
