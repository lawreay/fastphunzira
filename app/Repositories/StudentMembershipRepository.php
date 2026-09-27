<?php

namespace App\Repositories;

use PDO;

final class StudentMembershipRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUserId(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM student_memberships WHERE user_id = :user_id LIMIT 1'
        );
        $statement->execute([':user_id' => $userId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function isPremiumActive(int $userId): bool
    {
        $membership = $this->findByUserId($userId);

        if ($membership === null || ($membership['plan'] ?? 'regular') !== 'premium' || ($membership['status'] ?? '') !== 'active') {
            return false;
        }

        if (!empty($membership['expires_at']) && strtotime((string) $membership['expires_at']) < time()) {
            return false;
        }

        return true;
    }

    public function activatePremium(int $userId, ?string $expiresAt): array
    {
        $existing = $this->findByUserId($userId);

        if ($existing === null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO student_memberships (user_id, plan, status, started_at, expires_at)
                 VALUES (:user_id, "premium", "active", CURRENT_TIMESTAMP, :expires_at)'
            );
        } else {
            $statement = $this->pdo->prepare(
                'UPDATE student_memberships
                 SET plan = "premium", status = "active", started_at = CURRENT_TIMESTAMP, expires_at = :expires_at
                 WHERE user_id = :user_id'
            );
        }

        $statement->execute([
            ':user_id' => $userId,
            ':expires_at' => $expiresAt,
        ]);

        return $this->findByUserId($userId) ?? [];
    }
}
