<?php
namespace App\Helpers;

class FileUploadHelper
{
    private const ALLOWED = [
        'video' => ['mp4', 'webm', 'mov'],
        'pdf'   => ['pdf'],
        'doc'   => ['doc', 'docx'],
        'ppt'   => ['ppt', 'pptx'],
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'zip'   => ['zip'],
        'all'   => ['mp4', 'webm', 'mov', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'md', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'xlsx', 'xls', 'csv', 'html', 'htm'],
        'spreadsheet' => ['xlsx', 'xls', 'csv'],
    ];

    private const MAX_SIZE = [
        'video' => 524288000,  // 500MB
        'default' => 52428800, // 50MB
    ];

    public static function upload(array $file, string $category = 'all', string $subdir = ''): ?array
    {
        try {
            return self::uploadOrFail($file, $category, $subdir);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @throws \RuntimeException */
    public static function uploadOrFail(array $file, string $category = 'all', string $subdir = ''): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(self::uploadErrorMessage((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)));
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = self::ALLOWED[$category] ?? self::ALLOWED['all'];

        if (!in_array($ext, $allowed, true)) {
            throw new \RuntimeException("File type .{$ext} is not allowed");
        }

        $maxSize = self::MAX_SIZE[$category] ?? self::MAX_SIZE['default'];
        if ($file['size'] > $maxSize) {
            throw new \RuntimeException('File exceeds maximum upload size (' . round($maxSize / 1048576) . ' MB limit)');
        }

        $uploadDir = __DIR__ . '/../../storage/uploads/' . trim($subdir, '/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $path = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new \RuntimeException('Could not save uploaded file on server');
        }

        if (in_array($ext, ['mp4', 'mov'], true)) {
            self::optimizeMp4ForStreaming($path);
        }

        $relativePath = 'uploads/' . trim($subdir, '/') . '/' . $filename;

        return [
            'original_name' => $file['name'],
            'filename'      => $filename,
            'path'          => $relativePath,
            'mime_type'     => $file['type'],
            'size'          => $file['size'],
            'extension'     => $ext,
        ];
    }

    public static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File exceeds server upload limit. Try a smaller file or ask admin to increase upload_max_filesize.',
            UPLOAD_ERR_PARTIAL => 'File upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            default => 'File upload failed (error ' . $code . ')',
        };
    }

    public static function delete(string $relativePath): void
    {
        $fullPath = __DIR__ . '/../../storage/' . ltrim($relativePath, '/');
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    /** Move MP4/MOV moov atom to start so browsers can seek/scrub (requires ffmpeg on server). */
    private static function optimizeMp4ForStreaming(string $absolutePath): void
    {
        if (!is_file($absolutePath)) {
            return;
        }
        $ffmpeg = self::findExecutable('ffmpeg');
        if (!$ffmpeg) {
            return;
        }
        $tmp = $absolutePath . '.faststart.mp4';
        $cmd = sprintf(
            '%s -y -i %s -c copy -movflags +faststart %s 2>&1',
            escapeshellarg($ffmpeg),
            escapeshellarg($absolutePath),
            escapeshellarg($tmp)
        );
        exec($cmd, $output, $code);
        if ($code === 0 && is_file($tmp) && filesize($tmp) > 0) {
            rename($tmp, $absolutePath);
        } elseif (is_file($tmp)) {
            @unlink($tmp);
        }
    }

    private static function findExecutable(string $name): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = sprintf('where %s 2>NUL', escapeshellarg($name));
        } else {
            $cmd = sprintf('command -v %s 2>/dev/null', escapeshellarg($name));
        }
        $path = trim((string) shell_exec($cmd));
        return $path !== '' ? explode("\n", $path)[0] : null;
    }
}
