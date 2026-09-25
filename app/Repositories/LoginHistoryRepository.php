<?php

namespace App\Repositories;

use PDO;

final class LoginHistoryRepository implements LoginHistoryRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function record(array $entry): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO login_history (user_id, email, status, ip_address, user_agent, created_at)
             VALUES (:user_id, :email, :status, :ip_address, :user_agent, NOW())'
        );

        $statement->execute([
            ':user_id' => isset($entry['user_id']) ? (int) $entry['user_id'] : null,
            ':email' => strtolower(trim((string) ($entry['email'] ?? 'unknown'))),
            ':status' => (string) ($entry['status'] ?? 'failed'),
            ':ip_address' => isset($entry['ip_address']) ? (string) $entry['ip_address'] : null,
            ':user_agent' => isset($entry['user_agent']) ? (string) $entry['user_agent'] : null,
        ]);

        $entry['id'] = (int) $this->pdo->lastInsertId();

        return $entry;
    }

    public function countRecentFailures(string $email, string $ipAddress, string $since): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                SUM(CASE WHEN email = :email THEN 1 ELSE 0 END) AS email_failures,
                SUM(CASE WHEN ip_address = :ip_address THEN 1 ELSE 0 END) AS ip_failures
             FROM login_history
             WHERE status = 'failed'
               AND created_at >= :since"
        );

        $statement->execute([
            ':email' => strtolower(trim($email)),
            ':ip_address' => $ipAddress,
            ':since' => $since,
        ]);

        $counts = $statement->fetch() ?: [];

        return [
            'email_failures' => (int) ($counts['email_failures'] ?? 0),
            'ip_failures' => (int) ($counts['ip_failures'] ?? 0),
        ];
    }
}
