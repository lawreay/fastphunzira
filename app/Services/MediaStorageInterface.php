<?php

namespace App\Services;

interface MediaStorageInterface
{
    public function store(array $file, string $directory, array $allowedMimes, int $maxBytes): array;
    public function delete(string $relativePath): bool;
    public function absolutePath(string $relativePath): string;
}
