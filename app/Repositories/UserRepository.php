<?php

namespace App\Repositories;

use PDO;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $user): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash, status, role_id, force_password_change, created_at)
            VALUES (:full_name, :email, :password_hash, :status, :role_id, :force_password_change, NOW())'
        );

        $statement->execute([
            ':full_name' => $user['full_name'],
            ':email' => strtolower(trim($user['email'])),
            ':password_hash' => $user['password_hash'],
            ':status' => $user['status'] ?? 'active',
            ':role_id' => $user['role_id'] ?? null,
            ':force_password_change' => (int) ($user['force_password_change'] ?? 1),
        ]);

        $userId = (int) $this->pdo->lastInsertId();
        $roleName = $user['role'] ?? 'student';

        $roleStatement = $this->pdo->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
        $roleStatement->execute([':name' => $roleName]);
        $role = $roleStatement->fetch();

        if ($role) {
            $assignStatement = $this->pdo->prepare(
                'INSERT INTO user_roles (user_id, role_id, created_at)
                VALUES (:user_id, :role_id, NOW())
                ON DUPLICATE KEY UPDATE user_id = user_id'
            );
            $assignStatement->execute([
                ':user_id' => $userId,
                ':role_id' => (int) $role['id'],
            ]);
        }

        $user['id'] = $userId;
        $user['email'] = strtolower(trim($user['email']));

        return $user;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.*, r.name AS role
             FROM users u
             LEFT JOIN user_roles ur ON ur.user_id = u.id
             LEFT JOIN roles r ON r.id = ur.role_id
             WHERE u.email = :email AND u.deleted_at IS NULL
             LIMIT 1'
        );

        $statement->execute([':email' => strtolower(trim($email))]);
        $user = $statement->fetch();

        if ($user === false) {
            return null;
        }

        $user['role'] = $user['role'] ?? 'student';

        return $user;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.*, r.name AS role
             FROM users u
             LEFT JOIN user_roles ur ON ur.user_id = u.id
             LEFT JOIN roles r ON r.id = ur.role_id
             WHERE u.id = :id AND u.deleted_at IS NULL
             LIMIT 1'
        );

        $statement->execute([':id' => $id]);
        $user = $statement->fetch();

        if ($user === false) {
            return null;
        }

        $user['role'] = $user['role'] ?? 'student';

        return $user;
    }

    public function userExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}
