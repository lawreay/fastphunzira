<?php

namespace App\Repositories;

interface LessonRepositoryInterface
{
    public function create(array $lesson): array;

    public function update(int $id, array $data): ?array;

    public function findById(int $id): ?array;

    public function findByModule(int $moduleId): array;

    public function findByCourse(int $courseId): array;
}
