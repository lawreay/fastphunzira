<?php

namespace App\Repositories;

interface LoginHistoryRepositoryInterface
{
    public function record(array $attempt): array;

    public function countFailuresSinceLastSuccessByEmail(string $email, int $windowSeconds): int;

    public function countFailuresSinceLastSuccessByIp(string $ipAddress, int $windowSeconds): int;
}
