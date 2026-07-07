<?php
namespace App\Repositories;

class AuthRepository extends BaseRepository
{
    public function storeRefreshToken(int $userId, string $tokenHash, string $expiresAt): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $tokenHash, $expiresAt]);
    }

    public function findValidRefreshToken(string $tokenHash): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM refresh_tokens WHERE token_hash = ? AND revoked_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([$tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function revokeRefreshToken(string $tokenHash): void
    {
        $stmt = $this->db->prepare('UPDATE refresh_tokens SET revoked_at = NOW() WHERE token_hash = ?');
        $stmt->execute([$tokenHash]);
    }

    public function revokeAllUserTokens(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE refresh_tokens SET revoked_at = NOW() WHERE user_id = ? AND revoked_at IS NULL'
        );
        $stmt->execute([$userId]);
    }

    public function createPasswordReset(string $email, string $token, string $expiresAt): void
    {
        $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE email = ? AND used_at IS NULL')
            ->execute([$email]);

        $stmt = $this->db->prepare(
            'INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$email, $token, $expiresAt]);
    }

    public function findPasswordReset(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM password_resets WHERE token = ? AND used_at IS NULL AND expires_at > NOW()'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function markPasswordResetUsed(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }
}
