<?php
namespace App\Services;

/**
 * Parses a Word document into summary, revision notes, and MCQs.
 * Works with any .docx — tries section headers first, then falls back to
 * full-text notes + MCQ block detection.
 */
class WordStudyPackParserService
{
    public function __construct(private QuizWordParserService $wordParser) {}

    /** @return array{summary: string, notes: string, mcqs: array, invalid_mcqs: array, summary_meta: array} */
    public function parse(string $text): array
    {
        $text = $this->normalizeText($text);
        if ($text === '') {
            throw new \InvalidArgumentException('Word file has no readable text');
        }

        $sections = $this->splitSections($text);
        $mcqSource = $sections['mcqs'] ?? $text;
        $parsed = $this->looksLikeMcqContent($mcqSource)
            ? $this->wordParser->parseText($mcqSource)
            : ['valid' => [], 'invalid' => [], 'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0]];

        $summary = trim($sections['summary'] ?? '');
        $notes = trim($sections['notes'] ?? '');

        if ($summary === '' && $notes === '') {
            $notes = $text;
            $summary = $this->buildSummary($notes);
        } elseif ($summary === '' && $notes !== '') {
            $summary = $this->buildSummary($notes);
        } elseif ($notes === '' && $summary !== '') {
            $notes = $text;
        }

        $mcqs = [];
        foreach ($parsed['valid'] as $q) {
            $row = $this->toMcqRow($q);
            if ($row) {
                $mcqs[] = $row;
            }
        }

        return [
            'summary'       => $summary,
            'notes'         => $notes,
            'mcqs'          => $mcqs,
            'invalid_mcqs'  => $parsed['invalid'],
            'summary_meta'  => $parsed['summary'],
        ];
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        // Decorative separators common in Word exports (e.g. ________)
        $text = preg_replace('/^[\s_•\-=~]+$/m', '', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        return trim($text);
    }

    private function looksLikeMcqContent(string $text): bool
    {
        return (bool) preg_match('/(?:^|\n)\s*[A-D][\).\:\]]\s+/m', $text)
            || (bool) preg_match('/(?:ANSWER|Correct\s+Answer)\s*:/i', $text)
            || (bool) preg_match('/Question\s+\d+\b/i', $text);
    }

    /** @return array{summary?: string, notes?: string, mcqs?: string} */
    private function splitSections(string $text): array
    {
        $pattern = '/(?=^(?:SUMMARY|Summary|LECTURE SUMMARY|NOTES|Notes|REVISION NOTES|Revision Notes|MCQ|MCQs|QUIZ|QUESTIONS|Questions)\s*(?:[:\-]|$))/m';
        if (!preg_match($pattern, $text)) {
            return [];
        }

        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_NO_EMPTY);
        $headers = [];
        preg_match_all($pattern, $text, $headerMatches);
        foreach ($headerMatches[0] as $i => $h) {
            $headers[$i] = strtolower(trim(preg_replace('/[:\-]+\s*$/', '', trim($h))));
        }

        $out = [];
        foreach ($parts as $i => $body) {
            $header = $headers[$i] ?? '';
            $body = trim($body);
            if ($body === '') {
                continue;
            }
            if (str_contains($header, 'summary')) {
                $out['summary'] = ($out['summary'] ?? '') === '' ? $body : $out['summary'] . "\n\n" . $body;
            } elseif (str_contains($header, 'note')) {
                $out['notes'] = ($out['notes'] ?? '') === '' ? $body : $out['notes'] . "\n\n" . $body;
            } elseif (preg_match('/mcq|quiz|question/', $header)) {
                $out['mcqs'] = ($out['mcqs'] ?? '') === '' ? $body : $out['mcqs'] . "\n\n" . $body;
            }
        }
        return $out;
    }

    private function buildSummary(string $notes): string
    {
        $notes = trim($notes);
        if ($notes === '') {
            return '';
        }
        $paragraphs = preg_split('/\n\s*\n/', $notes);
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs)));
        if (count($paragraphs) >= 2) {
            return implode("\n\n", array_slice($paragraphs, 0, 2));
        }
        if (strlen($notes) > 1200) {
            return rtrim(substr($notes, 0, 1200)) . '…';
        }
        return $notes;
    }

    /** @param array<string,mixed> $q */
    private function toMcqRow(array $q): ?array
    {
        $options = $q['options'] ?? [];
        if (count($options) < 2) {
            return null;
        }
        $letters = ['A', 'B', 'C', 'D', 'E'];
        $correct = 'A';
        $row = ['question' => trim($q['question_text'])];
        foreach ($options as $i => $opt) {
            $letter = $letters[$i] ?? null;
            if (!$letter) {
                break;
            }
            $row['option_' . strtolower($letter)] = $opt['option_text'];
            if (!empty($opt['is_correct'])) {
                $correct = $letter;
            }
        }
        $row['correct_option'] = $correct;
        $row['explanation'] = $q['explanation'] ?? null;
        return $row;
    }
}
