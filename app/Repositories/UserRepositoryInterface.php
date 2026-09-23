<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function create(array $user): array;

    public function findByEmail(string $email): ?array;

    public function findById(int $id): ?array;

    public function userExists(string $email): bool;
}
