<?php
namespace App\Repositories;

class BookmarkRepository extends BaseRepository
{
    public function toggle(int $studentId, string $type, int $contentId, ?string $note = null): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM content_bookmarks WHERE student_id = ? AND content_type = ? AND content_id = ?'
        );
        $stmt->execute([$studentId, $type, $contentId]);
        $id = $stmt->fetchColumn();

        if ($id) {
            $del = $this->db->prepare('DELETE FROM content_bookmarks WHERE id = ?');
            $del->execute([$id]);
            return false; // removed
        }
        $ins = $this->db->prepare(
            'INSERT INTO content_bookmarks (student_id, content_type, content_id, note) VALUES (?,?,?,?)'
        );
        $ins->execute([$studentId, $type, $contentId, $note]);
        return true; // added
    }

    public function listByType(int $studentId, string $type): array
    {
        $stmt = $this->db->prepare(
            'SELECT content_id, note, created_at FROM content_bookmarks
             WHERE student_id = ? AND content_type = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$studentId, $type]);
        return $stmt->fetchAll();
    }

    public function bookmarkedIds(int $studentId, string $type): array
    {
        return array_map('intval', array_column($this->listByType($studentId, $type), 'content_id'));
    }

    // ── Highlights ─────────────────────────────────────────────

    public function addHighlight(int $studentId, int $lectureId, ?string $section, string $text, string $color = 'yellow'): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO note_highlights (student_id, lecture_id, section, highlighted_text, color) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([$studentId, $lectureId, $section, $text, $color]);
        return (int) $this->db->lastInsertId();
    }

    public function listHighlights(int $studentId, int $lectureId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM note_highlights WHERE student_id = ? AND lecture_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$studentId, $lectureId]);
        return $stmt->fetchAll();
    }

    public function deleteHighlight(int $studentId, int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM note_highlights WHERE id = ? AND student_id = ?');
        $stmt->execute([$id, $studentId]);
    }
}
