<?php
namespace App\Services;

use App\Repositories\AssignmentRepository;

class AssignmentTestService
{
    public function __construct(
        private AssignmentRepository $assignments,
        private QuizWordParserService $wordParser
    ) {}

    /** @param array<int,array<string,mixed>> $questions Valid parsed questions */
    public function importQuestions(int $assignmentId, array $questions): int
    {
        $this->assignments->deleteQuestions($assignmentId);
        $imported = 0;
        foreach ($questions as $i => $q) {
            if ($this->wordParser->validateImportQuestion($q)) {
                continue;
            }
            $this->assignments->addQuestion($assignmentId, $q, $i + 1);
            $imported++;
        }
        return $imported;
    }

    /**
     * Grade student answers.
     *
     * @param array<string|int,int|string> $answers question_id => option_id
     * @return array<string,mixed>
     */
    public function grade(int $assignmentId, array $answers, float $maxMarks): array
    {
        $questions = $this->assignments->getQuestions($assignmentId, true);
        $total = count($questions);
        $correct = 0;
        $review = [];

        foreach ($questions as $q) {
            $qid = (int) $q['id'];
            $selected = isset($answers[$qid]) ? (int) $answers[$qid] : (isset($answers[(string) $qid]) ? (int) $answers[(string) $qid] : null);
            $correctIds = array_map('intval', array_column(
                array_filter($q['options'], fn($o) => !empty($o['is_correct'])),
                'id'
            ));
            $isCorrect = $selected !== null && in_array($selected, $correctIds, true);
            if ($isCorrect) {
                $correct++;
            }

            $review[] = [
                'question_id'   => $qid,
                'question_text' => $q['question_text'],
                'explanation'   => $q['explanation'] ?? null,
                'selected'      => $selected,
                'is_correct'    => $isCorrect,
                'correct_ids'   => $correctIds,
                'options'       => array_map(fn($o) => [
                    'id'          => (int) $o['id'],
                    'option_text' => $o['option_text'],
                    'is_correct'  => (bool) $o['is_correct'],
                ], $q['options']),
            ];
        }

        $percentage = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
        $marks = round(($percentage / 100) * $maxMarks, 2);

        return [
            'score'       => $marks,
            'max_marks'   => $maxMarks,
            'percentage'  => $percentage,
            'passed'      => $percentage >= 50,
            'correct'     => $correct,
            'total'       => $total,
            'review'      => $review,
        ];
    }
}
