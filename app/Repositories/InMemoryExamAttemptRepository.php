<?php

namespace App\Repositories;

final class InMemoryExamAttemptRepository implements ExamAttemptRepositoryInterface
{
    private array $attempts = [];
    private array $answers = [];
    private int $nextAttemptId = 1;
    private int $nextAnswerId = 1;

    public function create(array $attempt): array
    {
        $attempt['id'] = $this->nextAttemptId++;
        $attempt['status'] = $attempt['status'] ?? 'in_progress';
        $attempt['score'] = (float) ($attempt['score'] ?? 0);
        $attempt['percentage'] = (float) ($attempt['percentage'] ?? 0);
        $attempt['passed'] = !empty($attempt['passed']) ? 1 : 0;
        $this->attempts[$attempt['id']] = $attempt;

        return $attempt;
    }

    public function findById(int $id): ?array
    {
        return $this->attempts[$id] ?? null;
    }

    public function findActiveByStudentAndExam(int $studentId, int $examId): ?array
    {
        foreach (array_reverse($this->attempts, true) as $attempt) {
            if ((int) ($attempt['user_id'] ?? 0) === $studentId && (int) ($attempt['exam_id'] ?? 0) === $examId && ($attempt['status'] ?? '') === 'in_progress') {
                return $attempt;
            }
        }

        return null;
    }

    public function countByStudentAndExam(int $studentId, int $examId): int
    {
        return count(array_filter($this->attempts, static fn (array $attempt): bool =>
            (int) ($attempt['user_id'] ?? 0) === $studentId && (int) ($attempt['exam_id'] ?? 0) === $examId
        ));
    }

    public function saveAnswer(array $answer): array
    {
        $attemptId = (int) ($answer['exam_attempt_id'] ?? $answer['attempt_id'] ?? 0);
        $questionId = (int) ($answer['question_id'] ?? 0);

        foreach ($this->answers as $index => $existing) {
            if ((int) ($existing['exam_attempt_id'] ?? $existing['attempt_id'] ?? 0) === $attemptId && (int) ($existing['question_id'] ?? 0) === $questionId) {
                $this->answers[$index] = $answer;
                $answer['id'] = (int) ($this->answers[$index]['id'] ?? 0);
                return $answer;
            }
        }

        $answer['id'] = $this->nextAnswerId++;
        $answer['exam_attempt_id'] = $attemptId;
        $this->answers[] = $answer;

        return $answer;
    }

    public function findAnswers(int $attemptId): array
    {
        return array_values(array_filter($this->answers, static fn (array $answer): bool =>
            (int) ($answer['exam_attempt_id'] ?? $answer['attempt_id'] ?? 0) === $attemptId
        ));
    }

    public function finalize(int $attemptId, array $result): ?array
    {
        if (!isset($this->attempts[$attemptId]) || ($this->attempts[$attemptId]['status'] ?? '') !== 'in_progress') {
            return null;
        }

        $this->attempts[$attemptId] = array_merge($this->attempts[$attemptId], [
            'status' => !empty($result['status']) ? $result['status'] : 'submitted',
            'score' => (float) ($result['score'] ?? 0),
            'percentage' => (float) ($result['percentage'] ?? 0),
            'passed' => !empty($result['passed']) ? 1 : 0,
            'submitted_at' => $result['submitted_at'] ?? date('Y-m-d H:i:s'),
        ]);

        return $this->attempts[$attemptId];
    }
}
