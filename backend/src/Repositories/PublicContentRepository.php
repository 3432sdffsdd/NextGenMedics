<?php
namespace App\Repositories;

class PublicContentRepository extends BaseRepository
{
    public function getMentors(): array
    {
        return $this->db->query(
            'SELECT * FROM mentors WHERE is_active = 1 ORDER BY sort_order'
        )->fetchAll();
    }

    public function getTestimonials(): array
    {
        return $this->db->query(
            'SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order'
        )->fetchAll();
    }

    public function getFreeResources(?string $type = null): array
    {
        if ($type) {
            $stmt = $this->db->prepare(
                'SELECT * FROM free_resources WHERE is_active = 1 AND type = ? ORDER BY sort_order'
            );
            $stmt->execute([$type]);
            return $stmt->fetchAll();
        }
        return $this->db->query(
            'SELECT * FROM free_resources WHERE is_active = 1 ORDER BY sort_order'
        )->fetchAll();
    }

    public function saveContactMessage(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $data['name'], $data['email'], $data['phone'] ?? null,
            $data['subject'] ?? null, $data['message'],
        ]);
        return (int) $this->db->lastInsertId();
    }
}
