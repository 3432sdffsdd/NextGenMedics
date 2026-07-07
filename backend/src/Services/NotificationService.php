<?php
namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;

class NotificationService
{
    public function __construct(
        private NotificationRepository $notifications,
        private MailService $mail,
        private UserRepository $users
    ) {}

    public function notify(int $userId, string $type, string $title, string $message, ?array $data = null, bool $sendEmail = true): void
    {
        $id = $this->notifications->create($userId, $type, $title, $message, $data);

        if (!$sendEmail) {
            return;
        }

        $user = $this->users->findById($userId);
        if ($user && !empty($user['email'])) {
            $sent = $this->mail->sendNotification($user['email'], $title, $message);
            if ($sent) {
                $this->notifications->markEmailSent($id);
            }
        }
    }

    public function notifyMany(array $userIds, string $type, string $title, string $message, ?array $data = null, bool $sendEmail = true): void
    {
        foreach (array_unique($userIds) as $userId) {
            $this->notify((int) $userId, $type, $title, $message, $data, $sendEmail);
        }
    }

    /** Notify all teachers assigned to a course (skips excludeUserId). */
    public function notifyCourseTeachers(
        int $courseId,
        array $teacherIds,
        int $excludeUserId,
        string $type,
        string $title,
        string $message,
        ?array $data = null
    ): void {
        $data = array_merge(['course_id' => $courseId], $data ?? []);
        foreach (array_unique($teacherIds) as $teacherId) {
            if ((int) $teacherId === $excludeUserId) {
                continue;
            }
            $this->notify((int) $teacherId, $type, $title, $message, $data, false);
        }
    }
}
