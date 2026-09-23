<?php

namespace App\Repositories;

final class InMemoryQuizRepository implements QuizRepositoryInterface
{
    private array $quizzes = [];
    private int $nextId = 1;

    public function create(array $quiz): array
    {
        $quiz['id'] = $this->nextId++;
        $this->quizzes[$quiz['id']] = $quiz;
        return $quiz;
    }

    public function findById(int $id): ?array
    {
        return $this->quizzes[$id] ?? null;
    }

    public function updateStatus(int $id, string $status): ?array
    {
        if (!isset($this->quizzes[$id])) {
            return null;
        }
        $this->quizzes[$id]['status'] = $status;
        return $this->quizzes[$id];
    }

    public function findByCourse(int $courseId): array
    {
        return array_values(array_filter($this->quizzes, fn(array $quiz): bool => (int) $quiz['course_id'] === $courseId));
    }
}
