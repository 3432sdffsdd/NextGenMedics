<?php
namespace App\Services;

use App\Repositories\ClassScheduleRepository;

class ClassReminderService
{
    private const REMINDER_MINUTES = 10;
    private const WINDOW_SECONDS = 90;

    public function __construct(
        private ClassScheduleRepository $schedules,
        private NotificationService $notifier,
        private WhatsAppService $whatsapp
    ) {}

    public function processDueReminders(): array
    {
        $sent = ['teacher' => 0, 'student' => 0, 'skipped' => 0];
        $todayDow = (int) date('w');
        $occurrenceDate = date('Y-m-d');
        $now = time();

        foreach ($this->schedules->getAllActiveWithCourse() as $slot) {
            if ((int) $slot['day_of_week'] !== $todayDow) {
                continue;
            }

            $classTs = strtotime($occurrenceDate . ' ' . $slot['start_time']);
            $diff = $classTs - $now;
            if ($diff < (self::REMINDER_MINUTES * 60 - self::WINDOW_SECONDS / 2)
                || $diff > (self::REMINDER_MINUTES * 60 + self::WINDOW_SECONDS / 2)) {
                continue;
            }

            $courseTitle = $slot['course_title'];
            $classTitle = $slot['title'] ?: 'Live Class';
            $timeLabel = date('g:i A', $classTs);
            $dayName = date('l', $classTs);
            $meeting = $slot['meeting_url'] ? "\nJoin: {$slot['meeting_url']}" : '';

            // Teacher
            $teacherId = (int) $slot['teacher_id'];
            if ($teacherId && !$this->schedules->reminderAlreadySent($slot['id'], $occurrenceDate, $teacherId, 'teacher', 'in_app')) {
                $teacherMsg = "Your class \"{$classTitle}\" ({$courseTitle}) starts in 10 minutes — {$dayName} at {$timeLabel}. Please join and prepare to teach.{$meeting}";
                $this->notifier->notify($teacherId, 'class_reminder', 'Class starts in 10 minutes', $teacherMsg, ['schedule_id' => $slot['id']], false);
                $this->schedules->logReminder($slot['id'], $occurrenceDate, $teacherId, 'teacher', 'in_app');

                if (!$this->schedules->reminderAlreadySent($slot['id'], $occurrenceDate, $teacherId, 'teacher', 'whatsapp')) {
                    $teacher = $this->getUserPhone($teacherId, $slot['teacher_phone'] ?? null);
                    if ($teacher && $this->whatsapp->send($teacher, "👨‍🏫 *Teacher Reminder*\n\n{$teacherMsg}")) {
                        $this->schedules->logReminder($slot['id'], $occurrenceDate, $teacherId, 'teacher', 'whatsapp');
                    }
                }
                $sent['teacher']++;
            } else {
                $sent['skipped']++;
            }

            // Students
            foreach ($this->schedules->getEnrolledStudents((int) $slot['course_id']) as $student) {
                $studentId = (int) $student['id'];
                if ($this->schedules->reminderAlreadySent($slot['id'], $occurrenceDate, $studentId, 'student', 'in_app')) {
                    $sent['skipped']++;
                    continue;
                }

                $studentMsg = "Your class \"{$classTitle}\" ({$courseTitle}) starts in 10 minutes — {$dayName} at {$timeLabel}. Please be ready to join.{$meeting}";
                $this->notifier->notify($studentId, 'class_reminder', 'Class starts in 10 minutes', $studentMsg, ['schedule_id' => $slot['id']], false);
                $this->schedules->logReminder($slot['id'], $occurrenceDate, $studentId, 'student', 'in_app');

                if (!$this->schedules->reminderAlreadySent($slot['id'], $occurrenceDate, $studentId, 'student', 'whatsapp')
                    && !empty($student['phone'])) {
                    if ($this->whatsapp->send($student['phone'], "🎓 *Student Reminder*\n\n{$studentMsg}")) {
                        $this->schedules->logReminder($slot['id'], $occurrenceDate, $studentId, 'student', 'whatsapp');
                    }
                }
                $sent['student']++;
            }
        }

        return $sent;
    }

    private function getUserPhone(int $userId, ?string $cached): ?string
    {
        return $cached ?: null;
    }
}
