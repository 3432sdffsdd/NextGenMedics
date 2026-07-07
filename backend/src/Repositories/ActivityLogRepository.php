<?php
namespace App\Repositories;

class ActivityLogRepository extends BaseRepository
{
    public function log(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $description = null, ?string $ip = null): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address) VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([$userId, $action, $entityType, $entityId, $description, $ip]);
    }

    public function audit(?int $userId, string $event, ?array $oldValues, ?array $newValues, ?string $ip = null): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO audit_logs (user_id, event, old_values, new_values, ip_address, user_agent) VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([
            $userId, $event,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ip, $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
