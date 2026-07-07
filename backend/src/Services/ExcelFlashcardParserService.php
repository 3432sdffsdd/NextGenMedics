<?php
namespace App\Services;

use App\Helpers\SimpleXlsReader;

/**
 * Reads simple two-column Excel (.xlsx, .xls, .csv) flashcard sheets (Front / Back).
 */
class ExcelFlashcardParserService
{
    /** @return array{valid: array<int,array{front:string,back:string}>, invalid: array, summary: array, topic: string} */
    public function parseFile(string $absolutePath, string $originalFilename): array
    {
        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $rows = match ($ext) {
            'csv'   => $this->readCsv($absolutePath),
            'xlsx'  => $this->readXlsx($absolutePath),
            'xls'   => $this->readXls($absolutePath),
            default => throw new \InvalidArgumentException('Upload an .xlsx, .xls, or .csv flashcard file'),
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
        return in_array($f, ['front', 'question', 'prompt', 'term'], true)
            && in_array($b, ['back', 'answer', 'response', 'definition'], true);
    }

    /** @return array<int, array{0: string, 1: string}> */
    private function readCsv(string $path): array
    {
        $rows = [];
        if (($fh = fopen($path, 'rb')) === false) {
            throw new \RuntimeException('Could not read CSV file');
        }
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
            throw new \RuntimeException('PHP zip extension is required to read Excel files');
        }
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Could not open Excel file. Save as .xlsx and try again.');
        }

        $shared = $this->loadSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if ($name && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }
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
            throw new \RuntimeException($err ?: 'Could not read .xls Excel file');
        }

        $rows = [];
        foreach ($xls->rows(0) as $row) {
            $rows[] = [
                trim((string) ($row[0] ?? '')),
                trim((string) ($row[1] ?? '')),
            ];
        }
        return $rows;
    }

    /** @return string[] */
    private function loadSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }
        $strings = [];
        if (preg_match_all('/<si>(.*?)<\/si>/s', $xml, $items)) {
            foreach ($items[1] as $chunk) {
                if (preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $chunk, $parts)) {
                    $strings[] = html_entity_decode(implode('', $parts[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
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
            foreach ($cells as $cell) {
                $attrs = $cell[1];
                $inner = $cell[2] ?? '';
                if (!preg_match('/\br="([A-Z]+)(\d+)"/', $attrs, $cm)) {
                    continue;
                }
                $grid[$rowNum][$cm[1]] = $this->cellValue($attrs, $inner, $shared);
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
            if (in_array($lower, ['front', 'question', 'prompt', 'term'], true)) {
                $frontCol = $col;
            }
            if (in_array($lower, ['back', 'answer', 'response', 'definition'], true)) {
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
            if (preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $inner, $parts)) {
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

        if (preg_match('/<t[^>]*>(.*?)<\/t>/s', $inner, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return '';
    }
}
