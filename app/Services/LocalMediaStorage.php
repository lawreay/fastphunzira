<?php

namespace App\Services;

use RuntimeException;

final class LocalMediaStorage implements MediaStorageInterface
{
    public function __construct(private string $basePath)
    {
        $this->basePath = rtrim($this->basePath, DIRECTORY_SEPARATOR);
    }

    public function store(array $file, string $directory, array $allowedMimes, int $maxBytes): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file is larger than the server allows.',
                UPLOAD_ERR_PARTIAL => 'The file upload was interrupted. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was selected.',
                default => 'The file upload failed.',
            });
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if ($tmp === '' || !is_uploaded_file($tmp)) throw new RuntimeException('Invalid upload received.');
        if ($size <= 0 || $size > $maxBytes) throw new RuntimeException('The uploaded file exceeds the configured media size limit.');

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        if (!in_array($mime, $allowedMimes, true)) throw new RuntimeException('This file type is not supported.');

        $safeDirectory = trim($directory, '/\\');
        $targetDirectory = $this->basePath . DIRECTORY_SEPARATOR . $safeDirectory;
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0750, true) && !is_dir($targetDirectory)) throw new RuntimeException('Media storage could not be created.');

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $extension = preg_match('/^[a-z0-9]{1,8}$/i', $extension) ? '.' . $extension : '';
        $storedName = bin2hex(random_bytes(16)) . $extension;
        $relativePath = $safeDirectory . '/' . $storedName;
        $destination = $this->absolutePath($relativePath);
        if (!move_uploaded_file($tmp, $destination)) throw new RuntimeException('The uploaded file could not be stored.');
        @chmod($destination, 0640);

        return ['original_name' => basename((string) ($file['name'] ?? 'file')), 'stored_name' => $storedName, 'mime_type' => $mime, 'file_size' => $size, 'storage_path' => $relativePath];
    }

    public function delete(string $relativePath): bool
    {
        $path = $this->absolutePath($relativePath);
        return is_file($path) ? unlink($path) : false;
    }

    public function absolutePath(string $relativePath): string
    {
        $relativePath = str_replace(['\\', '..'], ['', ''], ltrim($relativePath, '/\\'));
        return $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
}
