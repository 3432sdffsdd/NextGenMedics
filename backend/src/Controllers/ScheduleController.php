<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\FileUploadHelper;
use App\Repositories\BatchRepository;
use App\Repositories\ScheduleRepository;
use App\Services\CourseService;
use App\Services\ScheduleService;

class ScheduleController extends BaseController
{
    private const STATUSES = ['upcoming', 'live', 'completed', 'cancelled', 'postponed', 'rescheduled'];

    public function __construct(
        private ScheduleRepository $schedules,
        private ScheduleService $scheduleService,
        private CourseService $courseService,
        private BatchRepository $batches
    ) {}

    /** GET /schedules — filtered, role-scoped list. */
    public function index(Request $request): void
    {
        $user = $request->user();
        $filters = [
            'course_id'  => $request->query('course_id'),
            'batch_id'   => $request->query('batch_id'),
            'teacher_id' => $request->query('teacher_id'),
            'subject'    => $request->query('subject'),
            'status'     => $request->query('status'),
            'date_from'  => $request->query('date_from'),
            'date_to'    => $request->query('date_to'),
        ];

        $studentBatchIds = $user['role'] === 'student'
            ? $this->batches->batchIdsForStudent((int) $user['id'])
            : [];

        Response::success($this->schedules->listFiltered($user, $filters, $studentBatchIds));
    }

    /** GET /schedules/{id} */
    public function show(Request $request): void
    {
        $schedule = $this->schedules->findById((int) $request->param('id'));
        if (!$schedule) {
            Response::error('Schedule not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $schedule['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($schedule);
    }

    /** POST /schedules — create a single lecture. */
    public function store(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'course_id'     => 'required|integer',
            'lecture_title' => 'required|min:2',
            'class_date'    => 'required|date',
        ]);
        if (!$data) return;

        $body = $request->body();
        if (empty($body['start_time']) || empty($body['end_time'])) {
            Response::error('Validation failed', 422, ['start_time' => ['start_time and end_time are required']]);
            return;
        }

        $courseId = (int) $data['course_id'];
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $payload = $this->buildPayload($body, $request);

        // Conflict check (unless forced).
        if (empty($body['force'])) {
            $conflicts = $this->schedules->findConflicts(
                $payload['teacher_id'] ?: null,
                $payload['batch_id'] ?: null,
                $payload['class_date'],
                $payload['start_time'],
                $payload['end_time']
            );
            if ($conflicts) {
                Response::error('Scheduling conflict detected', 409, ['conflicts' => $conflicts]);
                return;
            }
        }

        $id = $this->schedules->create($payload);
        $schedule = $this->schedules->findById($id);

        $whenLabel = date('M j, Y', strtotime($payload['class_date'])) . ' ' . substr($payload['start_time'], 0, 5);
        $this->scheduleService->notifyScheduled(
            $courseId, $payload['batch_id'] ?: null, $schedule['course_title'], $payload['lecture_title'], $whenLabel, $id
        );

        Response::success($schedule, 'Class scheduled', 201);
    }

    /** POST /schedules/bulk — generate daily/weekly/monthly recurring lectures. */
    public function bulk(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $data = $this->validate($request, [
            'course_id'     => 'required|integer',
            'lecture_title' => 'required|min:2',
            'start_date'    => 'required|date',
            'mode'          => 'required|in:single,daily,weekly,monthly',
        ]);
        if (!$data) return;

        $body = $request->body();
        if (empty($body['start_time']) || empty($body['end_time'])) {
            Response::error('Validation failed', 422, ['start_time' => ['start_time and end_time are required']]);
            return;
        }

        $courseId = (int) $data['course_id'];
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $dates = $this->scheduleService->expandDates([
            'mode'         => $body['mode'],
            'start_date'   => $body['start_date'],
            'until_date'   => $body['until_date'] ?? null,
            'interval'     => $body['interval'] ?? 1,
            'days_of_week' => $body['days_of_week'] ?? null,
            'day_of_month' => $body['day_of_month'] ?? null,
        ]);

        $force = !empty($body['force']);
        $lectureNumber = isset($body['lecture_number']) ? (int) $body['lecture_number'] : null;
        $created = [];
        $skipped = [];

        foreach ($dates as $date) {
            $payload = $this->buildPayload(array_merge($body, ['class_date' => $date, 'lecture_number' => $lectureNumber]), $request);

            if (!$force) {
                $conflicts = $this->schedules->findConflicts(
                    $payload['teacher_id'] ?: null,
                    $payload['batch_id'] ?: null,
                    $date,
                    $payload['start_time'],
                    $payload['end_time']
                );
                if ($conflicts) {
                    $skipped[] = ['date' => $date, 'reason' => 'conflict'];
                    continue;
                }
            }

            $created[] = $this->schedules->create($payload);
            if ($lectureNumber !== null) $lectureNumber++;
        }

        if ($created) {
            $schedule = $this->schedules->findById($created[0]);
            $this->scheduleService->notifyScheduled(
                $courseId,
                (int) ($body['batch_id'] ?? 0) ?: null,
                $schedule['course_title'],
                $body['lecture_title'],
                '',
                null,
                count($created)
            );
        }

        Response::success([
            'created' => count($created),
            'skipped' => $skipped,
            'total'   => count($dates),
        ], 'Schedule generated', 201);
    }

    /** PUT /schedules/{id} */
    public function update(Request $request): void
    {
        $schedule = $this->authScheduleForWrite($request);
        if (!$schedule) return;

        $body = $request->body();
        unset($body['course_id'], $body['created_by'], $body['id']);

        if (!empty($body['start_time']) && !empty($body['end_time'])) {
            $body['duration_minutes'] = $this->scheduleService->computeDuration($body['start_time'], $body['end_time']);
        }

        $this->schedules->update((int) $schedule['id'], $body);
        Response::success($this->schedules->findById((int) $schedule['id']), 'Schedule updated');
    }

    /** DELETE /schedules/{id} */
    public function destroy(Request $request): void
    {
        $schedule = $this->authScheduleForWrite($request);
        if (!$schedule) return;
        $this->schedules->delete((int) $schedule['id']);
        Response::success(null, 'Schedule deleted');
    }

    /** PATCH /schedules/{id}/cancel */
    public function cancel(Request $request): void
    {
        $schedule = $this->authScheduleForWrite($request);
        if (!$schedule) return;
        $remarks = $request->input('remarks');
        if ($remarks !== null) $this->schedules->update((int) $schedule['id'], ['remarks' => $remarks]);
        $this->schedules->setStatus((int) $schedule['id'], 'cancelled', true);
        Response::success($this->schedules->findById((int) $schedule['id']), 'Class cancelled');
    }

    /** PATCH /schedules/{id}/complete */
    public function complete(Request $request): void
    {
        $schedule = $this->authScheduleForWrite($request);
        if (!$schedule) return;
        $this->schedules->setStatus((int) $schedule['id'], 'completed', true);
        Response::success($this->schedules->findById((int) $schedule['id']), 'Class marked completed');
    }

    /** PATCH /schedules/{id}/status  { status: upcoming|... | auto } */
    public function setStatus(Request $request): void
    {
        $schedule = $this->authScheduleForWrite($request);
        if (!$schedule) return;

        $status = (string) $request->input('status', '');
        if ($status === 'auto') {
            $this->schedules->update((int) $schedule['id'], ['is_status_locked' => 0, 'status' => 'upcoming']);
        } elseif (in_array($status, self::STATUSES, true)) {
            $this->schedules->setStatus((int) $schedule['id'], $status, true);
        } else {
            Response::error('Invalid status', 422);
            return;
        }
        Response::success($this->schedules->findById((int) $schedule['id']), 'Status updated');
    }

    /** PATCH /schedules/{id}/reschedule  { class_date, start_time, end_time } */
    public function reschedule(Request $request): void
    {
        $schedule = $this->authScheduleForWrite($request);
        if (!$schedule) return;

        $body = $request->body();
        if (empty($body['class_date']) || empty($body['start_time']) || empty($body['end_time'])) {
            Response::error('class_date, start_time and end_time are required', 422);
            return;
        }

        $update = [
            'class_date'       => $body['class_date'],
            'start_time'       => $body['start_time'],
            'end_time'         => $body['end_time'],
            'duration_minutes' => $this->scheduleService->computeDuration($body['start_time'], $body['end_time']),
            'status'           => 'rescheduled',
            'is_status_locked' => 1,
        ];
        if (!empty($body['remarks'])) $update['remarks'] = $body['remarks'];

        $this->schedules->update((int) $schedule['id'], $update);
        Response::success($this->schedules->findById((int) $schedule['id']), 'Class rescheduled');
    }

    /** POST /schedules/{id}/duplicate  { class_date } */
    public function duplicate(Request $request): void
    {
        $schedule = $this->authScheduleForWrite($request);
        if (!$schedule) return;

        $newDate = $request->input('class_date', $schedule['class_date']);
        $copy = [
            'course_id'      => (int) $schedule['course_id'],
            'batch_id'       => $schedule['batch_id'] ? (int) $schedule['batch_id'] : null,
            'teacher_id'     => $schedule['teacher_id'] ? (int) $schedule['teacher_id'] : null,
            'subject'        => $schedule['subject'],
            'lecture_title'  => $schedule['lecture_title'],
            'lecture_number' => $schedule['lecture_number'],
            'topic_covered'  => $schedule['topic_covered'],
            'description'    => $schedule['description'],
            'class_date'     => $newDate,
            'start_time'     => $schedule['start_time'],
            'end_time'       => $schedule['end_time'],
            'duration_minutes' => $schedule['duration_minutes'],
            'meeting_link'   => $schedule['meeting_link'],
            'status'         => 'upcoming',
            'is_status_locked' => 0,
            'created_by'     => $request->userId(),
        ];
        $id = $this->schedules->create($copy);
        Response::success($this->schedules->findById($id), 'Schedule duplicated', 201);
    }

    /** GET /courses/{courseId}/schedules/months — monthly upload history for a course. */
    public function listMonthUploads(Request $request): void
    {
        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        try {
            Response::success($this->schedules->listMonthUploads($courseId));
        } catch (\Throwable) {
            Response::success([]);
        }
    }

    /** POST /courses/{courseId}/schedules/import — replace a month's schedule from Excel rows. */
    public function importMonth(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) {
            return;
        }

        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $month = (string) $request->input('month', '');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            Response::error('Invalid month format. Use YYYY-MM.', 422);
            return;
        }

        $rows = $request->input('rows', []);
        if (is_string($rows)) {
            $decoded = json_decode($rows, true);
            $rows = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($rows) || !$rows) {
            Response::error('No schedule rows provided', 422);
            return;
        }

        $replace = filter_var($request->input('replace', true), FILTER_VALIDATE_BOOLEAN);
        $defaultTeacherId = $request->userRole() === 'teacher' ? $request->userId() : null;

        $filePath = null;
        $fileName = null;
        $file = $request->file('file');
        if ($file) {
            $result = FileUploadHelper::upload($file, 'spreadsheet', 'schedules');
            if (!$result) {
                return;
            }
            $filePath = $result['path'];
            $fileName = $result['original_name'];
        }

        $created = 0;
        $skipped = [];

        $this->schedules->beginTransaction();
        try {
            if ($replace) {
                $existing = $this->schedules->getMonthUpload($courseId, $month);
                if ($existing && !empty($existing['file_path'])) {
                    FileUploadHelper::delete($existing['file_path']);
                }
                $this->schedules->deleteByCourseMonth($courseId, $month);
            }

            foreach ($rows as $i => $row) {
                if (!is_array($row)) {
                    $skipped[] = ['row' => $i + 1, 'reason' => 'invalid'];
                    continue;
                }

                $classDate = $row['class_date'] ?? null;
                $startTime = $this->normalizeTime($row['start_time'] ?? null);
                $endTime = $this->normalizeTime($row['end_time'] ?? null);
                $title = trim((string) ($row['lecture_title'] ?? $row['title'] ?? ''));

                if (!$classDate || !$startTime || !$endTime || $title === '') {
                    $skipped[] = ['row' => $i + 1, 'reason' => 'missing fields'];
                    continue;
                }

                if (!str_starts_with($classDate, $month)) {
                    $skipped[] = ['row' => $i + 1, 'reason' => 'date outside selected month'];
                    continue;
                }

                $teacherName = trim((string) ($row['teacher'] ?? $row['teacher_name'] ?? ''));
                $teacherId = $teacherName !== ''
                    ? ($this->schedules->findTeacherIdByName($teacherName) ?? $defaultTeacherId)
                    : $defaultTeacherId;

                $payload = [
                    'course_id'        => $courseId,
                    'batch_id'         => !empty($row['batch_id']) ? (int) $row['batch_id'] : null,
                    'teacher_id'       => $teacherId,
                    'subject'          => $row['subject'] ?? null,
                    'lecture_title'    => $title,
                    'lecture_number'   => isset($row['lecture_number']) && $row['lecture_number'] !== ''
                        ? (int) $row['lecture_number'] : null,
                    'topic_covered'    => $row['topic_covered'] ?? $row['topic'] ?? null,
                    'description'      => $row['description'] ?? null,
                    'class_date'       => $classDate,
                    'start_time'       => $startTime,
                    'end_time'         => $endTime,
                    'duration_minutes' => $this->scheduleService->computeDuration($startTime, $endTime),
                    'meeting_link'     => $row['meeting_link'] ?? $row['meeting_url'] ?? null,
                    'status'           => 'upcoming',
                    'is_status_locked' => 0,
                    'created_by'       => $request->userId(),
                ];

                $this->schedules->create($payload);
                $created++;
            }

            if ($created === 0) {
                $this->schedules->rollBack();
                Response::error('No valid rows could be imported', 422, ['skipped' => $skipped]);
                return;
            }

            $this->schedules->upsertMonthUpload([
                'course_id'   => $courseId,
                'month_year'  => $month,
                'file_path'   => $filePath,
                'file_name'   => $fileName,
                'row_count'   => $created,
                'uploaded_by' => $request->userId(),
            ]);

            $this->schedules->commit();
        } catch (\Throwable $e) {
            $this->schedules->rollBack();
            if ($filePath) {
                FileUploadHelper::delete($filePath);
            }
            Response::error('Import failed: ' . $e->getMessage(), 500);
            return;
        }

        Response::success([
            'created' => $created,
            'skipped' => $skipped,
            'month'   => $month,
        ], 'Monthly schedule imported', 201);
    }

    /** DELETE /courses/{courseId}/schedules/month/{month} — clear a month's schedule. */
    public function clearMonth(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) {
            return;
        }

        $courseId = (int) $request->param('courseId');
        $month = (string) $request->param('month');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            Response::error('Invalid month format. Use YYYY-MM.', 422);
            return;
        }

        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $existing = $this->schedules->getMonthUpload($courseId, $month);
        if ($existing && !empty($existing['file_path'])) {
            FileUploadHelper::delete($existing['file_path']);
        }

        $deleted = $this->schedules->deleteByCourseMonth($courseId, $month);
        $this->schedules->deleteMonthUpload($courseId, $month);

        Response::success(['deleted' => $deleted, 'month' => $month], 'Month schedule cleared');
    }

    /** POST /schedules/{id}/attachment  (multipart: file) */
    public function uploadAttachment(Request $request): void
    {
        $schedule = $this->authScheduleForWrite($request);
        if (!$schedule) return;

        $file = $request->file('file');
        if (!$file) {
            Response::error('No file provided', 422);
            return;
        }
        $result = FileUploadHelper::upload($file, 'all', 'schedules');
        if (!$result) {
            Response::error('Upload failed', 422);
            return;
        }
        $field = $request->input('field') === 'recording' ? 'recording_link' : 'attachment_path';
        $update = [$field => $result['path']];
        if ($field === 'attachment_path') $update['attachment_name'] = $result['original_name'];
        $this->schedules->update((int) $schedule['id'], $update);
        Response::success($this->schedules->findById((int) $schedule['id']), 'File uploaded');
    }

    // ── Helpers ──────────────────────────────────────────────

    private function buildPayload(array $body, Request $request): array
    {
        $start = $body['start_time'];
        $end = $body['end_time'];
        $teacherId = !empty($body['teacher_id'])
            ? (int) $body['teacher_id']
            : ($request->userRole() === 'teacher' ? $request->userId() : null);

        return [
            'course_id'      => (int) $body['course_id'],
            'batch_id'       => !empty($body['batch_id']) ? (int) $body['batch_id'] : null,
            'teacher_id'     => $teacherId,
            'subject'        => $body['subject'] ?? null,
            'lecture_title'  => $body['lecture_title'],
            'lecture_number' => isset($body['lecture_number']) && $body['lecture_number'] !== '' ? (int) $body['lecture_number'] : null,
            'topic_covered'  => $body['topic_covered'] ?? null,
            'description'    => $body['description'] ?? null,
            'class_date'     => $body['class_date'],
            'start_time'     => $start,
            'end_time'       => $end,
            'duration_minutes' => $this->scheduleService->computeDuration($start, $end),
            'meeting_link'   => $body['meeting_link'] ?? null,
            'recording_link' => $body['recording_link'] ?? null,
            'remarks'        => $body['remarks'] ?? null,
            'status'         => 'upcoming',
            'is_status_locked' => 0,
            'created_by'     => $request->userId(),
        ];
    }

    private function authScheduleForWrite(Request $request): ?array
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return null;
        $schedule = $this->schedules->findById((int) $request->param('id'));
        if (!$schedule) {
            Response::error('Schedule not found', 404);
            return null;
        }
        if (!$this->courseService->canAccess((int) $schedule['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return null;
        }
        return $schedule;
    }

    private function normalizeTime(?string $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }
        $time = trim($time);
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $time)) {
            return strlen($time) === 5 ? $time . ':00' : $time;
        }
        return null;
    }
}
