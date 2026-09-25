<?php

namespace App\Repositories;

interface LoginHistoryRepositoryInterface
{
    public function record(array $entry): array;

    public function countRecentFailures(string $email, string $ipAddress, string $since): array;
}
