<?php

namespace App\Repositories;

interface QuizRepositoryInterface
{
    public function create(array $quiz): array;
    public function findById(int $id): ?array;
    public function findByCourse(int $courseId): array;
}
