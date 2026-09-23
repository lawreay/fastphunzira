<?php

namespace App\Repositories;

final class InMemoryQuestionRepository implements QuestionRepositoryInterface
{
    private array $questions = [];
    private array $quizQuestions = [];
    private int $nextQuestionId = 1;
    private int $nextOptionId = 1;

    public function create(array $question): array
    {
        $question['id'] = $this->nextQuestionId++;
        $question['options'] = [];
        $this->questions[$question['id']] = $question;
        return $question;
    }

    public function addOption(int $questionId, array $option): array
    {
        $option['id'] = $this->nextOptionId++;
        $option['question_id'] = $questionId;
        $this->questions[$questionId]['options'][] = $option;
        return $option;
    }

    public function attachToQuiz(int $quizId, int $questionId, int $sortOrder = 0): void
    {
        $this->quizQuestions[$quizId][] = ['question_id' => $questionId, 'sort_order' => $sortOrder];
    }

    public function findById(int $id): ?array
    {
        return $this->questions[$id] ?? null;
    }

    public function findByQuiz(int $quizId): array
    {
        $links = $this->quizQuestions[$quizId] ?? [];
        usort($links, fn(array $a, array $b): int => $a['sort_order'] <=> $b['sort_order']);
        $result = [];
        foreach ($links as $link) {
            $question = $this->findById((int) $link['question_id']);
            if ($question !== null) {
                $result[] = $question;
            }
        }
        return $result;
    }
}
