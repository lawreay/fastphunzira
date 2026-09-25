<?php

namespace App\Repositories;

interface AuditLogRepositoryInterface
{
    public function create(array $event): array;

    public function findRecent(int $limit = 20): array;
}
