<?php
namespace App\Repositories;

interface ExamAttemptRepositoryInterface
{
    public function create(array $attempt): array;
    public function findById(int $id): ?array;
    public function findActiveByStudentAndExam(int $studentId, int $examId): ?array;
    public function countByStudentAndExam(int $studentId, int $examId): int;
    public function saveAnswer(array $answer): array;
    public function findAnswers(int $attemptId): array;
    public function finalize(int $attemptId, array $result): ?array;
}