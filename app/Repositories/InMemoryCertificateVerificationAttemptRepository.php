<?php

namespace App\Repositories;

final class InMemoryCertificateVerificationAttemptRepository implements CertificateVerificationAttemptRepositoryInterface
{
    private array $attempts = [];

    public function countRecentByIp(string $ipAddress, int $windowSeconds): int
    {
        $cutoff = time() - max(1, $windowSeconds);

        return count(array_filter($this->attempts, static function (array $attempt) use ($ipAddress, $cutoff): bool {
            return $attempt['ip_address'] === $ipAddress
                && $attempt['created_at'] >= $cutoff;
        }));
    }

    public function record(string $ipAddress, string $certificateNumber, bool $successful): void
    {
        $this->attempts[] = [
            'ip_address' => $ipAddress,
            'certificate_number_hash' => hash('sha256', $certificateNumber),
            'successful' => $successful,
            'created_at' => time(),
        ];
    }
}
