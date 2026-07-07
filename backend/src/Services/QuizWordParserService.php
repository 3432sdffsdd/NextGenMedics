<?php
namespace App\Services;

/**
 * Parses MCQ blocks from plain text (Word export, .txt, pasted quiz).
 *
 * Accepts the FCPS-style format as-is (title, numbered questions, Hint, A–D, Answer, Rationale).
 * Tolerates Word quirks: soft line breaks, glued lines, extra spaces.
 */
class QuizWordParserService
{
    public function parseText(string $text): array
    {
        $text = $this->normalizeQuizText($text);
        if ($text === '') {
            return ['valid' => [], 'invalid' => [], 'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0]];
        }

        $blocks = $this->splitBlocks($text);
        $valid = [];
        $invalid = [];

        foreach ($blocks as $i => $block) {
            $num = $i + 1;
            if (preg_match('/^(\d+)\.\s+/m', $block, $m)) {
                $num = (int) $m[1];
            } elseif (preg_match('/Question\s+(\d+)/i', $block, $m)) {
                $num = (int) $m[1];
            }
            $parsed = $this->parseBlock(trim($block), $num);
            if ($parsed['errors']) {
                $invalid[] = $parsed;
            } else {
                $valid[] = $parsed;
            }
        }

        return [
            'valid'   => $valid,
            'invalid' => $invalid,
            'summary' => [
                'total'   => count($valid) + count($invalid),
                'valid'   => count($valid),
                'invalid' => count($invalid),
            ],
        ];
    }

    private function normalizeQuizText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        $text = preg_replace('/^\x{FEFF}/u', '', $text) ?? $text;
        $text = preg_replace('/\x{00A0}/u', ' ', $text) ?? $text;

        $text = $this->reflowQuizText($text);

        $text = preg_replace('/^\d+\s+questions\s*\n/im', '', $text) ?? $text;

        if (preg_match('/(\d+)\.\s+/m', $text, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            if ($pos > 0) {
                $text = substr($text, $pos);
            }
        }

        return trim($text);
    }

    /** Insert line breaks when Word merges lines onto one row. */
    private function reflowQuizText(string $text): string
    {
        // Next numbered question after rationale (e.g. "...limb.\n2. During" or "...limb. 2. During")
        $text = preg_replace('/(?<=[.!?)\]"\'\d])\s+(\d+\.\s+)/u', "\n$1", $text) ?? $text;

        // Hint / Answer / Rationale labels
        $text = preg_replace('/(?<=[.!?)\]"\'\d])\s+(💡\s*Hint:)/u', "\n$1", $text) ?? $text;
        $text = preg_replace('/(?<=[.!?)\]"\'\d])\s+(Hint:)/i', "\n$1", $text) ?? $text;
        $text = preg_replace('/(?<=[.!?)\]"\'\d])\s+(Answer:\s*[A-Ea-e])/i', "\n$1", $text) ?? $text;
        $text = preg_replace('/(?<=[.!?)\]"\'\d])\s+(Rationale:)/i', "\n$1", $text) ?? $text;

        // Options B–D glued to previous option text (never split "1. A patient")
        $text = preg_replace('/(?<=[a-z0-9)\]"\'\x{2019}\x{2018}])\s+([B-D])[.)]\s+/u', "\n$1. ", $text) ?? $text;

        // Option A after hint or question stem ending with punctuation
        $text = preg_replace('/(?<=[.!?)\]"\'\x{2019}\x{2018}])\s+(A)[.)]\s+/u', "\n$1. ", $text) ?? $text;

        return $text;
    }

    /** @return string[] */
    private function splitBlocks(string $text): array
    {
        if (preg_match('/\d+\.\s+/m', $text)) {
            $parts = preg_split('/(?=^\d+\.\s+)/m', $text, -1, PREG_SPLIT_NO_EMPTY);
            return array_values(array_filter(array_map('trim', $parts)));
        }

        if (preg_match('/Question\s+\d+\b/i', $text)) {
            $parts = preg_split('/(?=Question\s+\d+\b)/i', $text, -1, PREG_SPLIT_NO_EMPTY);
            return array_values(array_filter(array_map('trim', $parts)));
        }

        $parts = preg_split('/\n\s*\n+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) > 1) {
            return array_values(array_filter(array_map('trim', $parts)));
        }

        $parts = preg_split(
            '/(?<=(?:^|\n)(?:ANSWER|Answer|Correct\s+Answer)\s*:\s*[A-Ea-e])\s*\n+/im',
            $text,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        return array_values(array_filter(array_map('trim', $parts)));
    }

    private function parseBlock(string $block, int $number): array
    {
        $errors = [];
        $explanation = null;

        $block = preg_replace('/^\d+\.\s*/', '', $block);

        if (preg_match('/(?:Rationale|Explanation)\s*:\s*(.+?)(?=\n\s*\d+\.\s+|\z)/is', $block, $em)) {
            $explanation = trim($em[1]);
            $block = preg_replace('/(?:Rationale|Explanation)\s*:.+?(?=\n\s*\d+\.\s+|\z)/is', '', $block);
        }

        $correctLetter = null;
        if (preg_match('/(?:ANSWER|Answer|Correct\s+Answer)\s*:\s*([A-Ea-e])\b/i', $block, $cm)) {
            $correctLetter = strtoupper($cm[1]);
            $block = preg_replace('/(?:ANSWER|Answer|Correct\s+Answer)\s*:\s*[A-Ea-e]\b[^\n]*/i', '', $block);
        } else {
            $errors[] = 'Missing answer (use Answer: B or ANSWER: B)';
        }

        $options = $this->extractOptions($block);
        if (count($options) < 4) {
            $errors[] = 'Must have at least 4 options (A–D)';
        }

        $texts = array_values($options);
        if (count($texts) !== count(array_unique(array_map('strtolower', $texts)))) {
            $errors[] = 'Duplicate option text detected';
        }

        if ($correctLetter && !isset($options[$correctLetter])) {
            $errors[] = "Answer {$correctLetter} does not match any option";
        }

        $questionText = $this->extractQuestionText($block, array_keys($options));
        if ($questionText === '') {
            $errors[] = 'Missing question text';
        }

        $optionRows = [];
        foreach (['A', 'B', 'C', 'D', 'E'] as $letter) {
            if (!isset($options[$letter])) {
                continue;
            }
            $optionRows[] = [
                'option_text' => $options[$letter],
                'is_correct'  => $correctLetter === $letter ? 1 : 0,
            ];
        }

        return [
            'number'        => $number,
            'question_text' => $questionText,
            'explanation'   => $explanation,
            'options'       => $optionRows,
            'errors'        => $errors,
        ];
    }

    /** @return array<string,string> */
    private function extractOptions(string $block): array
    {
        $options = [];

        if (preg_match_all('/^([A-Ea-e])[.)]\s*(.+)$/m', $block, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $letter = strtoupper($match[1]);
                $options[$letter] = trim($match[2]);
            }
        }

        if (count($options) >= 4) {
            return $options;
        }

        // Fallback: options glued on one line (Word export without line breaks).
        if (preg_match_all(
            '/(?:^|\n|\s)([A-D])[.)]\s*(.*?)(?=(?:\s+[A-D][.)]\s)|(?:\s*(?:Answer|Rationale|ANSWER):)|$)/isu',
            $block,
            $inline,
            PREG_SET_ORDER
        )) {
            foreach ($inline as $match) {
                $letter = strtoupper($match[1]);
                $text = trim($match[2]);
                if ($text !== '') {
                    $options[$letter] = $text;
                }
            }
        }

        return $options;
    }

    /** @param string[] $optionLetters */
    private function extractQuestionText(string $block, array $optionLetters): string
    {
        $lines = array_filter(array_map('trim', explode("\n", $block)), fn ($l) => $l !== '');
        $questionLines = [];

        foreach ($lines as $line) {
            if (preg_match('/^Question\s+\d+/i', $line)) {
                continue;
            }
            if (preg_match('/^[A-Ea-e][.)]\s*/', $line)) {
                continue;
            }
            if (preg_match('/^(?:ANSWER|Answer|Correct\s+Answer)\s*:/i', $line)) {
                continue;
            }
            if (preg_match('/^(?:Rationale|Explanation)\s*:/i', $line)) {
                continue;
            }
            $questionLines[] = $line;
        }

        $text = trim(implode("\n", $questionLines));

        if ($text === '' && $optionLetters) {
            $first = $optionLetters[0];
            if (preg_match('/^(.*?)(?:\s+' . preg_quote($first, '/') . '[.)]\s)/is', $block, $m)) {
                $text = trim(preg_replace('/^\d+\.\s*/', '', $m[1]) ?? '');
            }
        }

        return $text;
    }

    /** Validate a question payload before DB import (manual edit or bulk import). */
    public function validateImportQuestion(array $q): array
    {
        $errors = [];
        $text = trim((string) ($q['question_text'] ?? ''));
        if ($text === '') {
            $errors[] = 'Missing question text';
        }

        $options = $q['options'] ?? [];
        if (count($options) < 4) {
            $errors[] = 'Must have at least 4 options (A–D)';
        }

        $correctCount = 0;
        $texts = [];
        foreach ($options as $i => $opt) {
            $optText = trim((string) ($opt['option_text'] ?? ''));
            if ($optText === '') {
                $errors[] = 'Option ' . ($i + 1) . ' is empty';
                continue;
            }
            $lower = strtolower($optText);
            if (in_array($lower, $texts, true)) {
                $errors[] = 'Duplicate option text detected';
            }
            $texts[] = $lower;
            if (!empty($opt['is_correct'])) {
                $correctCount++;
            }
        }

        if ($correctCount !== 1) {
            $errors[] = 'Exactly one correct option is required';
        }

        return $errors;
    }
}
