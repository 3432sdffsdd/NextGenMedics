<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\DiscussionRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ContentRepository;
use App\Services\CourseService;
use App\Services\NotificationService;

class DiscussionController extends BaseController
{
    public function __construct(
        private DiscussionRepository $discussions,
        private CourseRepository $courses,
        private ContentRepository $content,
        private CourseService $courseService,
        private NotificationService $notifier
    ) {}

    public function index(Request $request): void
    {
        $courseId = (int) $request->param('courseId');
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $result = $this->discussions->listByCourse($courseId, $request->page(), $request->perPage());
        Response::paginated($result['items'], $result['total'], $request->page(), $request->perPage());
    }

    public function byLecture(Request $request): void
    {
        $lectureId = (int) $request->param('lectureId');
        $courseId = $this->content->getLectureCourseId($lectureId);
        if (!$courseId || !$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($this->discussions->listByLecture($lectureId));
    }

    public function show(Request $request): void
    {
        $thread = $this->discussions->findThread((int) $request->param('id'));
        if (!$thread) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $thread['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $thread['replies'] = $this->discussions->getReplies((int) $thread['id'], $request->userId());
        Response::success($thread);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request, [
            'course_id' => 'required|integer',
            'title'     => 'required|min:3',
            'content'   => 'required|min:3',
        ]);
        if (!$data) return;

        $courseId = (int) $data['course_id'];
        if (!$this->courseService->canAccess($courseId, $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $lectureId = $request->input('lecture_id');
        $lectureId = ($lectureId !== null && $lectureId !== '' && $lectureId !== 'undefined')
            ? (int) $lectureId
            : null;

        try {
            $id = $this->discussions->createThread([
                'course_id'  => $courseId,
                'lecture_id' => $lectureId,
                'author_id'  => $request->userId(),
                'title'      => trim((string) $data['title']),
                'content'    => trim((string) $data['content']),
            ]);
        } catch (\Throwable $e) {
            Response::error('Could not create discussion: ' . $e->getMessage(), 422);
            return;
        }

        try {
            if ($request->userRole() === 'student') {
                $course = $this->courses->findById($courseId);
                $teacherIds = $this->courses->getTeacherIdsForNotify($courseId);
                if ($course && $teacherIds) {
                    $this->notifier->notifyCourseTeachers(
                        $courseId,
                        $teacherIds,
                        $request->userId(),
                        'discussion_question',
                        'New student question',
                        "New question in {$course['title']}: \"{$data['title']}\".",
                        ['thread_id' => $id]
                    );
                }
            } elseif (in_array($request->userRole(), ['teacher', 'admin'], true)) {
                $course = $this->courses->findById($courseId);
                $studentIds = array_column($this->courses->getEnrolledStudents($courseId), 'id');
                if ($course && $studentIds) {
                    $this->notifier->notifyMany(
                        $studentIds,
                        'new_discussion',
                        'New discussion posted',
                        "Your teacher posted \"{$data['title']}\" in {$course['title']}.",
                        ['thread_id' => $id, 'course_id' => $courseId],
                        false
                    );
                }
            }
        } catch (\Throwable) {
            // Discussion was created; notification failure must not block the post.
        }

        Response::success(['id' => $id], 'Thread created', 201);
    }

    public function reply(Request $request): void
    {
        $data = $this->validate($request, ['content' => 'required|min:1']);
        if (!$data) return;

        $thread = $this->discussions->findThread((int) $request->param('id'));
        if (!$thread || !$this->courseService->canAccess((int) $thread['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $isAnswer = in_array($request->userRole(), ['teacher', 'admin'], true)
            && $request->input('is_answer');

        $id = $this->discussions->createReply([
            'thread_id' => $thread['id'],
            'parent_id' => $request->input('parent_id') ?: null,
            'author_id' => $request->userId(),
            'content'   => $data['content'],
            'is_answer' => $isAnswer ? 1 : 0,
        ]);

        $recipients = [];
        if ((int) $thread['author_id'] !== $request->userId()) {
            $recipients[] = (int) $thread['author_id'];
        }
        if ($request->userRole() === 'student') {
            foreach ($this->courses->getTeacherIdsForNotify((int) $thread['course_id']) as $teacherId) {
                if ($teacherId !== $request->userId()) {
                    $recipients[] = $teacherId;
                }
            }
        }
        $isTeacher = in_array($request->userRole(), ['teacher', 'admin'], true);
        foreach (array_unique($recipients) as $userId) {
            $this->notifier->notify(
                $userId,
                'discussion_reply',
                $isTeacher ? 'Your teacher replied' : 'New reply on your question',
                ($isTeacher ? 'Your teacher replied to' : 'Someone replied to') . " \"{$thread['title']}\".",
                ['thread_id' => $thread['id'], 'course_id' => (int) $thread['course_id']],
                false
            );
        }

        Response::success(['id' => $id], 'Reply posted', 201);
    }

    public function likeReply(Request $request): void
    {
        $reply = $this->discussions->findReply((int) $request->param('id'));
        if (!$reply || !$this->replyAccessible($request, (int) $reply['thread_id'])) {
            Response::error('Forbidden', 403);
            return;
        }
        Response::success($this->discussions->toggleLike((int) $reply['id'], $request->userId()));
    }

    /** Teacher/admin: pin a reply, mark it as the teacher-approved answer. */
    public function flagReply(Request $request): void
    {
        if (!$this->requireRole($request, ['admin', 'teacher'])) return;

        $reply = $this->discussions->findReply((int) $request->param('id'));
        if (!$reply || !$this->replyAccessible($request, (int) $reply['thread_id'])) {
            Response::error('Forbidden', 403);
            return;
        }

        $flags = array_intersect_key($request->body(), array_flip(['is_pinned', 'is_teacher_approved', 'is_answer']));
        $this->discussions->updateReplyFlags((int) $reply['id'], $flags);

        if (!empty($flags['is_teacher_approved']) && (int) $reply['author_id'] !== $request->userId()) {
            $this->notifier->notify(
                (int) $reply['author_id'],
                'answer_approved',
                'Your answer was approved',
                'A teacher marked your reply as a verified answer.',
                ['reply_id' => $reply['id']],
                false
            );
        }
        Response::success(null, 'Reply updated');
    }

    /** Author can edit their own reply. */
    public function editReply(Request $request): void
    {
        $reply = $this->discussions->findReply((int) $request->param('id'));
        if (!$reply) {
            Response::error('Not found', 404);
            return;
        }
        if ((int) $reply['author_id'] !== $request->userId()) {
            $isStaff = in_array($request->userRole(), ['teacher', 'admin'], true)
                && $this->replyAccessible($request, (int) $reply['thread_id']);
            if (!$isStaff) {
                Response::error('You can only edit your own comment', 403);
                return;
            }
        }
        $data = $this->validate($request, ['content' => 'required|min:1']);
        if (!$data) return;

        $this->discussions->updateReplyContent((int) $reply['id'], $data['content']);
        Response::success(null, 'Comment updated');
    }

    /** Author, teacher, or admin can delete a reply. */
    public function deleteReply(Request $request): void
    {
        $reply = $this->discussions->findReply((int) $request->param('id'));
        if (!$reply) {
            Response::error('Not found', 404);
            return;
        }
        $isOwner = (int) $reply['author_id'] === $request->userId();
        $isStaff = in_array($request->userRole(), ['teacher', 'admin'], true) && $this->replyAccessible($request, (int) $reply['thread_id']);
        if (!$isOwner && !$isStaff) {
            Response::error('Forbidden', 403);
            return;
        }
        $this->discussions->deleteReply((int) $reply['id']);
        Response::success(null, 'Reply deleted');
    }

    /** Author or teacher/admin can edit a discussion thread. */
    public function update(Request $request): void
    {
        $thread = $this->discussions->findThread((int) $request->param('id'));
        if (!$thread) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $thread['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $isOwner = (int) $thread['author_id'] === $request->userId();
        $isStaff = in_array($request->userRole(), ['teacher', 'admin'], true);
        if (!$isOwner && !$isStaff) {
            Response::error('You can only edit your own discussion', 403);
            return;
        }

        $data = $this->validate($request, [
            'title'   => 'required|min:3',
            'content' => 'required|min:3',
        ]);
        if (!$data) return;

        $this->discussions->updateThreadContent((int) $thread['id'], trim($data['title']), trim($data['content']));
        Response::success($this->discussions->findThread((int) $thread['id']), 'Discussion updated');
    }

    /** Author or teacher/admin can delete a discussion thread (and all replies). */
    public function destroy(Request $request): void
    {
        $thread = $this->discussions->findThread((int) $request->param('id'));
        if (!$thread) {
            Response::error('Not found', 404);
            return;
        }
        if (!$this->courseService->canAccess((int) $thread['course_id'], $request->user())) {
            Response::error('Forbidden', 403);
            return;
        }

        $isOwner = (int) $thread['author_id'] === $request->userId();
        $isStaff = in_array($request->userRole(), ['teacher', 'admin'], true);
        if (!$isOwner && !$isStaff) {
            Response::error('Forbidden', 403);
            return;
        }

        $this->discussions->deleteThread((int) $thread['id']);
        Response::success(null, 'Discussion deleted');
    }

    public function report(Request $request): void
    {
        $replyId = $request->input('reply_id') ? (int) $request->input('reply_id') : null;
        $threadId = $request->input('thread_id') ? (int) $request->input('thread_id') : null;
        if (!$replyId && !$threadId) {
            Response::error('Nothing to report', 422);
            return;
        }
        $this->discussions->report($request->userId(), $threadId, $replyId, $request->input('reason'));
        Response::success(null, 'Reported. Our moderators will review it.');
    }

    public function moderate(Request $request): void
    {
        if (!$this->requireRole($request, ['admin'])) return;

        $this->discussions->updateThread((int) $request->param('id'), $request->body());
        Response::success(null, 'Thread moderated');
    }

    private function replyAccessible(Request $request, int $threadId): bool
    {
        $thread = $this->discussions->findThread($threadId);
        return $thread && $this->courseService->canAccess((int) $thread['course_id'], $request->user());
    }
}
