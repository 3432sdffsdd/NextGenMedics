<?php
namespace App\Services\GoalPlanner;

use App\Repositories\CourseRepository;
use PDO;

/**
 * Builds a selectable content tree from enrolled LMS courses (no duplication).
 */
class ContentCatalogService
{
    public function __construct(
        private CourseRepository $courses,
        private PDO $db
    ) {}

    public function catalogForStudent(int $studentId): array
    {
        $enrolled = $this->courses->listByStudent($studentId);
        $subjects = [];

        foreach ($enrolled as $course) {
            $courseId = (int) $course['id'];
            $lectures = $this->lecturesForCourse($courseId);
            $quizzes = $this->publishedQuizzes($courseId);

            $lectureNodes = [];
            foreach ($lectures as $lec) {
                $lid = (int) $lec['id'];
                $resources = $this->resourcesForLecture($lid);
                $videos = array_values(array_filter($resources, static fn($r) => ($r['type'] ?? '') === 'video'));
                $notes = array_values(array_filter($resources, static fn($r) => in_array($r['type'] ?? '', ['pdf', 'slides', 'reference', 'link'], true)));
                $fcCount = $this->flashcardCount($lid);
                $hasRevision = $this->hasPublishedRevision($lid);

                $lectureNodes[] = [
                    'id'            => $lid,
                    'title'         => $lec['title'],
                    'module_title'  => $lec['module_title'],
                    'chapter_title' => $lec['chapter_title'],
                    'videos'        => array_map(static fn($r) => [
                        'id' => (int) $r['id'], 'title' => $r['title'], 'type' => 'video',
                    ], $videos),
                    'notes'         => array_map(static fn($r) => [
                        'id' => (int) $r['id'], 'title' => $r['title'], 'type' => $r['type'],
                    ], $notes),
                    'flashcard_count' => $fcCount,
                    'has_revision'    => $hasRevision,
                    'quizzes'         => [], // course-level quizzes listed below; lecture-linked if any
                ];
            }

            $subjects[] = [
                'course_id'    => $courseId,
                'title'        => $course['title'],
                'lectures'     => $lectureNodes,
                'quizzes'      => array_map(static fn($q) => [
                    'id' => (int) $q['id'],
                    'title' => $q['title'],
                    'question_count' => (int) ($q['question_count'] ?? 0),
                    'status' => $q['status'],
                ], $quizzes),
                'totals' => [
                    'lectures'   => count($lectureNodes),
                    'quizzes'    => count($quizzes),
                    'videos'     => array_sum(array_map(static fn($l) => count($l['videos']), $lectureNodes)),
                    'notes'      => array_sum(array_map(static fn($l) => count($l['notes']), $lectureNodes)),
                    'flashcards' => array_sum(array_map(static fn($l) => $l['flashcard_count'], $lectureNodes)),
                    'revision'   => count(array_filter($lectureNodes, static fn($l) => $l['has_revision'])),
                ],
            ];
        }

        return ['subjects' => $subjects, 'goal_types' => $this->goalTypes()];
    }

    public function goalTypes(): array
    {
        return [
            ['value' => 'full_syllabus', 'label' => 'Complete Entire FCPS Part 1 Syllabus'],
            ['value' => 'selected_subjects', 'label' => 'Complete Selected Subjects'],
            ['value' => 'selected_lectures', 'label' => 'Complete Selected Lectures'],
            ['value' => 'selected_quizzes', 'label' => 'Complete Selected Quizzes'],
            ['value' => 'selected_flashcards', 'label' => 'Complete Selected Flashcards'],
            ['value' => 'selected_notes', 'label' => 'Complete Selected Notes'],
            ['value' => 'mock_exam', 'label' => 'Prepare for Mock Exam'],
            ['value' => 'revision_only', 'label' => 'Revision Only'],
            ['value' => 'custom', 'label' => 'Custom Goal'],
        ];
    }

    /**
     * Expand goal + selection into concrete plan items.
     *
     * @param list<array{type:string,ref_id:int,course_id?:int,lecture_id?:int}> $selection
     */
    public function resolveItems(int $studentId, string $goalType, array $selection): array
    {
        $catalog = $this->catalogForStudent($studentId);
        $items = [];

        if ($goalType === 'full_syllabus' || ($goalType === 'selected_subjects' && !$selection)) {
            foreach ($catalog['subjects'] as $sub) {
                $items = array_merge($items, $this->itemsForSubject($sub, true, true, true, true, true));
            }
            return $this->dedupe($items);
        }

        if ($goalType === 'revision_only') {
            foreach ($catalog['subjects'] as $sub) {
                foreach ($sub['lectures'] as $lec) {
                    if ($lec['has_revision']) {
                        $items[] = $this->item('revision', $lec['id'], $sub['course_id'], $lec['id'], $sub['title'], 'Revision: ' . $lec['title'], 30);
                    }
                }
            }
            return $this->dedupe($items);
        }

        if ($goalType === 'mock_exam') {
            foreach ($catalog['subjects'] as $sub) {
                foreach ($sub['quizzes'] as $q) {
                    $items[] = $this->item('quiz', $q['id'], $sub['course_id'], null, $sub['title'], 'Quiz: ' . $q['title'], 40);
                }
                foreach ($sub['lectures'] as $lec) {
                    if ($lec['has_revision']) {
                        $items[] = $this->item('revision', $lec['id'], $sub['course_id'], $lec['id'], $sub['title'], 'Revision: ' . $lec['title'], 25);
                    }
                }
            }
            return $this->dedupe($items);
        }

        // Selection-driven goals
        $byCourse = [];
        foreach ($catalog['subjects'] as $sub) {
            $byCourse[$sub['course_id']] = $sub;
        }

        foreach ($selection as $sel) {
            $type = $sel['type'] ?? '';
            $refId = (int) ($sel['ref_id'] ?? 0);
            $courseId = (int) ($sel['course_id'] ?? 0);
            $sub = $byCourse[$courseId] ?? null;

            if ($type === 'subject' && $sub) {
                $items = array_merge($items, $this->itemsForSubject($sub, true, true, true, true, true));
                continue;
            }
            if ($type === 'lecture' && $sub) {
                foreach ($sub['lectures'] as $lec) {
                    if ($lec['id'] === $refId) {
                        $items = array_merge($items, $this->itemsForLecture($sub, $lec, true, true, true, true));
                    }
                }
                continue;
            }
            if ($type === 'quiz' && $sub) {
                foreach ($sub['quizzes'] as $q) {
                    if ($q['id'] === $refId) {
                        $items[] = $this->item('quiz', $q['id'], $courseId, null, $sub['title'], 'Quiz: ' . $q['title'], 40);
                    }
                }
                continue;
            }
            if ($type === 'video' || $type === 'note') {
                $title = (string) ($sel['title'] ?? ucfirst($type));
                $lectureId = (int) ($sel['lecture_id'] ?? 0) ?: null;
                $subject = $sub['title'] ?? 'Course';
                $items[] = $this->item($type, $refId, $courseId ?: null, $lectureId, $subject, $title, $type === 'video' ? 40 : 30);
                continue;
            }
            if ($type === 'flashcard_set' && $sub) {
                foreach ($sub['lectures'] as $lec) {
                    if ($lec['id'] === $refId && $lec['flashcard_count'] > 0) {
                        $items[] = $this->item('flashcard_set', $lec['id'], $courseId, $lec['id'], $sub['title'],
                            'Flashcards: ' . $lec['title'] . ' (' . $lec['flashcard_count'] . ')', 25);
                    }
                }
                continue;
            }
            if ($type === 'revision' && $sub) {
                foreach ($sub['lectures'] as $lec) {
                    if ($lec['id'] === $refId && $lec['has_revision']) {
                        $items[] = $this->item('revision', $lec['id'], $courseId, $lec['id'], $sub['title'], 'Revision: ' . $lec['title'], 30);
                    }
                }
            }
        }

        // Goal-type filters
        if ($goalType === 'selected_lectures') {
            $items = array_values(array_filter($items, static fn($i) => in_array($i['item_type'], ['lecture', 'video'], true)));
        } elseif ($goalType === 'selected_quizzes') {
            $items = array_values(array_filter($items, static fn($i) => $i['item_type'] === 'quiz'));
        } elseif ($goalType === 'selected_flashcards') {
            $items = array_values(array_filter($items, static fn($i) => $i['item_type'] === 'flashcard_set'));
        } elseif ($goalType === 'selected_notes') {
            $items = array_values(array_filter($items, static fn($i) => $i['item_type'] === 'note'));
        }

        return $this->dedupe($items);
    }

    private function itemsForSubject(array $sub, bool $lec, bool $vid, bool $notes, bool $quiz, bool $fc): array
    {
        $items = [];
        foreach ($sub['lectures'] as $lecture) {
            $items = array_merge($items, $this->itemsForLecture($sub, $lecture, $lec, $vid, $notes, $fc));
        }
        if ($quiz) {
            foreach ($sub['quizzes'] as $q) {
                $items[] = $this->item('quiz', $q['id'], $sub['course_id'], null, $sub['title'], 'Quiz: ' . $q['title'], 40);
            }
        }
        return $items;
    }

    private function itemsForLecture(array $sub, array $lec, bool $includeLec, bool $vid, bool $notes, bool $fc): array
    {
        $items = [];
        if ($includeLec) {
            $items[] = $this->item('lecture', $lec['id'], $sub['course_id'], $lec['id'], $sub['title'], 'Lecture: ' . $lec['title'], 45);
        }
        if ($vid) {
            foreach ($lec['videos'] as $v) {
                $items[] = $this->item('video', $v['id'], $sub['course_id'], $lec['id'], $sub['title'], 'Watch: ' . $v['title'], 40);
            }
        }
        if ($notes) {
            foreach ($lec['notes'] as $n) {
                $items[] = $this->item('note', $n['id'], $sub['course_id'], $lec['id'], $sub['title'], 'Notes: ' . $n['title'], 30);
            }
        }
        if ($fc && $lec['flashcard_count'] > 0) {
            $items[] = $this->item('flashcard_set', $lec['id'], $sub['course_id'], $lec['id'], $sub['title'],
                'Flashcards: ' . $lec['title'] . ' (' . $lec['flashcard_count'] . ')', 25);
        }
        if ($lec['has_revision']) {
            $items[] = $this->item('revision', $lec['id'], $sub['course_id'], $lec['id'], $sub['title'], 'Revision: ' . $lec['title'], 30);
        }
        return $items;
    }

    private function item(string $type, int $refId, ?int $courseId, ?int $lectureId, string $subject, string $title, int $mins): array
    {
        return [
            'item_type'          => $type,
            'ref_id'             => $refId,
            'course_id'          => $courseId,
            'lecture_id'         => $lectureId,
            'subject_title'      => $subject,
            'title'              => $title,
            'estimated_minutes'  => $mins,
        ];
    }

    private function dedupe(array $items): array
    {
        $seen = [];
        $out = [];
        foreach ($items as $i) {
            $k = $i['item_type'] . ':' . $i['ref_id'];
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[] = $i;
        }
        return $out;
    }

    private function lecturesForCourse(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT l.id, l.title, ch.title AS chapter_title, m.title AS module_title
             FROM lectures l
             JOIN chapters ch ON ch.id = l.chapter_id
             JOIN modules m ON m.id = ch.module_id
             WHERE m.course_id = ?
             ORDER BY m.sort_order, ch.sort_order, l.sort_order, l.id'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    private function resourcesForLecture(int $lectureId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, type, title FROM lecture_resources WHERE lecture_id = ? ORDER BY sort_order, id'
        );
        $stmt->execute([$lectureId]);
        return $stmt->fetchAll();
    }

    private function publishedQuizzes(int $courseId): array
    {
        $stmt = $this->db->prepare(
            "SELECT q.id, q.title, q.status,
                    (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count
             FROM quizzes q
             WHERE q.course_id = ? AND q.status = 'published'
             ORDER BY q.created_at DESC"
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    private function flashcardCount(int $lectureId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM flashcards WHERE lecture_id = ? AND status IN ('approved','published')"
        );
        try {
            $stmt->execute([$lectureId]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM flashcards WHERE lecture_id = ? AND status = 'approved'");
            $stmt->execute([$lectureId]);
            return (int) $stmt->fetchColumn();
        }
    }

    private function hasPublishedRevision(int $lectureId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM ai_lecture_content WHERE lecture_id = ? AND status = 'published'"
            );
            $stmt->execute([$lectureId]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
