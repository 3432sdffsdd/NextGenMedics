<?php
namespace App\Services;

use App\Repositories\QuizRepository;

class QuizEvaluationService
{
    public function __construct(private QuizRepository $quizzes) {}

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
