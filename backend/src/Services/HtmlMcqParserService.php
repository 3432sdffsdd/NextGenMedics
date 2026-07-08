<?php
namespace App\Services;

/**
 * Parses MCQ questions from HTML files or pages.
 * Falls back to plain-text Word-style parsing after stripping tags.
 */
class HtmlMcqParserService
{
    public function __construct(private QuizWordParserService $wordParser) {}

    public function parseHtml(string $html): array
    {
        $html = trim($html);
        if ($html === '') {
            return $this->emptyResult();
        }

        if ($json = $this->extractEmbeddedJson($html)) {
            $fromJson = $this->fromJsonPayload($json);
            if ($fromJson['summary']['valid'] > 0) {
                return $fromJson;
            }
        }

        $fromDom = $this->fromDom($html);
        if ($fromDom['summary']['valid'] > 0) {
            return $fromDom;
        }

        return $this->wordParser->parseText($this->htmlToText($html));
    }

    /** @return array{valid: array, invalid: array, summary: array} */
    private function emptyResult(): array
    {
        return ['valid' => [], 'invalid' => [], 'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0]];
    }

    public function fetchUrl(string $url): string
    {
        $url = $this->normalizeUrl($url);

        $body = $this->fetchWithCurl($url);
        if ($body !== null) {
            return $body;
        }

        $ctx = stream_context_create([
            'http' => [
                'timeout'         => 20,
                'follow_location' => 1,
                'max_redirects'   => 3,
                'user_agent'      => 'NextGenMedics-LMS/1.0',
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);

        $body = @file_get_contents($url, false, $ctx);
        if ($body === false || trim($body) === '') {
            throw new \RuntimeException('Could not fetch HTML from the provided link. Check the URL is public and reachable from the server.');
        }
        return $body;
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            throw new \InvalidArgumentException('URL is required');
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL');
        }
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            throw new \InvalidArgumentException('Only http/https links are allowed');
        }
        return $url;
    }

    private function fetchWithCurl(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_USERAGENT      => 'NextGenMedics-LMS/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || trim((string) $body) === '') {
            if ($error !== '') {
                throw new \RuntimeException('Could not fetch link: ' . $error);
            }
            return null;
        }
        if ($code >= 400) {
            throw new \RuntimeException("Could not fetch link (HTTP {$code})");
        }
        return (string) $body;
    }

    private function htmlToText(string $html): string
    {
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $html);
        $html = preg_replace('/<\/(?:p|div|h[1-6]|li|tr|br)\b[^>]*>/i', "\n", $html);
        $text = strip_tags($html);
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** @return array<string,mixed>|null */
    private function extractEmbeddedJson(string $html): ?array
    {
        if (preg_match('/<script[^>]+type=["\']application\/json["\'][^>]*>(.*?)<\/script>/is', $html, $m)) {
            $decoded = json_decode(trim($m[1]), true);
            return is_array($decoded) ? $decoded : null;
        }
        if (preg_match('/(?:var|const|let)\s+(?:quiz|questions|QUIZ|QUESTIONS)\s*=\s*(\[[\s\S]*?\]);/i', $html, $m)) {
            $decoded = json_decode($m[1], true);
            return is_array($decoded) ? ['questions' => $decoded] : null;
        }
        return null;
    }

    /** @param array<string,mixed> $payload */
    private function fromJsonPayload(array $payload): array
    {
        $rows = $payload['questions'] ?? $payload['items'] ?? $payload;
        if (!is_array($rows)) {
            return $this->emptyResult();
        }

        $valid = [];
        $invalid = [];
        foreach ($rows as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $num = (int) ($row['number'] ?? $i + 1);
            $parsed = $this->normalizeQuestionRow($row, $num);
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

    /** @param array<string,mixed> $row */
    private function normalizeQuestionRow(array $row, int $number): array
    {
        $errors = [];
        $questionText = trim((string) ($row['question_text'] ?? $row['question'] ?? $row['text'] ?? ''));
        if ($questionText === '') {
            $errors[] = 'Missing question text';
        }

        $rawOptions = $row['options'] ?? $row['choices'] ?? [];
        $correctLetter = strtoupper(trim((string) ($row['answer'] ?? $row['correct'] ?? $row['correct_answer'] ?? '')));
        $optionRows = [];

        if (is_array($rawOptions) && $rawOptions) {
            $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
            $idx = 0;
            foreach ($rawOptions as $key => $opt) {
                if (is_array($opt)) {
                    $text = trim((string) ($opt['option_text'] ?? $opt['text'] ?? ''));
                    $isCorrect = !empty($opt['is_correct']) || (!empty($correctLetter) && strtoupper((string) $key) === $correctLetter);
                } else {
                    $text = trim((string) $opt);
                    $letter = is_string($key) && preg_match('/^[A-D]$/i', (string) $key)
                        ? strtoupper((string) $key)
                        : ($letters[$idx] ?? chr(65 + $idx));
                    $isCorrect = $correctLetter !== '' && $letter === $correctLetter;
                }
                if ($text === '') {
                    continue;
                }
                $optionRows[] = ['option_text' => $text, 'is_correct' => $isCorrect ? 1 : 0];
                $idx++;
            }
        }

        $correctCount = count(array_filter($optionRows, fn($o) => !empty($o['is_correct'])));
        if (count($optionRows) < 4) {
            $errors[] = 'Must have at least 4 options (A–D)';
        }
        if ($correctCount !== 1) {
            $errors[] = 'Exactly one correct option is required';
        }

        return [
            'number'        => $number,
            'question_text' => $questionText,
            'explanation'   => trim((string) ($row['explanation'] ?? '')) ?: null,
            'options'       => $optionRows,
            'errors'        => $errors,
        ];
    }

    private function fromDom(string $html): array
    {
        if (!class_exists(\DOMDocument::class)) {
            return $this->emptyResult();
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        if (!$loaded) {
            return $this->emptyResult();
        }

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query(
            '//*[contains(concat(" ", normalize-space(@class), " "), " question ")
               or contains(concat(" ", normalize-space(@class), " "), " mcq ")
               or contains(concat(" ", normalize-space(@class), " "), " quiz-question ")
               or @data-question or @data-qid]'
        );

        if (!$nodes || $nodes->length === 0) {
            $nodes = $xpath->query('//fieldset');
        }

        if (!$nodes || $nodes->length === 0) {
            return $this->emptyResult();
        }

        $valid = [];
        $invalid = [];
        $num = 0;
        foreach ($nodes as $node) {
            $num++;
            $parsed = $this->parseDomBlock($node, $xpath, $num);
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

    private function parseDomBlock(\DOMNode $node, \DOMXPath $xpath, int $number): array
    {
        $errors = [];
        $blockText = trim(preg_replace('/\s+/', ' ', $node->textContent ?? ''));

        $questionText = '';
        foreach (['.//h1', './/h2', './/h3', './/h4', './/*[contains(@class,"question-text")]', './/*[contains(@class,"q-text")]'] as $qSel) {
            $qNode = $xpath->query($qSel, $node)?->item(0);
            if ($qNode && trim($qNode->textContent) !== '') {
                $questionText = trim($qNode->textContent);
                break;
            }
        }
        if ($questionText === '') {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\n+/', $this->htmlToText($node->ownerDocument->saveHTML($node))))));
            foreach ($lines as $line) {
                if (preg_match('/^Question\s+\d+/i', $line)) {
                    continue;
                }
                if (preg_match('/^[A-D][.)]\s*/', $line)) {
                    break;
                }
                if (preg_match('/^(?:ANSWER|Correct\s+Answer)\s*:/i', $line)) {
                    break;
                }
                $questionText = $questionText === '' ? $line : $questionText . "\n" . $line;
            }
        }

        $options = [];
        $labels = $xpath->query('.//label', $node);
        if ($labels && $labels->length >= 4) {
            foreach ($labels as $label) {
                $text = trim(preg_replace('/\s+/', ' ', $label->textContent ?? ''));
                if ($text !== '') {
                    $options[] = preg_replace('/^[A-D][.)]\s*/', '', $text);
                }
            }
        }

        if (count($options) < 4) {
            if (preg_match_all('/^([A-D])[.)]\s*(.+)$/m', $this->htmlToText($node->ownerDocument->saveHTML($node)), $m, PREG_SET_ORDER)) {
                $options = [];
                foreach ($m as $match) {
                    $options[strtoupper($match[1])] = trim($match[2]);
                }
            }
        }

        $correctLetter = null;
        $answerNode = $xpath->query('.//*[contains(@class,"answer") or contains(@class,"correct") or @data-correct or @data-answer]', $node)?->item(0);
        if ($answerNode) {
            $attr = $answerNode->getAttribute('data-correct') ?: $answerNode->getAttribute('data-answer');
            if ($attr !== '') {
                $correctLetter = strtoupper(trim($attr));
            } else {
                if (preg_match('/(?:ANSWER|Correct\s+Answer)\s*:\s*([A-D])/i', $answerNode->textContent ?? '', $cm)) {
                    $correctLetter = strtoupper($cm[1]);
                }
            }
        }
        if (!$correctLetter && preg_match('/(?:ANSWER|Correct\s+Answer)\s*:\s*([A-D])/i', $blockText, $cm)) {
            $correctLetter = strtoupper($cm[1]);
        }

        if ($questionText === '') {
            $errors[] = 'Missing question text';
        }
        if (count($options) < 4) {
            $errors[] = 'Must have at least 4 options (A–D)';
        }
        if (!$correctLetter) {
            $errors[] = 'Missing answer (use ANSWER: B or data-correct="B")';
        }

        $optionRows = [];
        if (isset($options['A']) || isset($options[0])) {
            $letters = array_is_list($options) ? ['A', 'B', 'C', 'D'] : array_keys($options);
            $values = array_is_list($options) ? $options : array_values($options);
            foreach ($values as $i => $text) {
                $letter = $letters[$i] ?? chr(65 + $i);
                if (!is_string($text) || trim($text) === '') {
                    continue;
                }
                $optionRows[] = [
                    'option_text' => trim($text),
                    'is_correct'  => ($correctLetter && strtoupper((string) $letter) === $correctLetter) ? 1 : 0,
                ];
            }
        }

        if (count($optionRows) >= 4 && $correctLetter) {
            $correctCount = count(array_filter($optionRows, fn($o) => !empty($o['is_correct'])));
            if ($correctCount !== 1) {
                $errors[] = "Answer {$correctLetter} does not match any option";
            }
        }

        return [
            'number'        => $number,
            'question_text' => $questionText,
            'explanation'   => null,
            'options'       => $optionRows,
            'errors'        => $errors,
        ];
    }
}
