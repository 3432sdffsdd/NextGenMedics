<?php
namespace App\Services;

/**
 * Extracts readable text from uploaded lecture files.
 *
 * Pure-PHP implementations (no Composer dependency required):
 *   - .txt / .md            → raw read
 *   - .pptx / .docx / .xlsx → ZipArchive + Office Open XML parsing
 *   - .pdf                  → best-effort stream extraction (FlateDecode)
 *
 * PDF text extraction is inherently imperfect in pure PHP. Teachers can review
 * and edit the extracted text before generation, so imperfect extraction never
 * blocks the workflow.
 */
class TextExtractionService
{
    private const STORAGE_ROOT = __DIR__ . '/../../storage/';

    /** Resolve a stored relative path (e.g. "uploads/...") to an absolute path. */
    public function resolvePath(string $relativePath): string
    {
        return self::STORAGE_ROOT . ltrim($relativePath, '/\\');
    }

    /**
     * Extract text from a stored file (relative path). Returns a cleaned,
     * whitespace-normalised string. Throws if the file cannot be read.
     */
    public function extract(string $relativePath, ?string $extension = null): string
    {
        return $this->extractAbsolute($this->resolvePath($relativePath), $extension);
    }

    /** Extract text from an absolute file path (e.g. PHP upload temp file). */
    public function extractAbsolute(string $absolutePath, ?string $extension = null): string
    {
        if (!is_file($absolutePath)) {
            throw new \RuntimeException('Source file not found for text extraction.');
        }

        $ext = strtolower($extension ?: pathinfo($absolutePath, PATHINFO_EXTENSION));

        $text = match ($ext) {
            'txt', 'md', 'csv' => (string) file_get_contents($absolutePath),
            'html', 'htm'      => $this->fromHtml($absolutePath),
            'pptx'             => $this->fromOfficeXml($absolutePath, 'ppt/slides/slide'),
            'ppt'              => $this->fromPpt($absolutePath),
            'docx'             => $this->fromOfficeXml($absolutePath, 'word/document'),
            'doc'              => $this->fromDoc($absolutePath),
            'pdf'              => $this->fromPdf($absolutePath),
            default            => throw new \RuntimeException("Unsupported file type for extraction: .{$ext}"),
        };

        return $this->clean($text);
    }

    /** HTML preview for in-app office viewer (Word + PowerPoint). */
    public function toPreviewHtml(string $relativePath): string
    {
        $absolute = $this->resolvePath($relativePath);
        if (!is_file($absolute)) {
            throw new \RuntimeException('File not found');
        }
        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
        return match ($ext) {
            'docx' => $this->docxToHtml($absolute),
            'doc'  => $this->textToHtml($this->fromDoc($absolute)),
            'pptx' => $this->pptxToHtml($absolute),
            'ppt'  => $this->pptToHtml($absolute),
            default => throw new \RuntimeException('Preview is not available for this file type'),
        };
    }

    /** Legacy binary Word (.doc) — antiword/catdoc if available, else binary text extraction. */
    private function fromDoc(string $absolute): string
    {
        foreach (['antiword', 'catdoc'] as $cmd) {
            $bin = $this->findExecutable($cmd);
            if ($bin) {
                $out = shell_exec(escapeshellarg($bin) . ' ' . escapeshellarg($absolute) . ' 2>&1');
                if (is_string($out) && strlen(trim($out)) > 10) {
                    return $this->clean($out);
                }
            }
        }

        $data = (string) file_get_contents($absolute);
        $text = $this->fromDocBinary($data);
        if (trim($text) === '') {
            throw new \RuntimeException('Could not read this .doc file. Try saving as .docx and re-uploading.');
        }
        return $this->clean($text);
    }

    private function fromDocBinary(string $data): string
    {
        $chunks = [];
        if (preg_match_all('/[\x09\x0A\x0D\x20-\x7E]{8,}/', $data, $m)) {
            $chunks = array_merge($chunks, $m[0]);
        }

        $run = '';
        $len = strlen($data);
        for ($i = 0; $i < $len - 1; $i += 2) {
            $lo = ord($data[$i]);
            $hi = ord($data[$i + 1]);
            if ($hi === 0 && ($lo >= 32 || in_array($lo, [9, 10, 13], true))) {
                $run .= chr($lo);
            } else {
                if (strlen(trim($run)) >= 4) {
                    $chunks[] = trim($run);
                }
                $run = '';
            }
        }
        if (strlen(trim($run)) >= 4) {
            $chunks[] = trim($run);
        }

        $chunks = array_values(array_unique(array_filter(array_map('trim', $chunks))));
        usort($chunks, fn($a, $b) => strlen($b) <=> strlen($a));
        return implode("\n\n", array_slice($chunks, 0, 200));
    }

    private function docxToHtml(string $absolute): string
    {
        if (!class_exists(\ZipArchive::class)) {
            return $this->textToHtml($this->fromOfficeXml($absolute, 'word/document'));
        }
        $zip = new \ZipArchive();
        if ($zip->open($absolute) !== true) {
            throw new \RuntimeException('Could not open Word document');
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (!$xml) {
            throw new \RuntimeException('Invalid .docx file');
        }

        $html = [];
        if (preg_match_all('/<w:p[^>]*>(.*?)<\/w:p>/s', $xml, $paras)) {
            foreach ($paras[1] as $para) {
                $text = '';
                if (preg_match_all('/<w:t(?:\s[^>]*)?>(.*?)<\/w:t>/s', $para, $runs)) {
                    foreach ($runs[1] as $t) {
                        $text .= html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
                $text = trim($text);
                if ($text !== '') {
                    $html[] = '<p>' . htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</p>';
                }
            }
        }
        return implode("\n", $html) ?: '<p>Empty document</p>';
    }

    private function pptxToHtml(string $absolute): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('PHP zip extension is required to preview PowerPoint files.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($absolute) !== true) {
            throw new \RuntimeException('Could not open PowerPoint file');
        }

        $slides = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false || !preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name, $m)) {
                continue;
            }
            $slideNum = (int) $m[1];
            $xml = $zip->getFromIndex($i);
            if ($xml === false) {
                continue;
            }
            $slides[$slideNum] = $this->slideXmlToHtml($xml, $slideNum, $zip, $name);
        }
        $zip->close();

        if (!$slides) {
            throw new \RuntimeException('Invalid .pptx file — no slides found');
        }

        ksort($slides, SORT_NUMERIC);
        return '<div class="lms-ppt-preview">' . implode("\n", $slides) . '</div>';
    }

    private function slideXmlToHtml(string $xml, int $slideNum, \ZipArchive $zip, string $slidePath): string
    {
        $relsPath = preg_replace('#^ppt/slides/([^/]+\.xml)$#', 'ppt/slides/_rels/$1.rels', $slidePath);
        $mediaMap = $this->parseSlideRelationships($zip, $relsPath);

        $blocks = [];
        foreach ($this->extractSlideImages($xml, $mediaMap, $zip) as $imgHtml) {
            $blocks[] = $imgHtml;
        }

        if (preg_match_all('/<a:p[^>]*>(.*?)<\/a:p>/s', $xml, $paras)) {
            foreach ($paras[1] as $para) {
                $line = '';
                if (preg_match_all('/<a:t(?:\s[^>]*)?>(.*?)<\/a:t>/s', $para, $runs)) {
                    foreach ($runs[1] as $t) {
                        $line .= html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
                $line = trim($line);
                if ($line !== '') {
                    $blocks[] = '<p>' . htmlspecialchars($line, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</p>';
                }
            }
        }

        $body = implode("\n", $blocks);
        if ($body === '') {
            $body = '<p class="lms-slide-empty">(No text or images on this slide)</p>';
        }

        return '<section class="lms-slide">'
            . '<header class="lms-slide-header">Slide ' . $slideNum . '</header>'
            . '<div class="lms-slide-content">' . $body . '</div>'
            . '</section>';
    }

    /** @return array<string, string> rId => target path inside ppt/ */
    private function parseSlideRelationships(\ZipArchive $zip, string $relsPath): array
    {
        $xml = $zip->getFromName($relsPath);
        if ($xml === false) {
            return [];
        }
        $map = [];
        if (preg_match_all('/<Relationship\b[^>]*\bId="([^"]+)"[^>]*\bTarget="([^"]+)"/', $xml, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $map[$row[1]] = $row[2];
            }
        }
        return $map;
    }

    /** @return list<string> */
    private function extractSlideImages(string $xml, array $mediaMap, \ZipArchive $zip): array
    {
        if (!preg_match_all('/r:embed="([^"]+)"/', $xml, $embeds)) {
            return [];
        }

        $html = [];
        foreach (array_unique($embeds[1]) as $rid) {
            $target = $mediaMap[$rid] ?? null;
            if (!$target || !str_contains($target, 'media/')) {
                continue;
            }
            $mediaPath = 'ppt/' . ltrim(str_replace('../', '', $target), '/');
            $bin = $zip->getFromName($mediaPath);
            if ($bin === false || $bin === '') {
                continue;
            }
            $mime = $this->guessImageMime($mediaPath);
            $html[] = '<img src="data:' . $mime . ';base64,' . base64_encode($bin) . '" alt="" class="lms-slide-img" loading="lazy" />';
        }
        return $html;
    }

    private function guessImageMime(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',
            default       => 'image/png',
        };
    }

    private function pptToHtml(string $absolute): string
    {
        $text = $this->fromPpt($absolute);
        $chunks = preg_split('/\f|\x0C/u', $text) ?: [];
        $chunks = array_values(array_filter(array_map('trim', $chunks)));

        if (!$chunks) {
            return $this->textToHtml($text);
        }

        $slides = [];
        foreach ($chunks as $i => $chunk) {
            $num = $i + 1;
            $body = $this->textToHtml($chunk);
            $slides[] = '<section class="lms-slide">'
                . '<header class="lms-slide-header">Slide ' . $num . '</header>'
                . '<div class="lms-slide-content">' . $body . '</div>'
                . '</section>';
        }

        return '<div class="lms-ppt-preview">' . implode("\n", $slides) . '</div>';
    }

    /** Legacy binary PowerPoint (.ppt). */
    private function fromPpt(string $absolute): string
    {
        foreach (['catppt', 'ppthtml'] as $cmd) {
            $bin = $this->findExecutable($cmd);
            if ($bin) {
                $out = shell_exec(escapeshellarg($bin) . ' ' . escapeshellarg($absolute) . ' 2>&1');
                if (is_string($out) && strlen(trim(strip_tags($out))) > 10) {
                    return $this->clean(strip_tags($out));
                }
            }
        }

        $data = (string) file_get_contents($absolute);
        $text = $this->fromDocBinary($data);
        if (trim($text) === '') {
            throw new \RuntimeException('Could not read this .ppt file. Try saving as .pptx and re-uploading.');
        }
        return $this->clean($text);
    }

    private function textToHtml(string $text): string
    {
        $parts = preg_split('/\R{2,}/', trim($text)) ?: [];
        $html = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            foreach (preg_split('/\R/', $part) as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $html[] = '<p>' . htmlspecialchars($line, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</p>';
                }
            }
        }
        return implode("\n", $html) ?: '<p>Empty document</p>';
    }

    private function findExecutable(string $name): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = sprintf('where %s 2>NUL', escapeshellarg($name));
        } else {
            $cmd = sprintf('command -v %s 2>/dev/null', escapeshellarg($name));
        }
        $path = trim((string) shell_exec($cmd));
        return $path !== '' ? explode("\n", $path)[0] : null;
    }

    /** Strip tags and decode entities from HTML lecture files. */
    private function fromHtml(string $absolute): string
    {
        $html = (string) file_get_contents($absolute);
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', ' ', $html);
        $html = preg_replace('/<\/(?:p|div|h[1-6]|li|tr|br)\b[^>]*>/i', "\n", $html);
        $text = strip_tags($html);
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Parse Office Open XML (pptx/docx) by unzipping and stripping text nodes. */
    private function fromOfficeXml(string $absolute, string $entryPrefix): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('PHP zip extension is required to read Office files.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($absolute) !== true) {
            throw new \RuntimeException('Could not open the document archive.');
        }

        $parts = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false) {
                continue;
            }
            // pptx: ppt/slides/slide1.xml, slide2.xml ...  docx: word/document.xml
            if (!str_starts_with($name, $entryPrefix) || !str_ends_with($name, '.xml')) {
                continue;
            }
            $xml = $zip->getFromIndex($i);
            if ($xml === false) {
                continue;
            }
            $parts[$name] = $this->stripXmlText($xml);
        }
        $zip->close();

        // Keep slide/paragraph order stable.
        ksort($parts, SORT_NATURAL);
        return implode("\n", $parts);
    }

    /** Pull text out of Office XML — preserves soft line breaks (Shift+Enter) inside paragraphs. */
    private function stripXmlText(string $xml): string
    {
        $paragraphs = preg_split('/<\/(?:a|w):p>/', $xml, -1, PREG_SPLIT_NO_EMPTY);
        $lines = [];

        foreach ($paragraphs as $para) {
            // Soft breaks (Shift+Enter) are common when pasting a whole quiz into one Word paragraph.
            $chunks = preg_split('/<(?:a|w):br\s*\/>/', $para, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($chunks as $chunk) {
                if (!preg_match_all('/<(?:a|w):t[^>]*>(.*?)<\/(?:a|w):t>/s', $chunk, $m)) {
                    continue;
                }
                $line = implode('', array_map(
                    fn ($t) => html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    $m[1]
                ));
                $line = trim(preg_replace('/[ \t]+/', ' ', $line) ?? $line);
                if ($line !== '') {
                    $lines[] = $line;
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Best-effort PDF text extraction: inflate content streams and pull text
     * from Tj / TJ operators. Works for many text-based PDFs; scanned/image
     * PDFs will yield little (teacher can paste text manually in that case).
     */
    private function fromPdf(string $absolute): string
    {
        $data = (string) file_get_contents($absolute);
        $out = [];

        // Extract and inflate each stream object.
        if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $data, $streams)) {
            foreach ($streams[1] as $stream) {
                $decoded = @gzuncompress($stream);
                if ($decoded === false) {
                    $decoded = @gzinflate($stream);
                }
                $content = $decoded !== false ? $decoded : $stream;
                $out[] = $this->extractPdfOperators($content);
            }
        }

        $text = trim(implode("\n", array_filter($out)));

        // Fallback: some PDFs keep text uncompressed in the raw body.
        if ($text === '') {
            $text = $this->extractPdfOperators($data);
        }

        return $text;
    }

    /** Extract literal strings from Tj/TJ show-text operators. */
    private function extractPdfOperators(string $content): string
    {
        $pieces = [];

        // ( ... ) Tj   and   ( ... ) '   show-text
        if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)\s*(?:Tj|\')/', $content, $m)) {
            foreach ($m[0] as $chunk) {
                $pieces[] = $this->decodePdfString($chunk);
            }
        }
        // [ (a) (b) ] TJ  arrays
        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $content, $m)) {
            foreach ($m[1] as $arr) {
                if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)/', $arr, $inner)) {
                    foreach ($inner[0] as $chunk) {
                        $pieces[] = $this->decodePdfString($chunk);
                    }
                }
            }
        }

        return implode(' ', array_filter(array_map('trim', $pieces)));
    }

    private function decodePdfString(string $chunk): string
    {
        // Grab the parenthesised literal.
        if (!preg_match('/\((.*)\)/s', $chunk, $m)) {
            return '';
        }
        $s = $m[1];
        // Unescape common PDF escapes.
        $s = strtr($s, [
            '\\n' => "\n", '\\r' => "\r", '\\t' => "\t",
            '\\(' => '(', '\\)' => ')', '\\\\' => '\\',
        ]);
        return $s;
    }

    /** Normalise whitespace and drop control characters. */
    private function clean(string $text): string
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }
}
