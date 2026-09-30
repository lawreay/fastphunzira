<?php

namespace App\Repositories;

final class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $users = [];

    public function create(array $user): array
    {
        $this->users[$user['email']] = $user;

        return $user;
    }

    public function findByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));

        return $this->users[$email] ?? null;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->users as $user) {
            if ((int) ($user['id'] ?? 0) === $id) {
                return $user;
            }
        }

        return null;
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        foreach ($this->users as $email => $user) {
            if ((int) ($user['id'] ?? 0) === $id) {
                $this->users[$email]['password_hash'] = $passwordHash;
                $this->users[$email]['force_password_change'] = 0;

                return true;
            }
        }

        return false;
    }

    public function userExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}
