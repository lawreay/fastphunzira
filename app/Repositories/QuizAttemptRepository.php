<?php

namespace App\Repositories;

use PDO;

final class QuizAttemptRepository implements QuizAttemptRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $attempt): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO quiz_attempts (quiz_id, user_id, attempt_number, status, started_at)
             VALUES (:quiz_id, :user_id, :attempt_number, :status, :started_at)'
        );
        $statement->execute([
            ':quiz_id' => (int) $attempt['quiz_id'],
            ':user_id' => (int) $attempt['user_id'],
            ':attempt_number' => (int) $attempt['attempt_number'],
            ':status' => $attempt['status'] ?? 'in_progress',
            ':started_at' => $attempt['started_at'] ?? date('Y-m-d H:i:s'),
        ]);
        $attempt['id'] = (int) $this->pdo->lastInsertId();
        return $attempt;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM quiz_attempts WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $attempt = $statement->fetch();
        return $attempt === false ? null : $attempt;
    }

    public function findActiveByStudentAndQuiz(int $studentId, int $quizId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM quiz_attempts
             WHERE user_id = :user_id AND quiz_id = :quiz_id AND status = 'in_progress'
             ORDER BY id DESC LIMIT 1"
        );
        $statement->execute([':user_id' => $studentId, ':quiz_id' => $quizId]);
        $attempt = $statement->fetch();
        return $attempt === false ? null : $attempt;
    }

    public function countByStudentAndQuiz(int $studentId, int $quizId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM quiz_attempts WHERE user_id = :user_id AND quiz_id = :quiz_id'
        );
        $statement->execute([':user_id' => $studentId, ':quiz_id' => $quizId]);
        return (int) $statement->fetchColumn();
    }

    public function saveAnswer(array $answer): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO quiz_answers (attempt_id, question_id, option_id, is_correct)
             VALUES (:attempt_id, :question_id, :option_id, :is_correct)
             ON DUPLICATE KEY UPDATE option_id = VALUES(option_id), is_correct = VALUES(is_correct)'
        );
        $statement->execute([
            ':attempt_id' => (int) $answer['attempt_id'],
            ':question_id' => (int) $answer['question_id'],
            ':option_id' => (int) $answer['option_id'],
            ':is_correct' => !empty($answer['is_correct']) ? 1 : 0,
        ]);
        return $answer;
    }

    public function finalize(int $attemptId, array $result): ?array
    {
        $statement = $this->pdo->prepare(
            "UPDATE quiz_attempts
             SET status = 'submitted',
                 score = :score,
                 percentage = :percentage,
                 passed = :passed,
                 submitted_at = :submitted_at
             WHERE id = :id AND status = 'in_progress'"
        );
        $statement->execute([
            ':id' => $attemptId,
            ':score' => (int) $result['score'],
            ':percentage' => (float) $result['percentage'],
            ':passed' => !empty($result['passed']) ? 1 : 0,
            ':submitted_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->findById($attemptId);
    }

    public function findAnswers(int $attemptId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM quiz_answers WHERE attempt_id = :attempt_id ORDER BY id ASC'
        );
        $statement->execute([':attempt_id' => $attemptId]);
        return $statement->fetchAll() ?: [];
    }
}
