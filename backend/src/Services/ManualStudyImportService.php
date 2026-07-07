<?php
namespace App\Services;

use App\Repositories\AiContentRepository;
use App\Repositories\FlashcardRepository;
use App\Repositories\McqRepository;

class ManualStudyImportService
{
    public function __construct(
        private WordStudyPackParserService $wordParser,
        private QuizWordParserService $quizParser,
        private HtmlMcqParserService $htmlParser,
        private ExcelFlashcardParserService $excelParser,
        private TextExtractionService $extractor,
        private AiContentRepository $content,
        private FlashcardRepository $flashcards,
        private McqRepository $mcqs
    ) {}

    /**
     * Import summary, notes, and MCQs from a Word file — replaces existing content of those types.
     *
     * @return array<string,mixed>
     */
    public function importWordPack(int $lectureId, int $courseId, int $userId, string $tmpPath, string $filename): array
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['docx', 'doc', 'txt'], true)) {
            throw new \InvalidArgumentException('Upload a Word (.doc or .docx) file');
        }

        if ($ext === 'txt') {
            $text = (string) file_get_contents($tmpPath);
        } else {
            $text = $this->extractor->extractAbsolute($tmpPath, $ext);
        }

        $parsed = $this->wordParser->parse($text);

        if (trim($parsed['summary'] . $parsed['notes']) === '') {
            throw new \InvalidArgumentException(
                'Could not read any text from this Word file. Save as .docx (Word format) and try again.'
            );
        }

        $this->content->ensure($lectureId, $courseId, $userId);
        $this->content->updateByLecture($lectureId, [
            'summary'         => $parsed['summary'],
            'revision_notes'  => $parsed['notes'],
            'high_yield_points' => [],
            'clinical_pearls'   => [],
            'common_mistakes'   => [],
            'key_definitions'   => [],
            'memory_tricks'     => [],
            'key_takeaways'     => [],
        ]);
        $this->mcqs->deleteAllByLecture($lectureId);
        $mcqCount = $this->mcqs->insertMany($lectureId, $courseId, $userId, $parsed['mcqs'], 'manual');

        $this->publishForStudents($lectureId);

        return [
            'summary_length' => strlen($parsed['summary']),
            'notes_length'   => strlen($parsed['notes']),
            'mcqs_imported'  => $mcqCount,
            'mcqs_invalid'   => count($parsed['invalid_mcqs']),
            'published'      => true,
        ];
    }

    /**
     * Import flashcards from Excel — replaces all flashcards for the lecture.
     * Topic is taken from the uploaded file name.
     *
     * @return array<string,mixed>
     */
    public function importFlashcards(int $lectureId, int $courseId, int $userId, string $tmpPath, string $filename): array
    {
        $parsed = $this->excelParser->parseFile($tmpPath, $filename);
        if (count($parsed['valid']) === 0) {
            $invalid = count($parsed['invalid']);
            $hint = $invalid > 0
                ? "{$invalid} row(s) were missing Front or Back text."
                : 'Use column A = Front and column B = Back (row 1 can be headers). Save as .xlsx or .xls.';
            throw new \InvalidArgumentException('No valid flashcard rows found. ' . $hint);
        }

        $topic = $parsed['topic'];
        $cards = array_map(fn($c) => [
            'front'      => $c['front'],
            'back'       => $c['back'],
            'topic'      => $topic,
            'difficulty' => 'moderate',
        ], $parsed['valid']);

        $this->content->ensure($lectureId, $courseId, $userId);

        $this->flashcards->deleteAllByLecture($lectureId);
        $count = $this->flashcards->insertMany($lectureId, $courseId, $userId, $cards, 'manual');

        $this->publishForStudents($lectureId);

        return [
            'topic'               => $topic,
            'flashcards_imported' => $count,
            'invalid_rows'        => count($parsed['invalid']),
            'published'           => true,
        ];
    }

    /**
     * Import MCQs only from Word / text / HTML — replaces existing MCQs for the lecture.
     *
     * @return array<string,mixed>
     */
    public function importMcqs(int $lectureId, int $courseId, int $userId, string $tmpPath, string $filename): array
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['doc', 'docx', 'txt', 'html', 'htm'], true)) {
            throw new \InvalidArgumentException('Upload a .docx, .doc, .txt, or .html quiz file');
        }

        if (in_array($ext, ['html', 'htm'], true)) {
            $html = (string) file_get_contents($tmpPath);
            $parsed = $this->htmlParser->parseHtml($html);
        } else {
            if ($ext === 'txt') {
                $text = (string) file_get_contents($tmpPath);
            } else {
                $text = $this->extractor->extractAbsolute($tmpPath, $ext);
            }
            $parsed = $this->quizParser->parseText($text);
        }

        if (count($parsed['valid']) === 0) {
            $invalid = count($parsed['invalid']);
            $hint = $invalid > 0
                ? "{$invalid} question block(s) had format errors."
                : 'Use numbered questions (1. …), options A–D, Answer: X, and optional Rationale:.';
            throw new \InvalidArgumentException('No valid MCQs found. ' . $hint);
        }

        $rows = $this->mcqRowsFromParsed($parsed['valid']);

        $this->content->ensure($lectureId, $courseId, $userId);
        $this->mcqs->deleteAllByLecture($lectureId);
        $mcqCount = $this->mcqs->insertMany($lectureId, $courseId, $userId, $rows, 'manual');

        $this->publishForStudents($lectureId);

        return [
            'mcqs_imported' => $mcqCount,
            'mcqs_invalid'  => count($parsed['invalid']),
            'published'     => true,
        ];
    }

    /** Save teacher-pasted summary and/or notes (does not touch MCQs or flashcards). */
    public function saveManualText(int $lectureId, int $courseId, int $userId, ?string $summary, ?string $notes, bool $publish = false): array
    {
        $this->content->ensure($lectureId, $courseId, $userId);
        $fields = [];
        if ($summary !== null) {
            $fields['summary'] = $summary;
        }
        if ($notes !== null) {
            $fields['revision_notes'] = $notes;
        }
        if ($fields) {
            $this->content->updateByLecture($lectureId, $fields);
        }
        if ($publish) {
            $this->publishForStudents($lectureId);
        }

        return [
            'summary_length' => $summary !== null ? strlen($summary) : null,
            'notes_length'   => $notes !== null ? strlen($notes) : null,
            'published'      => $publish,
        ];
    }

    /** @param array<int, array<string,mixed>> $valid */
    private function mcqRowsFromParsed(array $valid): array
    {
        $rows = [];
        foreach ($valid as $q) {
            $row = [
                'question'    => trim((string) ($q['question_text'] ?? '')),
                'explanation' => $q['explanation'] ?? null,
            ];
            $correct = 'A';
            foreach ($q['options'] ?? [] as $i => $opt) {
                $letter = chr(ord('A') + $i);
                if ($i <= 4) {
                    $row['option_' . strtolower($letter)] = $opt['option_text'] ?? '';
                }
                if (!empty($opt['is_correct'])) {
                    $correct = $letter;
                }
            }
            $row['correct_option'] = $correct;
            $rows[] = $row;
        }
        return $rows;
    }

    /** Make lecture study content visible to students immediately. */
    public function publishForStudents(int $lectureId): void
    {
        $this->mcqs->approveAllByLecture($lectureId);
        $this->content->setStatus($lectureId, 'published');
        $this->mcqs->publishAllByLecture($lectureId);
        $this->flashcards->approveAllByLecture($lectureId);
    }

    /** Preview Word pack without saving. */
    public function previewWord(string $tmpPath, string $filename): array
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === 'txt') {
            $text = (string) file_get_contents($tmpPath);
        } else {
            $text = $this->extractor->extractAbsolute($tmpPath, $ext);
        }
        $parsed = $this->wordParser->parse($text);
        return [
            'summary_preview' => mb_substr($parsed['summary'], 0, 400) . (strlen($parsed['summary']) > 400 ? '…' : ''),
            'notes_preview'     => mb_substr($parsed['notes'], 0, 400) . (strlen($parsed['notes']) > 400 ? '…' : ''),
            'mcqs_found'        => count($parsed['mcqs']),
            'mcqs_invalid'      => count($parsed['invalid_mcqs']),
        ];
    }

    /** Preview Excel flashcards without saving. */
    public function previewFlashcards(string $tmpPath, string $filename): array
    {
        $parsed = $this->excelParser->parseFile($tmpPath, $filename);
        return [
            'topic'   => $parsed['topic'],
            'summary' => $parsed['summary'],
            'sample'  => array_slice($parsed['valid'], 0, 3),
        ];
    }
}
