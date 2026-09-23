<?php

namespace App\Repositories;

interface QuestionRepositoryInterface
{
    public function create(array $question): array;
    public function addOption(int $questionId, array $option): array;
    public function attachToQuiz(int $quizId, int $questionId, int $sortOrder = 0): void;
    public function findById(int $id): ?array;
    public function findByQuiz(int $quizId): array;
}
