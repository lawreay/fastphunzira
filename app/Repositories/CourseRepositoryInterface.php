<?php

namespace App\Repositories;

interface CourseRepositoryInterface
{
    public function create(array $course): array;

    public function update(int $id, array $course): ?array;

    public function findById(int $id): ?array;

    public function findBySlug(string $slug): ?array;

    public function findAll(): array;

    public function findPublished(): array;

    public function getAll(): array;

    public function getPublished(): array;

    public function getBySlug(string $slug): ?array;
}
