<?php
namespace App\Services;

use App\AI\AiProviderInterface;

/**
 * Turns extracted lecture text into FCPS Part-I study resources.
 *
 * Prompt engineering (deliverable #11) lives in one place — SYSTEM_PROMPT —
 * so every resource type inherits the same medical-accuracy guardrails.
 * Flashcards and MCQs are generated in batches so large lectures never exceed
 * the model context and generation stays resumable.
 */
class AiContentService
{
    /** Default cap when not passed from config (Groq free tier safe). */
    private const DEFAULT_MAX_INPUT_CHARS = 6000;

    private int $maxInputChars;

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are an expert medical educator creating study material for FCPS Part-I candidates in Pakistan (CPSP examination).

Follow these rules strictly:
- Target FCPS Part-I level: basic sciences integrated with clinical application (Anatomy, Physiology, Biochemistry, Pathology, Pharmacology, Microbiology, Community Medicine).
- Emphasise concepts, facts, and question patterns frequently tested in FCPS/CPSP examinations.
- Use evidence-based medicine and current international guidelines where relevant.
- Be clinically accurate. NEVER invent facts, references, drug doses, or values. If the lecture text does not support a fact, do not include it.
- Prefer understanding over rote memorisation: explanations should teach the concept.
- Keep language clear, concise, and exam-focused.
- Base all content ONLY on the supplied lecture text; do not drift to unrelated topics.
- Always respond with STRICT valid JSON matching the requested schema. No markdown, no commentary outside JSON.
PROMPT;

    public function __construct(private AiProviderInterface $ai, ?int $maxInputChars = null)
    {
        $this->maxInputChars = max(2000, $maxInputChars ?? self::DEFAULT_MAX_INPUT_CHARS);
    }

    public function isReady(): bool
    {
        return $this->ai->isConfigured();
    }

    public function providerName(): string
    {
        return $this->ai->name();
    }

    /** Concise but complete lecture summary (plain text / light markdown). */
    public function generateSummary(string $text): string
    {
        $user = "Write a concise but complete summary of the following lecture for FCPS Part-I revision. "
              . "Cover the main themes and the most important points. 200-400 words. "
              . "Respond as JSON: {\"summary\": \"...\"}.\n\nLECTURE:\n" . $this->trim($text);

        $data = $this->json($this->ai->complete(self::SYSTEM_PROMPT, $user, ['json' => true, 'max_tokens' => 1024]));
        return trim((string) ($data['summary'] ?? ''));
    }

    /**
     * High-yield revision notes + structured sections for the Revision Center.
     * Returns keys: revision_notes, high_yield_points, clinical_pearls,
     * common_mistakes, key_definitions, memory_tricks, key_takeaways.
     */
    public function generateNotes(string $text): array
    {
        $user = <<<TXT
From the lecture below, produce high-yield FCPS Part-I revision material.
Respond as STRICT JSON with this exact schema:
{
  "revision_notes": "markdown string of well-structured high-yield notes (headings, bullet points, tables described in text). Focus on exam concepts, frequently tested facts, definitions, and clinical relevance. Describe important diagrams in words.",
  "high_yield_points": ["short high-yield fact", "..."],
  "clinical_pearls": ["clinically relevant pearl", "..."],
  "common_mistakes": ["common student mistake / confusion point", "..."],
  "key_definitions": [{"term": "...", "definition": "..."}],
  "memory_tricks": ["mnemonic or memory aid", "..."],
  "key_takeaways": ["the single most important takeaway", "..."]
}
Include mnemonics where genuinely helpful. Keep every array between 4 and 12 items where the content supports it.

LECTURE:
TXT;
        $user .= "\n" . $this->trim($text);

        $data = $this->json($this->ai->complete(self::SYSTEM_PROMPT, $user, ['json' => true, 'max_tokens' => 2048]));

        return [
            'revision_notes'    => trim((string) ($data['revision_notes'] ?? '')),
            'high_yield_points' => $this->stringList($data['high_yield_points'] ?? []),
            'clinical_pearls'   => $this->stringList($data['clinical_pearls'] ?? []),
            'common_mistakes'   => $this->stringList($data['common_mistakes'] ?? []),
            'key_definitions'   => $this->defList($data['key_definitions'] ?? []),
            'memory_tricks'     => $this->stringList($data['memory_tricks'] ?? []),
            'key_takeaways'     => $this->stringList($data['key_takeaways'] ?? []),
        ];
    }

    /**
     * Generate a batch of flashcards. Pass already-generated fronts to avoid
     * duplicates across batches.
     *
     * @return array<int,array{front:string,back:string,topic:string,difficulty:string}>
     */
    public function generateFlashcards(string $text, int $count, array $existingFronts = []): array
    {
        $count = max(1, min(40, $count));
        $avoid = $existingFronts
            ? "Do NOT duplicate or paraphrase these existing questions:\n- " . implode("\n- ", array_slice($existingFronts, -20))
            : '';

        $user = <<<TXT
Create exactly {$count} FCPS Part-I flashcards from the lecture below.
Each flashcard: a focused question on the FRONT and a precise answer on the BACK (like: "What nerve supplies the diaphragm?" / "Phrenic nerve (C3-C5)").
Cover the whole lecture, not one section. Vary difficulty.
{$avoid}
Respond as STRICT JSON:
{"flashcards": [{"front": "...", "back": "...", "topic": "short topic", "difficulty": "easy|moderate|difficult"}]}

LECTURE:
TXT;
        $user .= "\n" . $this->trim($text);

        $data = $this->json($this->ai->complete(self::SYSTEM_PROMPT, $user, ['json' => true, 'max_tokens' => 2048]));
        $out = [];
        foreach (($data['flashcards'] ?? []) as $c) {
            $front = trim((string) ($c['front'] ?? ''));
            $back  = trim((string) ($c['back'] ?? ''));
            if ($front === '' || $back === '') {
                continue;
            }
            $out[] = [
                'front'      => $front,
                'back'       => $back,
                'topic'      => trim((string) ($c['topic'] ?? '')),
                'difficulty' => $this->difficulty($c['difficulty'] ?? 'moderate'),
            ];
        }
        return $out;
    }

    /**
     * Generate a batch of FCPS-style single-best-answer MCQs.
     *
     * @return array<int,array> Each: question, options A-E, correct_option,
     *                          explanation, option_explanations, topic, difficulty.
     */
    public function generateMcqs(string $text, int $count, array $existingQuestions = []): array
    {
        $count = max(1, min(15, $count));
        $avoid = $existingQuestions
            ? "Do NOT duplicate or paraphrase these existing questions:\n- " . implode("\n- ", array_slice($existingQuestions, -15))
            : '';

        $user = <<<TXT
Create exactly {$count} FCPS-style Single Best Answer (SBA) MCQs from the lecture below, in the style of the CPSP FCPS Part-I examination in Pakistan.
Requirements:
- Clinical scenario / vignette based whenever appropriate.
- Exactly 5 options (A-E), only ONE correct.
- Provide a detailed explanation for the correct answer AND a brief reason each other option is wrong.
- Mix difficulty across easy, moderate, difficult.
- Cover the ENTIRE lecture, avoid duplicates.
{$avoid}
Respond as STRICT JSON:
{"mcqs": [{
  "question": "...",
  "options": {"A": "...", "B": "...", "C": "...", "D": "...", "E": "..."},
  "correct_option": "A",
  "explanation": "why the correct answer is correct",
  "option_explanations": {"A": "...", "B": "...", "C": "...", "D": "...", "E": "..."},
  "topic": "short topic",
  "difficulty": "easy|moderate|difficult"
}]}

LECTURE:
TXT;
        $user .= "\n" . $this->trim($text);

        $data = $this->json($this->ai->complete(self::SYSTEM_PROMPT, $user, ['json' => true, 'max_tokens' => 2048]));
        $out = [];
        foreach (($data['mcqs'] ?? []) as $q) {
            $question = trim((string) ($q['question'] ?? ''));
            $options  = $q['options'] ?? [];
            $correct  = strtoupper(trim((string) ($q['correct_option'] ?? '')));
            if ($question === '' || !is_array($options) || !in_array($correct, ['A', 'B', 'C', 'D', 'E'], true)) {
                continue;
            }
            if (empty($options['A']) || empty($options['B'])) {
                continue;
            }
            $out[] = [
                'question'            => $question,
                'option_a'            => trim((string) ($options['A'] ?? '')),
                'option_b'            => trim((string) ($options['B'] ?? '')),
                'option_c'            => trim((string) ($options['C'] ?? '')),
                'option_d'            => trim((string) ($options['D'] ?? '')),
                'option_e'            => trim((string) ($options['E'] ?? '')),
                'correct_option'      => $correct,
                'explanation'         => trim((string) ($q['explanation'] ?? '')),
                'option_explanations' => is_array($q['option_explanations'] ?? null) ? $q['option_explanations'] : null,
                'topic'               => trim((string) ($q['topic'] ?? '')),
                'difficulty'          => $this->difficulty($q['difficulty'] ?? 'moderate'),
            ];
        }
        return $out;
    }

    // ── helpers ────────────────────────────────────────────────

    /** Keep input within model limits; sample start/middle/end for long text. */
    private function trim(string $text): string
    {
        if (mb_strlen($text) <= $this->maxInputChars) {
            return $text;
        }
        $slice = (int) floor($this->maxInputChars / 3);
        $len = mb_strlen($text);
        $start  = mb_substr($text, 0, $slice);
        $middle = mb_substr($text, (int) ($len / 2 - $slice / 2), $slice);
        $end    = mb_substr($text, $len - $slice, $slice);
        return $start . "\n...\n" . $middle . "\n...\n" . $end;
    }

    /** Parse JSON from a model response, tolerating code fences / stray text. */
    private function json(string $response): array
    {
        $s = trim($response);
        // Strip ```json ... ``` fences.
        $s = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $s);
        $decoded = json_decode($s, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        // Fallback: grab the outermost JSON object/array.
        if (preg_match('/[\{\[].*[\}\]]/s', $s, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        throw new \RuntimeException('AI returned malformed JSON.');
    }

    private function stringList(mixed $v): array
    {
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $item) {
            $s = is_array($item) ? trim((string) reset($item)) : trim((string) $item);
            if ($s !== '') {
                $out[] = $s;
            }
        }
        return $out;
    }

    private function defList(mixed $v): array
    {
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $item) {
            if (is_array($item) && !empty($item['term'])) {
                $out[] = [
                    'term'       => trim((string) $item['term']),
                    'definition' => trim((string) ($item['definition'] ?? '')),
                ];
            }
        }
        return $out;
    }

    private function difficulty(mixed $v): string
    {
        $v = strtolower(trim((string) $v));
        return in_array($v, ['easy', 'moderate', 'difficult'], true) ? $v : 'moderate';
    }
}
