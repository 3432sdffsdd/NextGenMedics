<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Jwt;
use App\Repositories\ContentRepository;
use App\Repositories\UserRepository;
use App\Services\CourseService;
use App\Services\TextExtractionService;

class MediaController extends BaseController
{
    public function __construct(
        private Jwt $jwt,
        private UserRepository $users,
        private ContentRepository $content,
        private CourseService $courseService,
        private TextExtractionService $textExtractor
    ) {}

    /** JSON preview for Word / PowerPoint (in-app viewer). */
    public function previewDocument(Request $request): void
    {
        [$relativePath] = $this->resolveAuthorizedRelativePath($request);
        if (!$relativePath) {
            return;
        }

        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['doc', 'docx', 'ppt', 'pptx'], true)) {
            Response::error('Preview is only available for Word and PowerPoint files', 422);
            return;
        }

        try {
            Response::success(['html' => $this->textExtractor->toPreviewHtml($relativePath)]);
        } catch (\Throwable $e) {
            Response::error($e->getMessage(), 422);
        }
    }

    public function serve(Request $request): void
    {
        [$relativePath, $user] = $this->resolveAuthorizedRelativePath($request);
        if (!$relativePath) {
            return;
        }

        $storageRoot = realpath(__DIR__ . '/../../storage');
        $fullPath = realpath($storageRoot . '/' . $relativePath);
        if (!$fullPath || !$storageRoot || !str_starts_with($fullPath, $storageRoot) || !is_file($fullPath)) {
            Response::error('File not found', 404);
            return;
        }

        $fileSize = filesize($fullPath);
        $mime = $this->detectMime($fullPath);
        $filename = basename($fullPath);
        $download = $request->query('download') === '1';
        $isHead = $request->method() === 'HEAD';

        if ($download && $this->isVideoFile($fullPath, $mime) && ($user['role'] ?? '') === 'student') {
            $courseId = $this->content->getCourseIdFromFilePath($relativePath);
            if ($courseId && !$this->courseService->canDownloadVideos($courseId, $user)) {
                Response::error('Video download is not enabled for your account. Ask your admin to allow downloads.', 403);
                return;
            }
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mime);
        header_remove('X-Frame-Options');
        header('Accept-Ranges: bytes');
        header('Cache-Control: private, max-age=3600');
        header('X-Accel-Buffering: no');
        if ($download) {
            header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        } else {
            $safe = preg_replace('/[^\x20-\x7E]/', '_', $filename) ?: 'file';
            header('Content-Disposition: inline; filename="' . $safe . '"');
        }

        $rangeHeader = $this->getRangeHeader();
        if ($rangeHeader) {
            $parsed = $this->parseByteRange($rangeHeader, $fileSize);
            if ($parsed === null) {
                http_response_code(416);
                header("Content-Range: bytes */{$fileSize}");
                exit;
            }
            [$start, $end] = $parsed;
            $length = $end - $start + 1;
            http_response_code(206);
            header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
            header('Content-Length: ' . $length);
            if ($isHead) {
                exit;
            }
            $fp = fopen($fullPath, 'rb');
            if ($fp === false) {
                Response::error('Could not read file', 500);
                return;
            }
            fseek($fp, $start);
            $remaining = $length;
            $chunkSize = 262144;
            while ($remaining > 0 && !feof($fp)) {
                $chunk = fread($fp, min($chunkSize, $remaining));
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
                $remaining -= strlen($chunk);
                if (connection_aborted()) {
                    break;
                }
            }
            fclose($fp);
            exit;
        }

        header('Content-Length: ' . $fileSize);
        if ($isHead) {
            exit;
        }
        readfile($fullPath);
        exit;
    }

    private function getRangeHeader(): ?string
    {
        $range = $_SERVER['HTTP_RANGE'] ?? $_SERVER['REDIRECT_HTTP_RANGE'] ?? null;
        if (!$range && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    if (strcasecmp((string) $key, 'Range') === 0) {
                        $range = $value;
                        break;
                    }
                }
            }
        }
        if (!$range && function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    if (strcasecmp((string) $key, 'Range') === 0) {
                        $range = $value;
                        break;
                    }
                }
            }
        }
        return is_string($range) && trim($range) !== '' ? trim($range) : null;
    }

    /** @return array{0:int,1:int}|null [start, end] inclusive */
    private function parseByteRange(string $range, int $fileSize): ?array
    {
        if ($fileSize <= 0 || !preg_match('/bytes=(.*)/i', $range, $m)) {
            return null;
        }
        $spec = trim(explode(',', $m[1])[0]);
        if ($spec === '') {
            return null;
        }

        // Suffix range: bytes=-500 (last 500 bytes) — needed for MP4 metadata at file end
        if (preg_match('/^-(\d+)$/', $spec, $sm)) {
            $length = min((int) $sm[1], $fileSize);
            return [$fileSize - $length, $fileSize - 1];
        }

        // bytes=start-end or bytes=start-
        if (!preg_match('/^(\d+)-(\d*)$/', $spec, $sm)) {
            return null;
        }
        $start = (int) $sm[1];
        $end = $sm[2] !== '' ? (int) $sm[2] : $fileSize - 1;
        if ($start > $end || $start >= $fileSize) {
            return null;
        }
        return [$start, min($end, $fileSize - 1)];
    }

    /** Auth + path check shared by file streaming and document preview.
     *  @return array{0:?string,1:?array} [relativePath, user]
     */
    private function resolveAuthorizedRelativePath(Request $request): array
    {
        $token = $request->bearerToken() ?? $request->query('token');
        if (!$token) {
            Response::error('Unauthorized', 401);
            return [null, null];
        }

        $payload = $this->jwt->decode($token);
        if (!$payload || !isset($payload['sub'])) {
            Response::error('Invalid or expired token', 401);
            return [null, null];
        }

        $user = $this->users->findById((int) $payload['sub']);
        if (!$user || $user['status'] !== 'active') {
            Response::error('Unauthorized', 401);
            return [null, null];
        }

        $relativePath = $request->query('path');
        if (!$relativePath || str_contains($relativePath, '..')) {
            Response::error('Invalid path', 422);
            return [null, null];
        }

        $relativePath = str_replace('\\', '/', $relativePath);
        $relativePath = ltrim($relativePath, '/');
        if (str_starts_with($relativePath, 'storage/')) {
            $relativePath = substr($relativePath, strlen('storage/'));
        }
        if (str_starts_with($relativePath, 'uploads/') === false && str_contains($relativePath, 'uploads/')) {
            $relativePath = preg_replace('#^.*?uploads/#', 'uploads/', $relativePath) ?? $relativePath;
        }

        $courseId = $this->content->getCourseIdFromFilePath($relativePath);
        if ($courseId) {
            if (!$this->courseService->canAccess($courseId, $user)) {
                Response::error('Forbidden', 403);
                return [null, null];
            }
        } elseif (preg_match('#uploads/submissions/(\d+)/#', $relativePath, $m)) {
            $ownerId = (int) $m[1];
            if ($user['role'] === 'student' && $ownerId !== (int) $user['id']) {
                Response::error('Forbidden', 403);
                return [null, null];
            }
        } elseif (preg_match('#uploads/courses/(\d+)/assignments/#', $relativePath, $m)) {
            if (!$this->courseService->canAccess((int) $m[1], $user)) {
                Response::error('Forbidden', 403);
                return [null, null];
            }
        } else {
            Response::error('Forbidden', 403);
            return [null, null];
        }

        return [$relativePath, $user];
    }

    private function isVideoFile(string $fullPath, string $mime): bool
    {
        if (str_starts_with($mime, 'video/')) {
            return true;
        }
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'mov', 'm4v', 'mkv', 'avi'], true);
    }

    private function detectMime(string $fullPath): string
    {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $map = [
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'mov'  => 'video/quicktime',
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv'  => 'text/csv',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'html' => 'text/html',
            'htm'  => 'text/html',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'txt'  => 'text/plain; charset=utf-8',
            'md'   => 'text/plain; charset=utf-8',
        ];
        if (isset($map[$ext])) {
            return $map[$ext];
        }
        $detected = mime_content_type($fullPath);
        return $detected ?: 'application/octet-stream';
    }
}
