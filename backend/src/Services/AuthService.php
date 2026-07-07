<?php
namespace App\Services;

use App\Core\Jwt;
use App\Helpers\PasswordHelper;
use App\Repositories\ActivityLogRepository;
use App\Repositories\AuthRepository;
use App\Repositories\UserRepository;

class AuthService
{
    public function __construct(
        private UserRepository $users,
        private AuthRepository $auth,
        private Jwt $jwt,
        private ActivityLogRepository $activityLog
    ) {}

    public function login(string $email, string $password, string $ip): ?array
    {
        $user = $this->users->findByEmail($email);
        if (!$user || !PasswordHelper::verify($password, $user['password'])) {
            return null;
        }
        if ($user['status'] !== 'active') {
            return ['error' => 'Account is suspended or inactive'];
        }

        $this->users->updateLastLogin($user['id']);
        $tokens = $this->issueTokens($user);
        unset($user['password']);

        $this->activityLog->log($user['id'], 'login', 'user', $user['id'], 'User logged in', $ip);

        return [
            'token'         => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'expires_in'    => 3600,
            'user'          => $this->formatUser($user),
        ];
    }

    public function refresh(string $refreshToken): ?array
    {
        $payload = $this->jwt->decode($refreshToken, 'refresh');
        if (!$payload || !isset($payload['sub'])) {
            return null;
        }

        $tokenHash = hash('sha256', $refreshToken);
        $stored = $this->auth->findValidRefreshToken($tokenHash);
        if (!$stored) {
            return null;
        }

        $user = $this->users->findById((int) $payload['sub']);
        if (!$user || $user['status'] !== 'active') {
            return null;
        }

        $this->auth->revokeRefreshToken($tokenHash);
        return $this->issueTokens($user);
    }

    public function logout(?int $userId, ?string $refreshToken, string $ip): void
    {
        if ($refreshToken) {
            $this->auth->revokeRefreshToken(hash('sha256', $refreshToken));
        }
        if ($userId) {
            $this->activityLog->log($userId, 'logout', 'user', $userId, 'User logged out', $ip);
        }
    }

    public function changePassword(int $userId, string $current, string $new): bool|string
    {
        $user = $this->users->findById($userId);
        if (!$user || !PasswordHelper::verify($current, $user['password'])) {
            return 'Current password is incorrect';
        }
        $this->users->update($userId, ['password' => PasswordHelper::hash($new)]);
        $this->auth->revokeAllUserTokens($userId);
        return true;
    }

    public function forgotPassword(string $email): ?string
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $this->auth->createPasswordReset($email, hash('sha256', $token), $expires);

        // In production, send email with reset link containing $token
        return $token;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $reset = $this->auth->findPasswordReset(hash('sha256', $token));
        if (!$reset) {
            return false;
        }

        $user = $this->users->findByEmail($reset['email']);
        if (!$user) {
            return false;
        }

        $this->users->update($user['id'], ['password' => PasswordHelper::hash($newPassword)]);
        $this->auth->markPasswordResetUsed($reset['id']);
        $this->auth->revokeAllUserTokens($user['id']);
        return true;
    }

    private function issueTokens(array $user): array
    {
        $accessToken = $this->jwt->encode(['sub' => $user['id'], 'role' => $user['role']]);
        $refreshToken = $this->jwt->encode(['sub' => $user['id']], 'refresh');

        $this->auth->storeRefreshToken(
            $user['id'],
            hash('sha256', $refreshToken),
            date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 7)
        );

        return ['access_token' => $accessToken, 'refresh_token' => $refreshToken];
    }

    private function formatUser(array $user): array
    {
        return [
            'id'         => (int) $user['id'],
            'username'   => $user['username'],
            'email'      => $user['email'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'full_name'  => $user['first_name'] . ' ' . $user['last_name'],
            'role'       => $user['role'],
            'avatar'     => $user['avatar'],
        ];
    }
}
