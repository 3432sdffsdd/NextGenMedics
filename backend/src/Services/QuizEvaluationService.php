<?php
namespace App\Services;

use App\Repositories\QuizRepository;
use App\Repositories\StudentQuestionHistoryRepository;

class QuizEvaluationService
{
    public function __construct(
        private QuizRepository $quizzes,
        private StudentQuestionHistoryRepository $questionHistory
    ) {}

    public function evaluateAttempt(int $attemptId, array $answers, ?int $timeTakenSeconds = null): array
    {
        $attempt = $this->quizzes->getAttempt($attemptId);
        if (!$attempt) {
            throw new \RuntimeException('Attempt not found');
        }

        $quiz = $this->quizzes->findById((int) $attempt['quiz_id']);
        $questions = $this->quizzes->getQuestions((int) $attempt['quiz_id'], false, true);
        $totalScore = 0;
        $totalMarks = 0;
        $review = [];
        $attempted = 0;
        $correct = 0;
        $wrong = 0;
        $unanswered = 0;
        $showReview = (bool) ($quiz['show_review'] ?? 1);
        $studentId = (int) $attempt['student_id'];
        $attemptDate = date('Y-m-d');

        foreach ($questions as $question) {
            $totalMarks += (float) $question['marks'];
            $answer = $answers[$question['id']] ?? null;
            $hasAnswer = $this->hasAnswer($question['question_type'], $answer);

            if (!$hasAnswer) {
                $unanswered++;
            } else {
                $attempted++;
            }

            $result = $this->evaluateQuestion($question, $answer, $quiz, $hasAnswer);

            $this->quizzes->saveAnswer([
                'attempt_id'           => $attemptId,
                'question_id'          => $question['id'],
                'selected_option_ids'  => $result['selected'] ?? null,
                'text_answer'          => $result['text'] ?? null,
                'is_correct'           => $result['is_correct'],
                'marks_awarded'        => $result['marks'],
            ]);

            // Feed Weak Areas / Mistakes / Daily Challenge history from Quizzes tab
            if ($hasAnswer && $result['is_correct'] !== null) {
                $letter = $this->selectedToLetter($question, $result['selected'] ?? null);
                $this->questionHistory->record(
                    $studentId,
                    (int) $question['id'],
                    (bool) $result['is_correct'],
                    $letter,
                    $attemptDate,
                    null,
                    null,
                    'quiz'
                );
            }

            $totalScore += $result['marks'];

            if ($result['is_correct'] === true) {
                $correct++;
            } elseif ($result['is_correct'] === false && $hasAnswer) {
                $wrong++;
            }

            if ($quiz['auto_evaluate'] && $showReview) {
                $correctIds = array_map('intval', array_column(
                    array_filter($question['options'], fn($o) => $o['is_correct']),
                    'id'
                ));
                $review[] = [
                    'question_id'    => (int) $question['id'],
                    'question_text'  => $question['question_text'],
                    'explanation'    => $question['explanation'] ?? null,
                    'selected'       => $result['selected'],
                    'text_answer'    => $result['text'],
                    'is_correct'     => $result['is_correct'],
                    'marks_awarded'  => $result['marks'],
                    'correct_ids'    => $correctIds,
                    'options'        => array_map(fn($o) => [
                        'id'          => (int) $o['id'],
                        'option_text' => $o['option_text'],
                        'is_correct'  => (bool) $o['is_correct'],
                    ], $question['options']),
                ];
            }
        }

        $percentage = $totalMarks > 0 ? round(($totalScore / $totalMarks) * 100, 2) : 0;
        $passed = $percentage >= (float) $quiz['passing_marks'];

        if ($timeTakenSeconds === null && !empty($attempt['started_at'])) {
            $timeTakenSeconds = max(0, time() - strtotime($attempt['started_at']));
        }

        $this->quizzes->submitAttempt($attemptId, $totalScore, $percentage, $passed, $timeTakenSeconds);

        $updated = $this->quizzes->getAttempt($attemptId);

        return [
            'attempt_id'          => $attemptId,
            'score'               => $totalScore,
            'total'               => $totalMarks,
            'percentage'          => $percentage,
            'passed'              => $passed,
            'total_questions'     => count($questions),
            'attempted'           => $attempted,
            'correct'             => $correct,
            'wrong'               => $wrong,
            'unanswered'          => $unanswered,
            'time_taken_seconds'  => $timeTakenSeconds,
            'submitted_at'        => $updated['submitted_at'] ?? null,
            'review'              => ($quiz['auto_evaluate'] && $showReview) ? $review : null,
        ];
    }

    private function selectedToLetter(array $question, mixed $selected): ?string
    {
        if (!is_array($selected) || !$selected) {
            return null;
        }
        $selectedId = (int) $selected[0];
        $letters = ['A', 'B', 'C', 'D', 'E'];
        $i = 0;
        foreach ($question['options'] ?? [] as $opt) {
            if ($i >= 5) {
                break;
            }
            if ((int) $opt['id'] === $selectedId) {
                return $letters[$i];
            }
            $i++;
        }
        return null;
    }

    private function hasAnswer(string $type, mixed $answer): bool
    {
        if ($answer === null || $answer === '') {
            return false;
        }
        if (is_array($answer)) {
            return count(array_filter($answer, fn($v) => $v !== null && $v !== '')) > 0;
        }
        return true;
    }

    private function evaluateQuestion(array $question, mixed $answer, array $quiz, bool $hasAnswer): array
    {
        $marks = 0;
        $isCorrect = null;
        $selected = null;
        $text = null;

        if (!$hasAnswer) {
            return ['marks' => 0, 'is_correct' => false, 'selected' => null, 'text' => null];
        }

        switch ($question['question_type']) {
            case 'mcq':
            case 'single_choice':
            case 'true_false':
                $selected = [(int) $answer];
                $correctIds = array_column(array_filter($question['options'], fn($o) => $o['is_correct']), 'id');
                $isCorrect = in_array((int) $answer, array_map('intval', $correctIds), true);
                $marks = $isCorrect ? (float) $question['marks'] : ($quiz['negative_marking'] ? -(float) $quiz['negative_mark_value'] : 0);
                break;

            case 'multiple_choice':
                $selected = array_map('intval', (array) $answer);
                sort($selected);
                $correctIds = array_map('intval', array_column(array_filter($question['options'], fn($o) => $o['is_correct']), 'id'));
                sort($correctIds);
                $isCorrect = $selected === $correctIds;
                $marks = $isCorrect ? (float) $question['marks'] : ($quiz['negative_marking'] ? -(float) $quiz['negative_mark_value'] : 0);
                break;

            case 'fill_blank':
                $text = trim((string) $answer);
                $correct = array_map('strtolower', array_column(array_filter($question['options'], fn($o) => $o['is_correct']), 'option_text'));
                $isCorrect = in_array(strtolower($text), $correct, true);
                $marks = $isCorrect ? (float) $question['marks'] : 0;
                break;

            case 'essay':
                $text = (string) $answer;
                $isCorrect = null;
                break;

            default:
                $text = is_string($answer) ? $answer : json_encode($answer);
        }

        return [
            'marks'      => max(0, $marks),
            'is_correct' => $isCorrect,
            'selected'   => $selected,
            'text'       => $text,
        ];
    }
}
