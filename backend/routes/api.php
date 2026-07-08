<?php
/**
 * NextGen Medics LMS API Routes
 */

use App\Controllers\AiController;
use App\Controllers\AnnouncementController;
use App\Controllers\AssignmentController;
use App\Controllers\AttendanceController;
use App\Controllers\AuthController;
use App\Controllers\BatchController;
use App\Controllers\ContentController;
use App\Controllers\CourseController;
use App\Controllers\DashboardController;
use App\Controllers\ClassScheduleController;
use App\Controllers\CronController;
use App\Controllers\DiscussionController;
use App\Controllers\LiveSessionController;
use App\Controllers\MediaController;
use App\Controllers\NotificationController;
use App\Controllers\ProgressController;
use App\Controllers\PremiumStudyController;
use App\Controllers\PublicController;
use App\Controllers\QuizController;
use App\Controllers\StudentAiController;
use App\Controllers\ScheduleController;
use App\Controllers\UserController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\RoleMiddleware;

/** @var Router $router */

$public = [CorsMiddleware::class, RateLimitMiddleware::class];
$auth = array_merge($public, [AuthMiddleware::class]);

// ── Health ──────────────────────────────────────────────────
$router->get('/', fn() => \App\Core\Response::json([
    'app'     => 'NextGen Medics API',
    'status'  => 'ok',
    'version' => '1.0',
    'endpoints' => [
        'health' => '/health',
        'login'  => 'POST /auth/login',
        'courses'=> '/courses',
    ],
]));
$router->get('/health', fn() => \App\Core\Response::json(['status' => 'ok', 'app' => 'NextGen Medics API']));

// ── Auth ────────────────────────────────────────────────────
$router->post('/auth/login', [AuthController::class, 'login'], $public);
$router->post('/auth/refresh', [AuthController::class, 'refresh'], $public);
$router->post('/auth/forgot-password', [AuthController::class, 'forgotPassword'], $public);
$router->post('/auth/reset-password', [AuthController::class, 'resetPassword'], $public);
$router->get('/auth/me', [AuthController::class, 'me'], $auth);
$router->post('/auth/logout', [AuthController::class, 'logout'], $auth);
$router->post('/auth/change-password', [AuthController::class, 'changePassword'], $auth);

// ── Public Content (Frontend) ───────────────────────────────
$router->get('/courses', [CourseController::class, 'index'], $public);
$router->get('/courses/categories', [CourseController::class, 'categories'], $public);
$router->get('/courses/{slug}', [CourseController::class, 'show'], $public);
$router->get('/mentors', [PublicController::class, 'mentors'], $public);
$router->get('/testimonials', [PublicController::class, 'testimonials'], $public);
$router->get('/resources', [PublicController::class, 'resources'], $public);
$router->post('/contact', [PublicController::class, 'contact'], $public);
$router->get('/announcements', [AnnouncementController::class, 'index'], $public);

// ── Dashboards ──────────────────────────────────────────────
$router->get('/admin/dashboard', [DashboardController::class, 'admin'], array_merge($auth, [RoleMiddleware::admin()]));
$router->get('/teacher/dashboard', [DashboardController::class, 'teacher'], array_merge($auth, [RoleMiddleware::teacher()]));
$router->get('/student/dashboard', [DashboardController::class, 'student'], array_merge($auth, [RoleMiddleware::student()]));

// ── User Management (Admin) ─────────────────────────────────
$router->get('/admin/users/{role}', [UserController::class, 'index'], array_merge($auth, [RoleMiddleware::admin()]));
$router->post('/admin/users/{role}', [UserController::class, 'store'], array_merge($auth, [RoleMiddleware::admin()]));
$router->get('/admin/users/id/{id}', [UserController::class, 'show'], array_merge($auth, [RoleMiddleware::admin()]));
$router->put('/admin/users/{id}', [UserController::class, 'update'], array_merge($auth, [RoleMiddleware::admin()]));
$router->patch('/admin/users/{id}/suspend', [UserController::class, 'suspend'], array_merge($auth, [RoleMiddleware::admin()]));
$router->patch('/admin/users/{id}/activate', [UserController::class, 'activate'], array_merge($auth, [RoleMiddleware::admin()]));
$router->post('/admin/users/{id}/reset-password', [UserController::class, 'resetPassword'], array_merge($auth, [RoleMiddleware::admin()]));
$router->delete('/admin/users/{id}', [UserController::class, 'destroy'], array_merge($auth, [RoleMiddleware::admin()]));
$router->get('/admin/roles', [UserController::class, 'roles'], array_merge($auth, [RoleMiddleware::admin()]));

// ── Profile ─────────────────────────────────────────────────
$router->put('/profile', [UserController::class, 'updateProfile'], $auth);

// ── Courses (Authenticated) ─────────────────────────────────
$router->get('/admin/courses', [CourseController::class, 'adminIndex'], array_merge($auth, [RoleMiddleware::admin()]));
$router->post('/courses', [CourseController::class, 'store'], array_merge($auth, [RoleMiddleware::admin()]));
$router->put('/courses/{id}', [CourseController::class, 'update'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/courses/{id}', [CourseController::class, 'destroy'], array_merge($auth, [RoleMiddleware::admin()]));
$router->patch('/courses/{id}/archive', [CourseController::class, 'archive'], array_merge($auth, [RoleMiddleware::admin()]));
$router->patch('/courses/{id}/publish', [CourseController::class, 'publish'], array_merge($auth, [RoleMiddleware::admin()]));
$router->post('/courses/{id}/duplicate', [CourseController::class, 'duplicate'], array_merge($auth, [RoleMiddleware::admin()]));
$router->post('/courses/{id}/assign-teacher', [CourseController::class, 'assignTeacher'], array_merge($auth, [RoleMiddleware::admin()]));
$router->post('/courses/{id}/enroll', [CourseController::class, 'enrollStudents'], array_merge($auth, [RoleMiddleware::admin()]));
$router->get('/courses/id/{id}', [CourseController::class, 'showById'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/courses/{id}/enrollments', [CourseController::class, 'enrollments'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/courses/{id}/students', [CourseController::class, 'enrolledStudents'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/courses/{id}/enroll/{studentId}', [CourseController::class, 'unenroll'], array_merge($auth, [RoleMiddleware::admin()]));
$router->get('/my/courses', [CourseController::class, 'myCourses'], $auth);
$router->get('/courses/{id}/structure', [CourseController::class, 'structure'], $auth);

// ── Media ───────────────────────────────────────────────────
$router->get('/media', [MediaController::class, 'serve'], $public);
$router->get('/media/document-preview', [MediaController::class, 'previewDocument'], $public);

// ── Course Content ──────────────────────────────────────────
$router->post('/content/modules', [ContentController::class, 'createModule'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/content/chapters', [ContentController::class, 'createChapter'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/content/lectures', [ContentController::class, 'createLecture'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/content/resources', [ContentController::class, 'uploadResource'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/content/{entity}/{id}', [ContentController::class, 'update'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/content/{entity}/{id}', [ContentController::class, 'destroy'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));

// ── Assignments ─────────────────────────────────────────────
$router->get('/assignments', [AssignmentController::class, 'index'], $auth);
$router->get('/assignments/my', [AssignmentController::class, 'myAssignments'], array_merge($auth, [RoleMiddleware::student()]));
$router->post('/assignments', [AssignmentController::class, 'store'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/assignments/parse-html', [AssignmentController::class, 'parseHtml'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/assignments/{id}', [AssignmentController::class, 'show'], $auth);
$router->put('/assignments/{id}', [AssignmentController::class, 'update'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/assignments/{id}/update', [AssignmentController::class, 'update'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/assignments/{id}', [AssignmentController::class, 'destroy'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->patch('/assignments/{id}/status', [AssignmentController::class, 'setStatus'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/assignments/{id}/attachments/{attachmentId}', [AssignmentController::class, 'deleteAttachment'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/assignments/{id}/submit', [AssignmentController::class, 'submit'], array_merge($auth, [RoleMiddleware::student()]));
$router->get('/assignments/{id}/test', [AssignmentController::class, 'getTest'], array_merge($auth, [RoleMiddleware::student()]));
$router->post('/assignments/{id}/submit-test', [AssignmentController::class, 'submitTest'], array_merge($auth, [RoleMiddleware::student()]));
$router->get('/assignments/{id}/submissions', [AssignmentController::class, 'submissions'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/assignments/submissions/{id}', [AssignmentController::class, 'deleteSubmission'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/assignments/grade', [AssignmentController::class, 'grade'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));

// ── Quizzes ─────────────────────────────────────────────────
$router->get('/quizzes', [QuizController::class, 'index'], $auth);
$router->get('/quizzes/template', [QuizController::class, 'downloadTemplate'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/quizzes', [QuizController::class, 'store'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/quizzes/{id}', [QuizController::class, 'show'], $auth);
$router->put('/quizzes/{id}', [QuizController::class, 'update'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/quizzes/{id}', [QuizController::class, 'destroy'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->patch('/quizzes/{id}/status', [QuizController::class, 'setStatus'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/quizzes/{id}/duplicate', [QuizController::class, 'duplicate'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/quizzes/questions', [QuizController::class, 'addQuestion'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/quizzes/questions/{questionId}', [QuizController::class, 'deleteQuestion'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/quizzes/parse-word', [QuizController::class, 'parseWord'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/quizzes/{id}/import-questions', [QuizController::class, 'importQuestions'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/quizzes/{id}/attempts', [QuizController::class, 'quizAttempts'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/quizzes/attempts/{attemptId}/teacher-review', [QuizController::class, 'teacherAttemptReview'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/quizzes/{id}/start', [QuizController::class, 'startAttempt'], array_merge($auth, [RoleMiddleware::student()]));
$router->get('/quizzes/{id}/my-attempts', [QuizController::class, 'myAttempts'], array_merge($auth, [RoleMiddleware::student()]));
$router->get('/quizzes/attempts/{attemptId}', [QuizController::class, 'attemptReview'], array_merge($auth, [RoleMiddleware::student()]));
$router->post('/quizzes/attempts/{attemptId}/submit', [QuizController::class, 'submitAttempt'], array_merge($auth, [RoleMiddleware::student()]));
$router->get('/quizzes/{id}/leaderboard', [QuizController::class, 'leaderboard'], $auth);

// ── Attendance ──────────────────────────────────────────────
$router->post('/attendance/sessions', [AttendanceController::class, 'createSession'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/attendance/mark', [AttendanceController::class, 'mark'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/attendance/course/{courseId}/by-date', [AttendanceController::class, 'sessionByDate'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/attendance/sessions/{sessionId}', [AttendanceController::class, 'updateSession'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/attendance/sessions/{sessionId}', [AttendanceController::class, 'deleteSession'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/attendance/course/{courseId}', [AttendanceController::class, 'byCourse'], $auth);
$router->get('/attendance/my', [AttendanceController::class, 'myAttendance'], $auth);
$router->get('/attendance/reports', [AttendanceController::class, 'reports'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/attendance/sessions/{sessionId}/records', [AttendanceController::class, 'sessionRecords'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));

// ── Announcements (Authenticated) ───────────────────────────
$router->post('/announcements', [AnnouncementController::class, 'store'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/announcements/{id}', [AnnouncementController::class, 'update'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/announcements/{id}', [AnnouncementController::class, 'destroy'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));

// ── Discussion ──────────────────────────────────────────────
$router->get('/discussions/course/{courseId}', [DiscussionController::class, 'index'], $auth);
$router->get('/discussions/{id}', [DiscussionController::class, 'show'], $auth);
$router->put('/discussions/{id}', [DiscussionController::class, 'update'], $auth);
$router->delete('/discussions/{id}', [DiscussionController::class, 'destroy'], $auth);
$router->post('/discussions', [DiscussionController::class, 'store'], $auth);
$router->post('/discussions/{id}/reply', [DiscussionController::class, 'reply'], $auth);
$router->patch('/discussions/{id}/moderate', [DiscussionController::class, 'moderate'], array_merge($auth, [RoleMiddleware::admin()]));

// ── Notifications ───────────────────────────────────────────
$router->get('/notifications', [NotificationController::class, 'index'], $auth);
$router->get('/notifications/unread-count', [NotificationController::class, 'unreadCount'], $auth);
$router->patch('/notifications/read-all', [NotificationController::class, 'markAllRead'], $auth);
$router->patch('/notifications/{id}/read', [NotificationController::class, 'markRead'], $auth);
$router->get('/courses/{courseId}/tab-notifications', [NotificationController::class, 'courseTabBadges'], $auth);
$router->patch('/courses/{courseId}/tab-notifications/read', [NotificationController::class, 'markCourseTabRead'], $auth);

// ── Live Sessions / Timetable ───────────────────────────────
$router->get('/sessions', [LiveSessionController::class, 'index'], $auth);
$router->post('/sessions', [LiveSessionController::class, 'store'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/sessions/{id}', [LiveSessionController::class, 'update'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/sessions/{id}', [LiveSessionController::class, 'destroy'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));

// ── Weekly class timetable ──────────────────────────────────
$router->get('/schedule', [ClassScheduleController::class, 'mySchedule'], $auth);
$router->get('/courses/{courseId}/schedule', [ClassScheduleController::class, 'byCourse'], $auth);
$router->post('/courses/{courseId}/schedule', [ClassScheduleController::class, 'store'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/schedule/{id}', [ClassScheduleController::class, 'destroy'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));

// ── Class Schedule (date-based lectures) ────────────────────
$router->get('/schedules', [ScheduleController::class, 'index'], $auth);
$router->get('/schedules/{id}', [ScheduleController::class, 'show'], $auth);
$router->post('/schedules', [ScheduleController::class, 'store'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/schedules/bulk', [ScheduleController::class, 'bulk'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/schedules/{id}', [ScheduleController::class, 'update'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/schedules/{id}', [ScheduleController::class, 'destroy'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->patch('/schedules/{id}/cancel', [ScheduleController::class, 'cancel'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->patch('/schedules/{id}/complete', [ScheduleController::class, 'complete'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->patch('/schedules/{id}/status', [ScheduleController::class, 'setStatus'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->patch('/schedules/{id}/reschedule', [ScheduleController::class, 'reschedule'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/schedules/{id}/duplicate', [ScheduleController::class, 'duplicate'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/schedules/{id}/attachment', [ScheduleController::class, 'uploadAttachment'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/courses/{courseId}/schedules/months', [ScheduleController::class, 'listMonthUploads'], $auth);
$router->post('/courses/{courseId}/schedules/import', [ScheduleController::class, 'importMonth'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/courses/{courseId}/schedules/month/{month}', [ScheduleController::class, 'clearMonth'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));

// ── Batches ─────────────────────────────────────────────────
$router->get('/courses/{courseId}/batches', [BatchController::class, 'index'], $auth);
$router->post('/batches', [BatchController::class, 'store'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/batches/{id}', [BatchController::class, 'update'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/batches/{id}', [BatchController::class, 'destroy'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/batches/{id}/students', [BatchController::class, 'students'], $auth);
$router->post('/batches/{id}/students', [BatchController::class, 'assign'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/batches/{id}/students/{studentId}', [BatchController::class, 'unassign'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));

// ── AI Learning Assistant (Teacher review workflow) ─────────
$router->post('/ai/lectures/{lectureId}/generate', [AiController::class, 'generate'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/ai/status', [AiController::class, 'status'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/ai/jobs/{jobId}/process', [AiController::class, 'process'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/ai/lectures/{lectureId}/job', [AiController::class, 'jobStatus'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->get('/ai/lectures/{lectureId}/review', [AiController::class, 'review'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/ai/lectures/{lectureId}/content', [AiController::class, 'updateContent'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/ai/lectures/{lectureId}/flashcards', [AiController::class, 'addFlashcard'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/ai/flashcards/{id}', [AiController::class, 'updateFlashcard'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/ai/flashcards/{id}', [AiController::class, 'deleteFlashcard'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/ai/lectures/{lectureId}/mcqs', [AiController::class, 'addMcq'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/ai/mcqs/{id}', [AiController::class, 'updateMcq'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->delete('/ai/mcqs/{id}', [AiController::class, 'deleteMcq'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/ai/lectures/{lectureId}/approve', [AiController::class, 'approve'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/ai/lectures/{lectureId}/import/word', [AiController::class, 'importWordPack'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/ai/lectures/{lectureId}/import/flashcards', [AiController::class, 'importFlashcards'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/ai/lectures/{lectureId}/import/mcqs', [AiController::class, 'importMcqs'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->post('/ai/lectures/{lectureId}/publish', [AiController::class, 'publish'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/ai/lectures/{lectureId}/challenge', [AiController::class, 'saveChallenge'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));

// ── Student AI Learning (Revision, Flashcards, MCQs, Challenge) ──
$student = array_merge($auth, [RoleMiddleware::student()]);
$router->get('/student/revision/lectures', [StudentAiController::class, 'revisionLectures'], $student);
$router->get('/student/revision/lectures/{lectureId}', [StudentAiController::class, 'revisionContent'], $student);
$router->get('/student/flashcards', [StudentAiController::class, 'flashcards'], $student);
$router->post('/student/flashcards/{id}/progress', [StudentAiController::class, 'flashcardProgress'], $student);
$router->get('/student/lectures/{lectureId}/mcqs', [StudentAiController::class, 'mcqPractice'], $student);
$router->get('/student/challenge/today', [StudentAiController::class, 'challengeToday'], $student);
$router->post('/student/mcqs/attempt', [StudentAiController::class, 'submitAttempt'], $student);
$router->get('/student/mcqs/attempts', [StudentAiController::class, 'recentAttempts'], $student);
$router->post('/student/bookmarks/toggle', [StudentAiController::class, 'toggleBookmark'], $student);
$router->get('/student/bookmarks', [StudentAiController::class, 'listBookmarks'], $student);
$router->post('/student/lectures/{lectureId}/highlights', [StudentAiController::class, 'addHighlight'], $student);
$router->delete('/student/highlights/{id}', [StudentAiController::class, 'deleteHighlight'], $student);

// ── Student Progress (Streak, Badges, Analytics) ────────────
$router->post('/student/activity/ping', [ProgressController::class, 'ping'], $student);
$router->get('/student/streak', [ProgressController::class, 'streak'], $student);
$router->get('/student/analytics', [ProgressController::class, 'analytics'], $student);
$router->get('/student/badges', [ProgressController::class, 'badges'], $student);

// ── Premium study features ──────────────────────────────────
$router->get('/student/premium/dashboard', [PremiumStudyController::class, 'dashboard'], $student);
$router->get('/student/premium/daily-challenge', [PremiumStudyController::class, 'dailyChallenge'], $student);
$router->get('/student/premium/daily-challenge/history', [PremiumStudyController::class, 'dailyHistory'], $student);
$router->get('/student/premium/weak-areas', [PremiumStudyController::class, 'weakAreas'], $student);
$router->get('/student/premium/study-plan', [PremiumStudyController::class, 'getStudyPlan'], $student);
$router->put('/student/premium/study-plan', [PremiumStudyController::class, 'saveStudyPlan'], $student);
$router->patch('/student/premium/study-plan/tasks/{id}', [PremiumStudyController::class, 'completeTask'], $student);
$router->get('/student/premium/question-bank/filters', [PremiumStudyController::class, 'questionBankFilters'], $student);
$router->get('/student/premium/question-bank', [PremiumStudyController::class, 'questionBank'], $student);
$router->post('/student/premium/question-bank/practice', [PremiumStudyController::class, 'questionBankPractice'], $student);
$router->get('/student/premium/mistakes/stats', [PremiumStudyController::class, 'mistakeStats'], $student);
$router->get('/student/premium/mistakes/practice', [PremiumStudyController::class, 'mistakesPractice'], $student);
$router->get('/student/premium/mistakes', [PremiumStudyController::class, 'mistakes'], $student);
$router->post('/student/premium/revision/start', [PremiumStudyController::class, 'startRevision'], $student);
$router->post('/student/premium/revision/{id}/complete', [PremiumStudyController::class, 'completeRevision'], $student);

// ── Lecture Discussions (nested, likes, pins, reports) ──────
$router->get('/discussions/lecture/{lectureId}', [DiscussionController::class, 'byLecture'], $auth);
$router->post('/discussions/replies/{id}/like', [DiscussionController::class, 'likeReply'], $auth);
$router->patch('/discussions/replies/{id}/flag', [DiscussionController::class, 'flagReply'], array_merge($auth, [RoleMiddleware::adminOrTeacher()]));
$router->put('/discussions/replies/{id}', [DiscussionController::class, 'editReply'], $auth);
$router->delete('/discussions/replies/{id}', [DiscussionController::class, 'deleteReply'], $auth);
$router->post('/discussions/report', [DiscussionController::class, 'report'], $auth);

// ── Cron (class reminders + streak reminders) ───────────────
$router->get('/cron/class-reminders', [CronController::class, 'classReminders'], $public);
$router->get('/cron/streak-reminders', [CronController::class, 'streakReminders'], $public);
