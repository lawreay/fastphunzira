<?php

namespace App\Repositories;

interface QuizAttemptRepositoryInterface
{
    public function create(array $attempt): array;
    public function findById(int $id): ?array;
    public function findActiveByStudentAndQuiz(int $studentId, int $quizId): ?array;
    public function countByStudentAndQuiz(int $studentId, int $quizId): int;
    public function saveAnswer(array $answer): array;
    public function finalize(int $attemptId, array $result): ?array;
    public function findAnswers(int $attemptId): array;
}
