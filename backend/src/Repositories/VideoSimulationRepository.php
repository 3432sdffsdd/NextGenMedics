<?php
namespace App\Repositories;

class VideoSimulationRepository extends BaseRepository
{
    public function upsert(int $lectureId, ?int $courseId, ?int $userId, array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO video_simulations
                (lecture_id, course_id, title, teaching_script, voice_over, scenes, timeline,
                 diagrams, subtitles, camera_guidance, duration_seconds, status, source, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                title = VALUES(title), teaching_script = VALUES(teaching_script),
                voice_over = VALUES(voice_over), scenes = VALUES(scenes), timeline = VALUES(timeline),
                diagrams = VALUES(diagrams), subtitles = VALUES(subtitles),
                camera_guidance = VALUES(camera_guidance), duration_seconds = VALUES(duration_seconds),
                status = \'draft\', updated_at = NOW()'
        );
        $stmt->execute([
            $lectureId, $courseId,
            $data['title'] ?? null,
            $data['teaching_script'] ?? null,
            $data['voice_over'] ?? null,
            json_encode($data['scenes'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($data['timeline'] ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($data['diagrams'] ?? [], JSON_UNESCAPED_UNICODE),
            $data['subtitles'] ?? null,
            $data['camera_guidance'] ?? null,
            (int) ($data['duration_seconds'] ?? 0),
            'draft', 'ai', $userId,
        ]);
    }

    public function findByLecture(int $lectureId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM video_simulations WHERE lecture_id = ?');
        $stmt->execute([$lectureId]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function setStatusForLecture(int $lectureId, string $status): void
    {
        $this->db->prepare('UPDATE video_simulations SET status = ? WHERE lecture_id = ?')
            ->execute([$status, $lectureId]);
    }

    private function hydrate(array $row): array
    {
        foreach (['scenes', 'timeline', 'diagrams'] as $f) {
            $row[$f] = !empty($row[$f]) ? (json_decode($row[$f], true) ?: []) : [];
        }
        return $row;
    }
}
