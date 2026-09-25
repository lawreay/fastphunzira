<?php

namespace App\Repositories;

use PDO;

final class CertificateVerificationAttemptRepository implements CertificateVerificationAttemptRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function countRecentByIp(string $ipAddress, int $windowSeconds): int
    {
        $windowSeconds = max(1, $windowSeconds);

        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM certificate_verification_attempts
             WHERE ip_address = :ip_address
               AND created_at >= DATE_SUB(NOW(), INTERVAL ' . $windowSeconds . ' SECOND)'
        );
        $statement->execute([':ip_address' => $ipAddress]);

        return (int) $statement->fetchColumn();
    }

    public function record(string $ipAddress, string $certificateNumber, bool $successful): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO certificate_verification_attempts
                (ip_address, certificate_number_hash, successful, created_at)
             VALUES
                (:ip_address, :certificate_number_hash, :successful, NOW())'
        );

        $statement->execute([
            ':ip_address' => $ipAddress,
            ':certificate_number_hash' => hash('sha256', $certificateNumber),
            ':successful' => $successful ? 1 : 0,
        ]);
    }
}
