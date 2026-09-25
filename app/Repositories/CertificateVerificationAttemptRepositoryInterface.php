<?php

namespace App\Repositories;

interface CertificateVerificationAttemptRepositoryInterface
{
    public function countRecentByIp(string $ipAddress, int $windowSeconds): int;

    public function record(string $ipAddress, string $certificateNumber, bool $successful): void;
}
