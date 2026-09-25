<?php

namespace App\Services;

use App\Repositories\CertificateVerificationAttemptRepositoryInterface;

final class CertificateVerificationRateLimitService
{
    private int $windowSeconds;
    private int $maxAttemptsPerIp;

    public function __construct(
        private CertificateVerificationAttemptRepositoryInterface $repository,
        array $config = []
    ) {
        $this->windowSeconds = max(1, (int) ($config['window_seconds'] ?? 900));
        $this->maxAttemptsPerIp = max(1, (int) ($config['max_attempts_per_ip'] ?? 30));
    }

    public function isThrottled(string $ipAddress): bool
    {
        $ipAddress = trim($ipAddress);

        if ($ipAddress === '') {
            return false;
        }

        return $this->repository->countRecentByIp($ipAddress, $this->windowSeconds) >= $this->maxAttemptsPerIp;
    }

    public function recordAttempt(string $ipAddress, string $certificateNumber, bool $successful): void
    {
        $ipAddress = trim($ipAddress);

        if ($ipAddress === '') {
            return;
        }

        $this->repository->record($ipAddress, trim($certificateNumber), $successful);
    }
}
