<?php
namespace App\Services;

use App\Helpers\SlugHelper;
use App\Repositories\ActivityLogRepository;
use App\Repositories\CourseRepository;

class CourseService
{
    public function __construct(
        private CourseRepository $courses,
        private ActivityLogRepository $activityLog
    ) {}

    public function create(array $data, int $userId, string $ip): array
    {
        $teacherIds = $this->normalizeTeacherIds($data);
        $slug = SlugHelper::unique($data['title'], fn($s) => $this->courses->slugExists($s));
        $data['slug'] = $slug;
        $data['created_by'] = $userId;

        $id = $this->courses->create($data);
        if ($teacherIds) {
            $this->courses->setTeachers($id, $teacherIds);
        }

        $this->activityLog->log($userId, 'create_course', 'course', $id, "Created course: {$data['title']}", $ip);
        return $this->courses->findById($id);
    }

    public function update(int $id, array $data, int $userId, string $ip): ?array
    {
        $hasTeacherIds = array_key_exists('teacher_ids', $data);
        $teacherIds = $this->normalizeTeacherIds($data);

        // teacher_ids is not a real column; never pass it to the generic UPDATE.
        unset($data['teacher_ids']);

        if (isset($data['title'])) {
            $data['slug'] = SlugHelper::unique($data['title'], fn($s) => $this->courses->slugExists($s, $id));
        }

        if ($data) {
            $this->courses->update($id, $data);
        }
        if ($hasTeacherIds) {
            $this->courses->setTeachers($id, $teacherIds);
        }
        $this->activityLog->log($userId, 'update_course', 'course', $id, 'Course updated', $ip);
        return $this->courses->findById($id);
    }

    /**
     * Resolve teacher ids from either a teacher_ids array or a single teacher_id.
     * Capped at 2 teachers per course.
     *
     * @return int[]
     */
    private function normalizeTeacherIds(array $data): array
    {
        $ids = [];
        if (!empty($data['teacher_ids']) && is_array($data['teacher_ids'])) {
            $ids = $data['teacher_ids'];
        } elseif (!empty($data['teacher_id'])) {
            $ids = [$data['teacher_id']];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        return array_slice($ids, 0, 2);
    }

    public function canAccess(int $courseId, array $user): bool
    {
        return match ($user['role']) {
            'admin'   => true,
            'teacher' => $this->courses->isTeacherAssigned($courseId, (int) $user['id']),
            'student' => $this->courses->isStudentEnrolled($courseId, (int) $user['id']),
            default   => false,
        };
    }

    /** Students may stream videos freely; download requires enrollment flag. */
    public function canDownloadVideos(int $courseId, array $user): bool
    {
        return match ($user['role'] ?? '') {
            'admin', 'teacher' => $this->canAccess($courseId, $user),
            'student' => $this->courses->studentCanDownloadVideos($courseId, (int) $user['id']),
            default => false,
        };
    }
}
