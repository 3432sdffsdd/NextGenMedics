<?php
namespace App\AI\Prompts;

/**
 * Stage-specific prompt templates for the Gemini generation engine.
 * One prompt per deliverable — never generate everything in one request.
 */
class PromptRepository
{
    public function system(): string
    {
        return <<<'PROMPT'
You are an expert medical educator creating study material for FCPS Part-I candidates in Pakistan (CPSP examination).

Rules:
- Target FCPS Part-I: basic sciences integrated with clinical application.
- Base content primarily on the supplied LECTURE TEXT.
- If you add brief supplementary medical knowledge needed for exam clarity, clearly label it as [Supplementary].
- Be clinically accurate. Do not invent drug doses, lab values, or guidelines.
- Prefer understanding over rote memorisation.
- Always respond with STRICT valid JSON matching the requested schema. No markdown fences, no commentary outside JSON.
- Do not include thinking notes, preamble, or trailing text — JSON only.
PROMPT;
    }

    public function forStage(string $stageKey): string
    {
        return match ($stageKey) {
            'detailed_notes'     => $this->detailedNotes(),
            'summary'            => $this->summary(),
            'high_yield'         => $this->highYield(),
            'drug_table'         => $this->drugTable(),
            'disease_comparison' => $this->diseaseComparison(),
            'mnemonics'          => $this->mnemonics(),
            'flashcards'         => $this->flashcards(),
            'viva'               => $this->viva(),
            'mcqs'               => $this->mcqs(),
            'clinical_cases'     => $this->clinicalCases(),
            'revision_sheet'     => $this->revisionSheet(),
            'video_simulation'   => $this->videoSimulation(),
            default              => throw new \InvalidArgumentException("Unknown stage prompt: {$stageKey}"),
        };
    }

    private function detailedNotes(): string
    {
        return <<<'TXT'
From the lecture text, produce SHORT, high-yield study NOTES only — not a long essay.
Keep it compact: bullet points, key facts, definitions. Aim for roughly half a page to one page max.
Respond as STRICT JSON:
{"detailed_notes": "short markdown notes with headings and bullets only. No long paragraphs."}
TXT;
    }

    private function summary(): string
    {
        return <<<'TXT'
Produce a SHORT summary of the lecture (120-200 words max).
Respond as STRICT JSON:
{"summary": "brief plain text or light markdown summary"}
TXT;
    }

    private function mnemonics(): string
    {
        return <<<'TXT'
Create useful mnemonics tied to lecture topics. Prefer genuine memory aids over forced ones.
Respond as STRICT JSON:
{"mnemonics": [{"topic":"...", "mnemonic":"...", "explanation":"..."}]}
Provide 3-8 items where content supports it.
TXT;
    }

    private function clinicalCases(): string
    {
        return <<<'TXT'
Create exactly 5 clinical case scenarios based on the lecture for bedside/exam practice.
Keep each scenario concise (not lengthy).
Respond as STRICT JSON:
{"cases": [{
  "title":"...",
  "scenario":"...",
  "questions":[{"question":"...","answer":"..."}],
  "diagnosis":"...",
  "discussion":"..."
}]}
TXT;
    }

    private function highYield(): string
    {
        return <<<'TXT'
Produce HIGH-YIELD notes focused on exam-tested facts from the lecture.
Respond as STRICT JSON:
{
  "high_yield_notes": "markdown high-yield notes",
  "high_yield_points": ["short high-yield fact", "..."]
}
Include 8-20 points where content supports it.
TXT;
    }

    private function drugTable(): string
    {
        return <<<'TXT'
Extract drugs/medications mentioned or clearly implied in the lecture into a structured table.
If the lecture has no drugs, return an empty array.
Respond as STRICT JSON:
{"drugs": [{"drug_name":"...", "drug_class":"...", "mechanism":"...", "indications":"...", "adverse_effects":"...", "notes":"..."}]}
TXT;
    }

    private function diseaseComparison(): string
    {
        return <<<'TXT'
If the lecture compares diseases/conditions, build a comparison matrix. If not applicable, return an empty array.
Respond as STRICT JSON:
{"comparisons": [{"title":"...", "diseases":["A","B"], "comparison_rows":[{"feature":"Onset","values":{"A":"...","B":"..."}}]}]}
TXT;
    }

    private function flashcards(): string
    {
        return <<<'TXT'
Create FCPS Part-I flashcards from the lecture (focused question FRONT / precise answer BACK).
Respond as STRICT JSON:
{"flashcards": [{"front":"...", "back":"...", "topic":"...", "difficulty":"easy|moderate|difficult"}]}
TXT;
    }

    private function viva(): string
    {
        return <<<'TXT'
Create viva-style oral questions with model answers for FCPS-oriented teaching.
Respond as STRICT JSON:
{"viva_questions": [{"question":"...", "answer":"...", "topic":"...", "difficulty":"easy|moderate|difficult"}]}
TXT;
    }

    private function mcqs(): string
    {
        return <<<'TXT'
Create FCPS-style Single Best Answer MCQs (5 options A-E, one correct) with explanations.
Respond as STRICT JSON:
{"mcqs": [{
  "question":"...",
  "options":{"A":"...","B":"...","C":"...","D":"...","E":"..."},
  "correct_option":"A",
  "explanation":"...",
  "option_explanations":{"A":"...","B":"...","C":"...","D":"...","E":"..."},
  "topic":"...",
  "difficulty":"easy|moderate|difficult"
}]}
TXT;
    }

    private function revisionSheet(): string
    {
        return <<<'TXT'
Create a 5-MINUTE revision sheet covering only the absolute essentials from the lecture.
Respond as STRICT JSON:
{"revision_sheet": "markdown content a student can revise in ~5 minutes"}
TXT;
    }

    private function videoSimulation(): string
    {
        return <<<'TXT'
Create a VIDEO TEACHING SIMULATION pack (script + production guidance) based on the lecture.
Respond as STRICT JSON:
{
  "title":"...",
  "teaching_script":"...",
  "voice_over":"...",
  "scenes":[{"scene":1,"title":"...","description":"...","duration_seconds":30}],
  "timeline":[{"at_seconds":0,"action":"..."}],
  "diagrams":[{"title":"...","description":"..."}],
  "subtitles":"full subtitle text",
  "camera_guidance":"...",
  "duration_seconds":300
}
TXT;
    }
}
