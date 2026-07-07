<?php
namespace App\Repositories;

class UserRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.slug AS role, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.deleted_at IS NULL'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.slug AS role, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? AND u.deleted_at IS NULL'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.slug AS role FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.username = ? AND u.deleted_at IS NULL'
        );
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (role_id, username, email, password, first_name, last_name, phone, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['role_id'],
            $data['username'],
            $data['email'],
            $data['password'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $this->db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET deleted_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function listByRole(string $roleSlug, int $page, int $perPage, ?string $search = null): array
    {
        $params = [$roleSlug];
        $where = 'r.slug = ? AND u.deleted_at IS NULL';

        if ($search) {
            $where .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term]);
        }

        $sql = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.phone, u.status, u.last_login_at, u.created_at, r.slug AS role
                FROM users u JOIN roles r ON r.id = u.role_id
                WHERE {$where} ORDER BY u.created_at DESC";

        return $this->paginate($sql, $params, $page, $perPage);
    }

    public function countByRole(string $roleSlug): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id
             WHERE r.slug = ? AND u.deleted_at IS NULL AND u.status = "active"'
        );
        $stmt->execute([$roleSlug]);
        return (int) $stmt->fetchColumn();
    }

    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function getRoleId(string $slug): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE slug = ?');
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    public function getAllRoles(): array
    {
        return $this->db->query('SELECT * FROM roles ORDER BY id')->fetchAll();
    }
}
