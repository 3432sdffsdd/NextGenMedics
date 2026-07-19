<?php
namespace App\Repositories;

/**
 * Teacher-uploaded quiz questions used as the Daily Challenge / Weak Areas bank.
 * Only published quizzes, single-choice / true-false style items.
 */
class QuizQuestionBankRepository extends BaseRepository
{
    private const LETTERS = ['A', 'B', 'C', 'D', 'E'];

    public function countAvailableForCourses(array $courseIds): int
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM quiz_questions qq
             JOIN quizzes q ON q.id = qq.quiz_id
             WHERE q.status = 'published'
               AND q.course_id IN ({$in})
               AND qq.question_type IN ('single_choice','true_false','multiple_choice')"
        );
        $stmt->execute($courseIds);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Random unseen question IDs from published quizzes in enrolled courses.
     * @param list<int> $excludeIds
     * @return list<int>
     */
    public function pickRandomIds(array $courseIds, int $limit, array $excludeIds = []): array
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds || $limit <= 0) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $params = $courseIds;
        $excludeSql = '';
        $excludeIds = array_values(array_filter(array_map('intval', $excludeIds)));
        if ($excludeIds) {
            $ex = implode(',', array_fill(0, count($excludeIds), '?'));
            $excludeSql = " AND qq.id NOT IN ({$ex})";
            $params = array_merge($params, $excludeIds);
        }
        $params[] = $limit;
        $stmt = $this->db->prepare(
            "SELECT qq.id FROM quiz_questions qq
             JOIN quizzes q ON q.id = qq.quiz_id
             WHERE q.status = 'published'
               AND q.course_id IN ({$in})
               AND qq.question_type IN ('single_choice','true_false','multiple_choice')
               {$excludeSql}
             ORDER BY RAND()
             LIMIT ?"
        );
        $stmt->execute($params);
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }

    /**
     * Load questions shaped for McqPlayer (option_a…option_e).
     * @param list<int> $ids
     * @return list<array>
     */
    public function findByIds(array $ids, bool $includeCorrect = false): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT qq.id, qq.question_text, qq.explanation, qq.question_type, qq.marks, qq.quiz_id,
                    q.title AS quiz_title, q.course_id, c.title AS course_title
             FROM quiz_questions qq
             JOIN quizzes q ON q.id = qq.quiz_id
             JOIN courses c ON c.id = q.course_id
             WHERE qq.id IN ({$in})"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();
        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row['id']] = $row;
        }

        $out = [];
        foreach ($ids as $id) {
            if (!isset($byId[$id])) {
                continue;
            }
            $out[] = $this->toPlayerQuestion($byId[$id], $includeCorrect);
        }
        return $out;
    }

    public function findOneWithOptions(int $questionId, bool $includeCorrect = true): ?array
    {
        $rows = $this->findByIds([$questionId], $includeCorrect);
        return $rows[0] ?? null;
    }

    private function toPlayerQuestion(array $row, bool $includeCorrect): array
    {
        $opts = $this->db->prepare(
            'SELECT id, option_text, is_correct, sort_order
             FROM quiz_question_options WHERE question_id = ? ORDER BY sort_order, id'
        );
        $opts->execute([(int) $row['id']]);
        $options = array_slice($opts->fetchAll(), 0, 5);

        $qid = (int) $row['id'];
        $q = [
            'id'           => $qid,
            'raw_id'       => $qid,
            'bank_id'      => 'quiz-' . $qid,
            'question'     => (string) $row['question_text'],
            'explanation'  => $row['explanation'] ?? null,
            'topic'        => $row['quiz_title'] ?? null,
            'subject'      => $row['quiz_title'] ?? null, // topic/lecture label (not course)
            'chapter'      => $row['quiz_title'] ?? null,
            'course_title' => $row['course_title'] ?? null,
            'course_id'    => (int) ($row['course_id'] ?? 0),
            'quiz_id'      => (int) ($row['quiz_id'] ?? 0),
            'question_type'=> $row['question_type'] ?? 'single_choice',
            'source_type'  => 'quiz',
        ];

        $correctLetter = null;
        foreach ($options as $i => $opt) {
            $letter = self::LETTERS[$i] ?? null;
            if (!$letter) {
                break;
            }
            $q['option_' . strtolower($letter)] = $opt['option_text'];
            $q['option_id_' . strtolower($letter)] = (int) $opt['id'];
            if (!empty($opt['is_correct']) && $correctLetter === null) {
                $correctLetter = $letter;
            }
        }
        // Pad missing letters for McqPlayer
        foreach (self::LETTERS as $letter) {
            $key = 'option_' . strtolower($letter);
            if (!isset($q[$key])) {
                $q[$key] = '';
            }
        }
        if ($includeCorrect) {
            $q['correct_option'] = $correctLetter;
        }
        return $q;
    }

    /**
     * Accuracy by topic (= quiz title). MCQs belong to the quiz/topic, not the course name.
     * @return list<array{topic:string,subject:string,total:int,correct:int,accuracy:int}>
     */
    public function accuracyByTopic(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT q.title AS topic,
                    q.title AS subject,
                    COUNT(*) AS total,
                    SUM(CASE WHEN h.is_correct = 1 THEN 1 ELSE 0 END) AS correct,
                    ROUND(100 * SUM(CASE WHEN h.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) AS accuracy
             FROM student_question_history h
             JOIN quiz_questions qq ON qq.id = h.question_id
             JOIN quizzes q ON q.id = qq.quiz_id
             WHERE h.student_id = ?
             GROUP BY q.id, q.title
             HAVING total >= 1
             ORDER BY accuracy ASC, total DESC"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /** @deprecated use accuracyByTopic */
    public function accuracyByCourse(int $studentId): array
    {
        return $this->accuracyByTopic($studentId);
    }

    /** Same grouping as topics (quiz title). Kept for callers expecting chapter key. */
    public function accuracyByQuiz(int $studentId): array
    {
        $rows = $this->accuracyByTopic($studentId);
        return array_map(static function ($r) {
            return [
                'chapter'  => $r['topic'],
                'topic'    => $r['topic'],
                'subject'  => $r['topic'],
                'total'    => $r['total'],
                'correct'  => $r['correct'],
                'accuracy' => $r['accuracy'],
            ];
        }, $rows);
    }

    public function overallStats(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS attempted,
                    SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) AS correct,
                    SUM(CASE WHEN is_correct = 0 THEN 1 ELSE 0 END) AS incorrect
             FROM student_question_history WHERE student_id = ?"
        );
        $stmt->execute([$studentId]);
        $row = $stmt->fetch() ?: ['attempted' => 0, 'correct' => 0, 'incorrect' => 0];
        $attempted = (int) $row['attempted'];
        $correct = (int) $row['correct'];
        return [
            'attempted' => $attempted,
            'correct'   => $correct,
            'incorrect' => (int) $row['incorrect'],
            'accuracy'  => $attempted > 0 ? (int) round(100 * $correct / $attempted) : 0,
        ];
    }

    public function countDailyCompleted(int $studentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM daily_challenge_sets
             WHERE student_id = ? AND completed_at IS NOT NULL'
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Published quiz topics available to the student (same quizzes teachers uploaded).
     * @return list<array{id:int,title:string,question_count:int,course_title:string}>
     */
    public function topicsForCourses(array $courseIds): array
    {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT q.id, q.title,
                    c.title AS course_title,
                    COUNT(qq.id) AS question_count
             FROM quizzes q
             JOIN courses c ON c.id = q.course_id
             JOIN quiz_questions qq ON qq.quiz_id = q.id
               AND qq.question_type IN ('single_choice','true_false','multiple_choice')
             WHERE q.status = 'published' AND q.course_id IN ({$in})
             GROUP BY q.id, q.title, c.title
             HAVING question_count > 0
             ORDER BY c.title ASC, q.title ASC"
        );
        $stmt->execute($courseIds);
        return array_map(static function ($r) {
            return [
                'id'             => (int) $r['id'],
                'title'          => (string) $r['title'],
                'course_title'   => (string) $r['course_title'],
                'question_count' => (int) $r['question_count'],
            ];
        }, $stmt->fetchAll());
    }

    /**
     * Question Bank browse: teacher quiz MCQs with attempted flag.
     * attempt_filter: '' | unattempted | attempted
     */
    public function searchBank(
        int $studentId,
        array $courseIds,
        array $filters,
        int $page = 1,
        int $perPage = 20
    ): array {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds) {
            return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
        }

        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $where = [
            "q.status = 'published'",
            "q.course_id IN ({$in})",
            "qq.question_type IN ('single_choice','true_false','multiple_choice')",
        ];
        $params = $courseIds;

        if (!empty($filters['quiz_id'])) {
            $where[] = 'q.id = ?';
            $params[] = (int) $filters['quiz_id'];
        }
        if (!empty($filters['topic'])) {
            $where[] = 'q.title = ?';
            $params[] = $filters['topic'];
        }
        if (!empty($filters['search'])) {
            $where[] = 'qq.question_text LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $attemptFilter = (string) ($filters['attempt_filter'] ?? '');
        if ($attemptFilter === 'unattempted') {
            $where[] = 'h.id IS NULL';
        } elseif ($attemptFilter === 'attempted') {
            $where[] = 'h.id IS NOT NULL';
        }

        $sqlWhere = implode(' AND ', $where);
        $joinHistory = 'LEFT JOIN student_question_history h ON h.question_id = qq.id AND h.student_id = ?';
        // student_id for join must come before course filters in params for the JOIN... 
        // Actually put student id first in params for join
        $countParams = array_merge([$studentId], $params);
        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM quiz_questions qq
             JOIN quizzes q ON q.id = qq.quiz_id
             {$joinHistory}
             WHERE {$sqlWhere}"
        );
        $countStmt->execute($countParams);
        $total = (int) $countStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $listParams = array_merge([$studentId], $params);
        $stmt = $this->db->prepare(
            "SELECT qq.id, qq.question_text, qq.explanation, qq.quiz_id,
                    q.title AS topic, c.title AS course_title,
                    CASE WHEN h.id IS NULL THEN 0 ELSE 1 END AS attempted,
                    h.is_correct
             FROM quiz_questions qq
             JOIN quizzes q ON q.id = qq.quiz_id
             JOIN courses c ON c.id = q.course_id
             {$joinHistory}
             WHERE {$sqlWhere}
             ORDER BY q.title ASC, qq.sort_order ASC, qq.id ASC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($listParams);
        $items = [];
        foreach ($stmt->fetchAll() as $row) {
            $items[] = [
                'id'          => (int) $row['id'],
                'question'    => (string) $row['question_text'],
                'topic'       => (string) $row['topic'],
                'subject'     => (string) $row['topic'],
                'chapter'     => (string) $row['topic'],
                'course_title'=> (string) $row['course_title'],
                'quiz_id'     => (int) $row['quiz_id'],
                'attempted'   => (int) $row['attempted'] === 1,
                'is_correct'  => $row['is_correct'] === null ? null : ((int) $row['is_correct'] === 1),
            ];
        }

        return [
            'items'    => $items,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Pick practice IDs for Question Bank.
     * @return list<int>
     */
    public function pickBankIds(
        int $studentId,
        array $courseIds,
        array $filters,
        int $limit = 20
    ): array {
        $courseIds = array_values(array_filter(array_map('intval', $courseIds)));
        if (!$courseIds || $limit <= 0) {
            return [];
        }
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $where = [
            "q.status = 'published'",
            "q.course_id IN ({$in})",
            "qq.question_type IN ('single_choice','true_false','multiple_choice')",
        ];
        $params = $courseIds;
        if (!empty($filters['quiz_id'])) {
            $where[] = 'q.id = ?';
            $params[] = (int) $filters['quiz_id'];
        }
        if (!empty($filters['topic'])) {
            $where[] = 'q.title = ?';
            $params[] = $filters['topic'];
        }
        $attemptFilter = (string) ($filters['attempt_filter'] ?? '');
        if ($attemptFilter === 'unattempted') {
            $where[] = 'h.id IS NULL';
        } elseif ($attemptFilter === 'attempted') {
            $where[] = 'h.id IS NOT NULL';
        }
        $sqlWhere = implode(' AND ', $where);
        $params = array_merge([$studentId], $params);
        $params[] = $limit;
        $stmt = $this->db->prepare(
            "SELECT qq.id FROM quiz_questions qq
             JOIN quizzes q ON q.id = qq.quiz_id
             LEFT JOIN student_question_history h ON h.question_id = qq.id AND h.student_id = ?
             WHERE {$sqlWhere}
             ORDER BY RAND()
             LIMIT ?"
        );
        $stmt->execute($params);
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }
}
