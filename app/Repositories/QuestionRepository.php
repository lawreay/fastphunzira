<?php

namespace App\Repositories;

use PDO;

final class QuestionRepository implements QuestionRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $question): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO questions (question_text, question_type, created_by)
             VALUES (:question_text, :question_type, :created_by)'
        );
        $statement->execute([
            ':question_text' => trim((string) ($question['question_text'] ?? '')),
            ':question_type' => $question['question_type'] ?? 'multiple_choice',
            ':created_by' => (int) ($question['created_by'] ?? 0),
        ]);
        $question['id'] = (int) $this->pdo->lastInsertId();
        return $question;
    }

    public function addOption(int $questionId, array $option): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO question_options (question_id, option_text, is_correct, sort_order)
             VALUES (:question_id, :option_text, :is_correct, :sort_order)'
        );
        $statement->execute([
            ':question_id' => $questionId,
            ':option_text' => trim((string) ($option['option_text'] ?? '')),
            ':is_correct' => !empty($option['is_correct']) ? 1 : 0,
            ':sort_order' => (int) ($option['sort_order'] ?? 0),
        ]);
        $option['id'] = (int) $this->pdo->lastInsertId();
        $option['question_id'] = $questionId;
        return $option;
    }

    public function attachToQuiz(int $quizId, int $questionId, int $sortOrder = 0): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO quiz_questions (quiz_id, question_id, sort_order)
             VALUES (:quiz_id, :question_id, :sort_order)'
        );
        $statement->execute([
            ':quiz_id' => $quizId,
            ':question_id' => $questionId,
            ':sort_order' => $sortOrder,
        ]);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM questions WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $question = $statement->fetch();
        if ($question === false) {
            return null;
        }

        $options = $this->pdo->prepare(
            'SELECT * FROM question_options WHERE question_id = :question_id ORDER BY sort_order ASC, id ASC'
        );
        $options->execute([':question_id' => $id]);
        $question['options'] = $options->fetchAll() ?: [];
        return $question;
    }

    public function findByQuiz(int $quizId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT q.*, qq.sort_order AS quiz_sort_order
             FROM questions q
             INNER JOIN quiz_questions qq ON qq.question_id = q.id
             WHERE qq.quiz_id = :quiz_id
             ORDER BY qq.sort_order ASC, q.id ASC'
        );
        $statement->execute([':quiz_id' => $quizId]);
        $questions = $statement->fetchAll() ?: [];

        foreach ($questions as &$question) {
            $options = $this->pdo->prepare(
                'SELECT * FROM question_options WHERE question_id = :question_id ORDER BY sort_order ASC, id ASC'
            );
            $options->execute([':question_id' => (int) $question['id']]);
            $question['options'] = $options->fetchAll() ?: [];
        }
        unset($question);

        return $questions;
    }
}
