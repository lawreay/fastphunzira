<?php
namespace App\Repositories;

interface ExamRepositoryInterface
{
    public function create(array $exam): array;
    public function findById(int $id): ?array;
    public function findByCourse(int $courseId): array;
    public function updateStatus(int $id, string $status): ?array;
    public function addQuestion(int $examId, int $questionId, float $marks, int $sortOrder): array;
    public function findQuestions(int $examId): array;
}