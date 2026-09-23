<?php

namespace App\Repositories;

interface CourseModuleRepositoryInterface
{
    public function create(array $module): array;

    public function update(int $id, array $data): ?array;

    public function findById(int $id): ?array;

    public function findByCourse(int $courseId): array;
}
