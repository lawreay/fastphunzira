<?php

namespace App\Repositories;

final class InMemoryExamRepository implements ExamRepositoryInterface
{
    private array $exams = [];
    private array $examQuestions = [];
    private int $nextExamQuestionId = 1;

    public function create(array $exam): array
    {
        $exam['id'] = count($this->exams) + 1;
        $exam['status'] = $exam['status'] ?? 'draft';
        $exam['total_questions'] = 0;
        $this->exams[$exam['id']] = $exam;

        return $exam;
    }

    public function findById(int $id): ?array
    {
        return $this->exams[$id] ?? null;
    }

    public function findByCourse(int $courseId): array
    {
        return array_values(array_filter($this->exams, static fn (array $exam): bool => (int) ($exam['course_id'] ?? 0) === $courseId));
    }

    public function updateStatus(int $id, string $status): ?array
    {
        if (!isset($this->exams[$id])) {
            return null;
        }

        $this->exams[$id]['status'] = $status;

        return $this->exams[$id];
    }

    public function addQuestion(int $examId, int $questionId, float $marks, int $sortOrder): array
    {
        $record = [
            'id' => $this->nextExamQuestionId++,
            'exam_id' => $examId,
            'question_id' => $questionId,
            'marks' => (float) $marks,
            'sort_order' => $sortOrder,
        ];

        $this->examQuestions[$examId][] = $record;
        $this->exams[$examId]['total_questions'] = count($this->examQuestions[$examId]);

        return $record;
    }

    public function findQuestions(int $examId): array
    {
        $questions = $this->examQuestions[$examId] ?? [];
        usort($questions, static fn (array $a, array $b): int => (int) $a['sort_order'] <=> (int) $b['sort_order']);

        return $questions;
    }
}
