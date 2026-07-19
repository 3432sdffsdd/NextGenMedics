<?php
namespace App\Services\VideoTracking;

use App\Repositories\CourseRepository;
use App\Repositories\VideoTrackingRepository;

class VideoTrackingService
{
    private const COMPLETE_THRESHOLD = 95.0;

    public function __construct(
        private VideoTrackingRepository $repo,
        private CourseRepository $courses
    ) {}

    public function getResume(int $studentId, int $resourceId): array
    {
        $ctx = $this->requireAccessibleResource($studentId, $resourceId);
        $p = $this->repo->getProgress($studentId, $resourceId);
        $last = $p ? (float) $p['last_position'] : 0;
        $duration = $p ? (float) $p['duration_seconds'] : 0;
        $canResume = $last > 5 && ($duration <= 0 || $last < $duration * 0.95);
        return [
            'resource_id' => $resourceId,
            'lecture_id' => (int) ($ctx['lecture_id'] ?? 0),
            'course_id' => (int) ($ctx['course_id'] ?? 0),
            'video_title' => $ctx['video_title'],
            'lecture_title' => $ctx['lecture_title'],
            'can_resume' => $canResume,
            'last_position' => $last,
            'last_position_label' => $this->formatTime($last),
            'duration_seconds' => $duration,
            'completion_pct' => $p ? (float) $p['completion_pct'] : 0,
            'status' => $p['status'] ?? 'not_started',
            'progress' => $p,
        ];
    }

    public function track(int $studentId, array $payload, ?string $ip = null): array
    {
        $resourceId = (int) ($payload['resource_id'] ?? 0);
        $eventType = (string) ($payload['event_type'] ?? 'heartbeat');
        $position = max(0, (float) ($payload['position'] ?? 0));
        $duration = max(0, (float) ($payload['duration'] ?? 0));
        $delta = max(0, min(30, (float) ($payload['watched_delta'] ?? 0))); // cap per ping
        $speed = max(0.5, min(3, (float) ($payload['playback_speed'] ?? 1)));
        $segmentStart = isset($payload['segment_start']) ? (float) $payload['segment_start'] : null;

        $ctx = $this->requireAccessibleResource($studentId, $resourceId);
        $lectureId = (int) $ctx['lecture_id'];
        $courseId = (int) $ctx['course_id'];

        $existing = $this->repo->getProgress($studentId, $resourceId) ?: [
            'duration_seconds' => 0,
            'watched_seconds' => 0,
            'max_position' => 0,
            'last_position' => 0,
            'completion_pct' => 0,
            'status' => 'not_started',
            'play_count' => 0,
            'replay_count' => 0,
            'pause_count' => 0,
            'seek_forward_count' => 0,
            'seek_backward_count' => 0,
            'playback_speed' => 1,
            'first_watched_at' => null,
            'completed_at' => null,
        ];

        $playCount = (int) $existing['play_count'];
        $replayCount = (int) $existing['replay_count'];
        $pauseCount = (int) $existing['pause_count'];
        $seekFwd = (int) $existing['seek_forward_count'];
        $seekBack = (int) $existing['seek_backward_count'];
        $maxPos = max((float) $existing['max_position'], $position);
        $wasCompleted = ($existing['status'] ?? '') === 'completed';

        if (in_array($eventType, ['play', 'started', 'resumed'], true)) {
            if ($playCount === 0) {
                $playCount = 1;
            } elseif ($eventType === 'play' || $eventType === 'started') {
                // restart near beginning counts as replay
                if ($position < 3) {
                    $replayCount++;
                    $playCount++;
                }
            }
        }
        if ($eventType === 'paused') {
            $pauseCount++;
        }
        if ($eventType === 'seek_forward') {
            $seekFwd++;
        }
        if ($eventType === 'seek_backward') {
            $seekBack++;
        }

        if ($segmentStart !== null && $position > $segmentStart) {
            $this->repo->addSegment($studentId, $resourceId, $segmentStart, $position);
        } elseif ($delta > 0) {
            $start = max(0, $position - $delta);
            $this->repo->addSegment($studentId, $resourceId, $start, $position);
        }

        $unique = $this->repo->uniqueWatchedSeconds($studentId, $resourceId);
        // Fallback if no segments yet: accumulate deltas (capped)
        if ($unique <= 0 && $delta > 0) {
            $unique = (float) $existing['watched_seconds'] + $delta;
        } elseif ($unique > 0 && $delta > 0) {
            $unique = max($unique, (float) $existing['watched_seconds']);
        } else {
            $unique = max($unique, (float) $existing['watched_seconds']);
        }

        if ($duration > 0) {
            $unique = min($unique, $duration);
        }

        $pct = 0.0;
        if ($duration > 0) {
            $uniquePct = ($unique / $duration) * 100;
            $maxPct = ($maxPos / $duration) * 100;
            // Unique seconds are authoritative; max position can raise % only if real watch time is substantial
            $pct = $uniquePct;
            if ($unique >= $duration * 0.4) {
                $pct = max($pct, $maxPct * 0.9);
            }
            $pct = round(min(100, $pct), 2);
        }

        $status = 'not_started';
        $completedAt = $existing['completed_at'] ?? null;
        $reachedEnd = $duration > 0 && (
            $pct >= self::COMPLETE_THRESHOLD
            || ($position >= $duration * 0.95 && $unique >= $duration * 0.5)
            || in_array($eventType, ['ended', 'completed'], true) && $unique >= $duration * 0.5
        );
        if ($reachedEnd) {
            $status = 'completed';
            $pct = max($pct, self::COMPLETE_THRESHOLD);
            $completedAt = $completedAt ?: date('Y-m-d H:i:s');
        } elseif ($pct > 0 || $playCount > 0 || $position > 1) {
            $status = 'watching';
        }
        if ($wasCompleted) {
            $status = 'completed';
            $pct = max($pct, self::COMPLETE_THRESHOLD);
        }

        $ua = $payload['client'] ?? [];
        $now = date('Y-m-d H:i:s');
        $row = [
            'lecture_id' => $lectureId,
            'course_id' => $courseId,
            'duration_seconds' => $duration > 0 ? $duration : (float) $existing['duration_seconds'],
            'watched_seconds' => round($unique, 2),
            'max_position' => round($maxPos, 2),
            'last_position' => round($position, 2),
            'completion_pct' => $pct,
            'status' => $status,
            'play_count' => $playCount,
            'replay_count' => $replayCount,
            'pause_count' => $pauseCount,
            'seek_forward_count' => $seekFwd,
            'seek_backward_count' => $seekBack,
            'playback_speed' => $speed,
            'device_type' => $ua['device_type'] ?? null,
            'browser' => $ua['browser'] ?? null,
            'os_name' => $ua['os'] ?? null,
            'ip_address' => $ip,
            'first_watched_at' => $existing['first_watched_at'] ?: $now,
            'last_watched_at' => $now,
            'completed_at' => $completedAt,
        ];

        $this->repo->upsertProgress($studentId, $resourceId, $row);

        $this->repo->insertEvent([
            'student_id' => $studentId,
            'resource_id' => $resourceId,
            'lecture_id' => $lectureId,
            'course_id' => $courseId,
            'event_type' => $eventType,
            'position_seconds' => $position,
            'duration_seconds' => $duration,
            'watched_delta' => $delta,
            'playback_speed' => $speed,
            'meta_json' => $payload['meta'] ?? null,
        ]);

        if ($status === 'completed') {
            $this->repo->markManualWatchCompat($studentId, $resourceId, true);
            $this->repo->syncPlannerVideoTasks($studentId, $resourceId);
        }

        return [
            'progress' => $this->repo->getProgress($studentId, $resourceId),
            'just_completed' => !$wasCompleted && $status === 'completed',
        ];
    }

    public function studentDashboard(int $studentId): array
    {
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        return [
            'summary' => $this->repo->studentSummary($studentId, $courseIds),
            'subjects' => $this->repo->subjectProgress($studentId, $courseIds),
            'videos' => $this->repo->listStudentVideos($studentId, $courseIds),
            'timeline' => $this->repo->timeline($studentId, 40),
        ];
    }

    public function studentVideos(int $studentId, array $filters = []): array
    {
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        return [
            'summary' => $this->repo->studentSummary($studentId, $courseIds),
            'videos' => $this->repo->listStudentVideos($studentId, $courseIds, $filters),
            'subjects' => $this->repo->subjectProgress($studentId, $courseIds),
        ];
    }

    public function teacherOverview(int $teacherId, int $courseId): array
    {
        $this->requireTeacherCourse($teacherId, $courseId);
        return [
            'analytics' => $this->repo->analyticsForCourse($courseId),
            'class_report' => $this->repo->classReport($courseId),
        ];
    }

    public function teacherStudent(int $teacherId, int $courseId, int $studentId): array
    {
        $this->requireTeacherCourse($teacherId, $courseId);
        $summary = $this->repo->studentSummary($studentId, [$courseId]);
        $videos = $this->repo->studentLectureDetails($studentId, $courseId);
        $mostReplay = null;
        $least = null;
        $last = null;
        foreach ($videos as $v) {
            if ($mostReplay === null || (int) ($v['replay_count'] ?? 0) > (int) ($mostReplay['replay_count'] ?? 0)) {
                $mostReplay = $v;
            }
            if ($least === null || (float) $v['completion_pct'] < (float) $least['completion_pct']) {
                $least = $v;
            }
            if (!empty($v['last_watched_at']) && ($last === null || $v['last_watched_at'] > $last['last_watched_at'])) {
                $last = $v;
            }
        }
        return [
            'summary' => $summary,
            'videos' => $videos,
            'most_replayed' => $mostReplay,
            'least_watched' => $least,
            'last_viewed' => $last,
            'timeline' => $this->repo->timeline($studentId, 60),
        ];
    }

    public function exportClassCsv(int $teacherId, int $courseId): string
    {
        $this->requireTeacherCourse($teacherId, $courseId);
        $rows = $this->repo->classReport($courseId);
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['Rank', 'Student', 'Email', 'Completion %', 'Completed', 'Remaining', 'Avg Watch %', 'Study Hours']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['rank'], $r['student_name'], $r['email'], $r['lecture_completion_pct'],
                $r['videos_completed'], $r['videos_remaining'], $r['average_watch_pct'], $r['study_hours'],
            ]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv ?: '';
    }

    private function requireAccessibleResource(int $studentId, int $resourceId): array
    {
        $ctx = $this->repo->resourceContext($resourceId);
        if (!$ctx) {
            throw new \RuntimeException('Video not found');
        }
        $courseIds = array_column($this->courses->listByStudent($studentId), 'id');
        if (!in_array((int) $ctx['course_id'], array_map('intval', $courseIds), true)) {
            throw new \RuntimeException('Video not accessible');
        }
        return $ctx;
    }

    private function requireTeacherCourse(int $teacherId, int $courseId): void
    {
        if (!$this->repo->teacherCanAccessCourse($teacherId, $courseId)) {
            throw new \RuntimeException('Course not found');
        }
    }

    private function formatTime(float $seconds): string
    {
        $s = (int) floor($seconds);
        $m = intdiv($s, 60);
        $r = $s % 60;
        $h = intdiv($m, 60);
        $m = $m % 60;
        if ($h > 0) {
            return sprintf('%d:%02d:%02d', $h, $m, $r);
        }
        return sprintf('%d:%02d', $m, $r);
    }
}
