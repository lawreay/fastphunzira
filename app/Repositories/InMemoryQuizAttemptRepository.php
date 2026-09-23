<?php

namespace App\Repositories;

final class InMemoryQuizAttemptRepository implements QuizAttemptRepositoryInterface
{
    private array $attempts = [];
    private array $answers = [];
    private int $nextAttemptId = 1;

    public function create(array $attempt): array
    {
        $attempt['id'] = $this->nextAttemptId++;
        $this->attempts[$attempt['id']] = $attempt;
        return $attempt;
    }

    public function findById(int $id): ?array
    {
        return $this->attempts[$id] ?? null;
    }

    public function findActiveByStudentAndQuiz(int $studentId, int $quizId): ?array
    {
        foreach (array_reverse($this->attempts, true) as $attempt) {
            if ((int) $attempt['user_id'] === $studentId && (int) $attempt['quiz_id'] === $quizId && ($attempt['status'] ?? '') === 'in_progress') {
                return $attempt;
            }
        }
        return null;
    }

    public function countByStudentAndQuiz(int $studentId, int $quizId): int
    {
        return count(array_filter($this->attempts, fn(array $attempt): bool =>
            (int) $attempt['user_id'] === $studentId && (int) $attempt['quiz_id'] === $quizId
        ));
    }

    public function saveAnswer(array $answer): array
    {
        foreach ($this->answers as $index => $existing) {
            if ((int) $existing['attempt_id'] === (int) $answer['attempt_id'] && (int) $existing['question_id'] === (int) $answer['question_id']) {
                $this->answers[$index] = $answer;
                return $answer;
            }
        }
        $this->answers[] = $answer;
        return $answer;
    }

    public function finalize(int $attemptId, array $result): ?array
    {
        if (!isset($this->attempts[$attemptId]) || ($this->attempts[$attemptId]['status'] ?? '') !== 'in_progress') {
            return null;
        }
        $this->attempts[$attemptId] = array_merge($this->attempts[$attemptId], [
            'status' => 'submitted',
            'score' => (int) $result['score'],
            'percentage' => (float) $result['percentage'],
            'passed' => !empty($result['passed']) ? 1 : 0,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->attempts[$attemptId];
    }

    public function findAnswers(int $attemptId): array
    {
        return array_values(array_filter($this->answers, fn(array $answer): bool => (int) $answer['attempt_id'] === $attemptId));
    }
}
