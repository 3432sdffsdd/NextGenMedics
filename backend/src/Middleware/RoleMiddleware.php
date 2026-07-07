<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class RoleMiddleware
{
    public function __construct(private array $roles) {}

    public function handle(Request $request): bool
    {
        $user = $request->user();
        if (!$user || !in_array($user['role'], $this->roles, true)) {
            Response::error('Forbidden', 403);
            return false;
        }
        return true;
    }

    public static function admin(): self
    {
        return new self(['admin']);
    }

    public static function teacher(): self
    {
        return new self(['teacher']);
    }

    public static function student(): self
    {
        return new self(['student']);
    }

    public static function adminOrTeacher(): self
    {
        return new self(['admin', 'teacher']);
    }

    public static function any(): self
    {
        return new self(['admin', 'teacher', 'student']);
    }
}
