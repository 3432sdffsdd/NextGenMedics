<?php
namespace App\Services;

use App\Helpers\SimpleXlsReader;

/**
 * Reads two-column Excel flashcard sheets (Front / Back).
 * Supports .xlsx, .xlsm, .xltx, .xltm (Office Open XML), legacy .xls / .xlt, and .csv.
 */
class ExcelFlashcardParserService
{
    private const XLSX_EXTENSIONS = ['xlsx', 'xlsm', 'xltx', 'xltm'];
    private const XLS_EXTENSIONS = ['xls', 'xlt'];
    private const ALL_EXTENSIONS = ['xlsx', 'xlsm', 'xltx', 'xltm', 'xls', 'xlt', 'csv'];

    /** @return array{valid: array<int,array{front:string,back:string}>, invalid: array, summary: array, topic: string} */
    public function parseFile(string $absolutePath, string $originalFilename): array
    {
        $format = $this->detectFormat($absolutePath, $originalFilename);
        $rows = match ($format) {
            'csv'  => $this->readCsv($absolutePath),
            'xlsx' => $this->readXlsx($absolutePath),
            'xls'  => $this->readXls($absolutePath),
            default => throw new \InvalidArgumentException(
                'Upload an Excel file (.xlsx or .xls). Column A = Front, column B = Back.'
            ),
        };

        $valid = [];
        $invalid = [];
        $num = 0;

        foreach ($rows as $row) {
            $front = trim((string) ($row[0] ?? ''));
            $back = trim((string) ($row[1] ?? ''));
            if ($front === '' && $back === '') {
                continue;
            }
            if ($num === 0 && $this->isHeaderRow($front, $back)) {
                continue;
            }
            $num++;
            if ($front === '' || $back === '') {
                $invalid[] = ['number' => $num, 'errors' => ['Both Front and Back are required']];
                continue;
            }
            $valid[] = ['front' => $front, 'back' => $back];
        }

        return [
            'valid'   => $valid,
            'invalid' => $invalid,
            'topic'   => $this->topicFromFilename($originalFilename),
            'summary' => [
                'total'   => count($valid) + count($invalid),
                'valid'   => count($valid),
                'invalid' => count($invalid),
            ],
        ];
    }

    /** @return string[] */
    public static function allowedExtensions(): array
    {
        return self::ALL_EXTENSIONS;
    }

    public function topicFromFilename(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $base = str_replace(['_', '-'], ' ', $base);
        return trim(preg_replace('/\s+/', ' ', $base)) ?: 'Flashcards';
    }

    private function isHeaderRow(string $front, string $back): bool
    {
        $f = strtolower($front);
        $b = strtolower($back);
        return in_array($f, ['front', 'question', 'prompt', 'term', 'card', 'word'], true)
            && in_array($b, ['back', 'answer', 'response', 'definition', 'meaning'], true);
    }

    private function detectFormat(string $path, string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $head = @file_get_contents($path, false, null, 0, 8);
        $head = is_string($head) ? $head : '';

        if (str_starts_with($head, "PK\x03\x04")) {
            return 'xlsx';
        }
        if (str_starts_with($head, pack('CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1))) {
            return 'xls';
        }
        if ($ext === 'csv' || $ext === 'txt') {
            return 'csv';
        }
        if (in_array($ext, self::XLSX_EXTENSIONS, true)) {
            return 'xlsx';
        }
        if (in_array($ext, self::XLS_EXTENSIONS, true)) {
            return 'xls';
        }
        if ($ext === 'csv') {
            return 'csv';
        }

        throw new \InvalidArgumentException(
            'Unsupported file type. Use .xlsx (Excel 2007+) or .xls (Excel 97–2003).'
        );
    }

    /** @return array<int, array{0: string, 1: string}> */
    private function readCsv(string $path): array
    {
        $rows = [];
        $raw = (string) file_get_contents($path);
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        $fh = fopen('php://memory', 'rb+');
        if ($fh === false) {
            throw new \RuntimeException('Could not read CSV file');
        }
        fwrite($fh, $raw);
        rewind($fh);
        while (($data = fgetcsv($fh)) !== false) {
            $rows[] = [$data[0] ?? '', $data[1] ?? ''];
        }
        fclose($fh);
        return $rows;
    }

    /** @return array<int, array{0: string, 1: string}> */
    private function readXlsx(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('PHP zip extension is required to read Excel files. Enable ext-zip on the server.');
        }
        $zip = new \ZipArchive();
        $opened = $zip->open($path);
        if ($opened !== true) {
            if ($this->looksLikeOle($path)) {
                return $this->readXls($path);
            }
            throw new \RuntimeException('Could not open Excel file. If this is an old .xls file, save it as .xls or re-save as .xlsx in Excel.');
        }

        $shared = $this->loadSharedStrings($zip);
        $sheetXml = $this->firstWorksheetXml($zip);
        $zip->close();

        if (!$sheetXml) {
            throw new \RuntimeException('No worksheet found in Excel file');
        }

        return $this->gridToRows($this->parseSheetGrid($sheetXml, $shared));
    }

    /** @return array<int, array{0: string, 1: string}> */
    private function readXls(string $path): array
    {
        $xls = SimpleXlsReader::parseFile($path);
        if (!$xls || !$xls->success()) {
            $err = SimpleXlsReader::parseError();
            throw new \RuntimeException($err ?: 'Could not read .xls Excel file. Try saving as .xlsx in Excel.');
        }

        $rows = [];
        foreach ($xls->rows(0) as $row) {
            $rows[] = [
                trim((string) ($row[0] ?? '')),
                trim((string) ($row[1] ?? '')),
            ];
        }

        if (!$rows && !empty($xls->sheets[0]['cells'])) {
            $cells = $xls->sheets[0]['cells'];
            ksort($cells);
            foreach ($cells as $cols) {
                if (!is_array($cols)) {
                    continue;
                }
                ksort($cols);
                $vals = array_values($cols);
                $rows[] = [
                    trim((string) ($vals[0] ?? '')),
                    trim((string) ($vals[1] ?? '')),
                ];
            }
        }

        return $rows;
    }

    private function looksLikeOle(string $path): bool
    {
        $head = @file_get_contents($path, false, null, 0, 8);
        return is_string($head) && str_starts_with($head, pack('CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1));
    }

    private function firstWorksheetXml(\ZipArchive $zip): ?string
    {
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml !== false) {
            return $sheetXml;
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    return $content;
                }
            }
        }
        return null;
    }

    /** @return string[] */
    private function loadSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }
        $strings = [];
        if (preg_match_all('/<si\b[^>]*>(.*?)<\/si>/s', $xml, $items)) {
            foreach ($items[1] as $chunk) {
                if (preg_match_all('/<t(?:\s[^>]*)?>(.*?)<\/t>/s', $chunk, $parts)) {
                    $text = implode('', array_map(
                        static fn($p) => html_entity_decode($p, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        $parts[1]
                    ));
                    $strings[] = $text;
                } else {
                    $strings[] = '';
                }
            }
        }
        return $strings;
    }

    /**
     * @param string[] $shared
     * @return array<int, array<string, string>>
     */
    private function parseSheetGrid(string $sheetXml, array $shared): array
    {
        $grid = [];
        $autoRow = 0;

        if (!preg_match('/<sheetData\b[^>]*>(.*)<\/sheetData>/s', $sheetXml, $bodyMatch)) {
            $bodyMatch = [null, null, $sheetXml];
        }
        $sheetBody = $bodyMatch[2] ?? $sheetXml;

        if (!preg_match_all('/<row\b([^>]*)>(.*?)<\/row>/s', $sheetBody, $rowMatches, PREG_SET_ORDER)) {
            return [];
        }

        foreach ($rowMatches as $rowMatch) {
            $rowAttrs = $rowMatch[1];
            $rowContent = $rowMatch[2];
            if (preg_match('/\br="(\d+)"/', $rowAttrs, $rm)) {
                $rowNum = (int) $rm[1];
            } else {
                $autoRow++;
                $rowNum = $autoRow;
            }

            if (!preg_match_all('/<c\b([^>]*?)(?:\/>|>(.*?)<\/c>)/s', $rowContent, $cells, PREG_SET_ORDER)) {
                continue;
            }

            $nextColIndex = 0;
            foreach ($cells as $cell) {
                $attrs = $cell[1];
                $inner = $cell[2] ?? '';
                if (preg_match('/\br="([A-Z]+)(\d+)"/', $attrs, $cm)) {
                    $colLetter = $cm[1];
                    $nextColIndex = $this->columnToIndex($colLetter) + 1;
                } else {
                    $colLetter = $this->indexToColumn($nextColIndex);
                    $nextColIndex++;
                }
                $grid[$rowNum][$colLetter] = $this->cellValue($attrs, $inner, $shared);
            }
        }

        ksort($grid);
        return $grid;
    }

    /**
     * @param array<int, array<string, string>> $grid
     * @return array<int, array{0: string, 1: string}>
     */
    private function gridToRows(array $grid): array
    {
        if (!$grid) {
            return [];
        }

        $frontCol = 'A';
        $backCol = 'B';
        $firstKey = array_key_first($grid);
        $firstRow = $grid[$firstKey] ?? [];

        foreach ($firstRow as $col => $val) {
            $lower = strtolower(trim($val));
            if (in_array($lower, ['front', 'question', 'prompt', 'term', 'card', 'word'], true)) {
                $frontCol = $col;
            }
            if (in_array($lower, ['back', 'answer', 'response', 'definition', 'meaning'], true)) {
                $backCol = $col;
            }
        }

        if ($this->isHeaderRow($firstRow[$frontCol] ?? '', $firstRow[$backCol] ?? '')) {
            unset($grid[$firstKey]);
        }

        $rows = [];
        foreach ($grid as $cols) {
            $rows[] = [$cols[$frontCol] ?? '', $cols[$backCol] ?? ''];
        }
        return $rows;
    }

    /** @param string[] $shared */
    private function cellValue(string $attrs, string $inner, array $shared): string
    {
        $type = '';
        if (preg_match('/\bt="([^"]+)"/', $attrs, $m)) {
            $type = $m[1];
        }

        if ($type === 'inlineStr' || str_contains($inner, '<is>')) {
            if (preg_match_all('/<t(?:\s[^>]*)?>(.*?)<\/t>/s', $inner, $parts)) {
                return html_entity_decode(implode('', $parts[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        if (preg_match('/<v>(.*?)<\/v>/s', $inner, $m)) {
            $raw = $m[1];
            if ($type === 's') {
                return $shared[(int) $raw] ?? '';
            }
            return html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('/<t(?:\s[^>]*)?>(.*?)<\/t>/s', $inner, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return '';
    }

    private function columnToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }
        return max(0, $index - 1);
    }

    private function indexToColumn(int $index): string
    {
        $index++;
        $letters = '';
        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = intdiv($index, 26);
        }
        return $letters;
    }
}
