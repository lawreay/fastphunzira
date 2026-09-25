<?php

namespace App\Repositories;

use PDO;

final class LoginHistoryRepository implements LoginHistoryRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function record(array $attempt): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO login_history (user_id, email, status, ip_address, user_agent, created_at)
             VALUES (:user_id, :email, :status, :ip_address, :user_agent, NOW())'
        );

        $statement->execute([
            ':user_id' => $attempt['user_id'] ?? null,
            ':email' => strtolower(trim((string) ($attempt['email'] ?? ''))),
            ':status' => $attempt['status'],
            ':ip_address' => $attempt['ip_address'] ?? null,
            ':user_agent' => $attempt['user_agent'] ?? null,
        ]);

        $attempt['id'] = (int) $this->pdo->lastInsertId();
        $attempt['created_at'] = $attempt['created_at'] ?? date('Y-m-d H:i:s');

        return $attempt;
    }

    public function countFailuresSinceLastSuccessByEmail(string $email, int $windowSeconds): int
    {
        $windowSeconds = max(1, $windowSeconds);
        $email = strtolower(trim($email));

        $statement = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM login_history h
             WHERE h.email = :email
               AND h.status = 'failed'
               AND h.created_at >= DATE_SUB(NOW(), INTERVAL {$windowSeconds} SECOND)
               AND h.created_at > COALESCE(
                   (
                       SELECT MAX(s.created_at)
                       FROM login_history s
                       WHERE s.email = :success_email
                         AND s.status = 'success'
                   ),
                   '1970-01-01 00:00:00'
               )"
        );

        $statement->execute([
            ':email' => $email,
            ':success_email' => $email,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function countFailuresByIp(string $ipAddress, int $windowSeconds): int
    {
        $windowSeconds = max(1, $windowSeconds);

        if ($ipAddress === '') {
            return 0;
        }

        $statement = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM login_history
             WHERE ip_address = :ip
               AND status = 'failed'
               AND created_at >= DATE_SUB(NOW(), INTERVAL {$windowSeconds} SECOND)"
        );

        $statement->execute([':ip' => $ipAddress]);

        return (int) $statement->fetchColumn();
    }
}
