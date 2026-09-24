<?php

namespace App\Repositories;

use PDO;

final class ExamAttemptRepository implements ExamAttemptRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $attempt): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO exam_attempts (exam_id, user_id, started_at, expires_at, submitted_at, score, percentage, passed, status)
             VALUES (:exam_id, :user_id, :started_at, :expires_at, :submitted_at, :score, :percentage, :passed, :status)'
        );

        $statement->execute([
            ':exam_id' => (int) ($attempt['exam_id'] ?? 0),
            ':user_id' => (int) ($attempt['user_id'] ?? 0),
            ':started_at' => $attempt['started_at'] ?? date('Y-m-d H:i:s'),
            ':expires_at' => $attempt['expires_at'] ?? null,
            ':submitted_at' => $attempt['submitted_at'] ?? null,
            ':score' => (float) ($attempt['score'] ?? 0),
            ':percentage' => (float) ($attempt['percentage'] ?? 0),
            ':passed' => !empty($attempt['passed']) ? 1 : 0,
            ':status' => $attempt['status'] ?? 'in_progress',
        ]);

        $attempt['id'] = (int) $this->pdo->lastInsertId();

        return $attempt;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM exam_attempts WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $attempt = $statement->fetch();

        return $attempt === false ? null : $attempt;
    }

    public function findActiveByStudentAndExam(int $studentId, int $examId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM exam_attempts
             WHERE user_id = :user_id AND exam_id = :exam_id AND status = 'in_progress'
             ORDER BY id DESC LIMIT 1"
        );
        $statement->execute([':user_id' => $studentId, ':exam_id' => $examId]);
        $attempt = $statement->fetch();

        return $attempt === false ? null : $attempt;
    }

    public function countByStudentAndExam(int $studentId, int $examId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM exam_attempts WHERE user_id = :user_id AND exam_id = :exam_id'
        );
        $statement->execute([':user_id' => $studentId, ':exam_id' => $examId]);

        return (int) $statement->fetchColumn();
    }

    public function saveAnswer(array $answer): array
    {
        $attemptId = (int) ($answer['exam_attempt_id'] ?? $answer['attempt_id'] ?? 0);
        $questionId = (int) ($answer['question_id'] ?? 0);
        $selected = $answer['selected_option_id'] ?? null;
        $text = $answer['answer_text'] ?? null;

        $existing = $this->pdo->prepare(
            'SELECT id FROM exam_answers WHERE exam_attempt_id = :attempt_id AND question_id = :question_id LIMIT 1'
        );
        $existing->execute([':attempt_id' => $attemptId, ':question_id' => $questionId]);
        $row = $existing->fetch();

        if ($row === false) {
            $statement = $this->pdo->prepare(
                'INSERT INTO exam_answers (exam_attempt_id, question_id, selected_option_id, answer_text, is_correct)
                 VALUES (:exam_attempt_id, :question_id, :selected_option_id, :answer_text, :is_correct)'
            );
            $statement->execute([
                ':exam_attempt_id' => $attemptId,
                ':question_id' => $questionId,
                ':selected_option_id' => $selected !== null && $selected !== '' ? (int) $selected : null,
                ':answer_text' => $text !== null && $text !== '' ? (string) $text : null,
                ':is_correct' => !empty($answer['is_correct']) ? 1 : 0,
            ]);
            $answer['id'] = (int) $this->pdo->lastInsertId();
        } else {
            $statement = $this->pdo->prepare(
                'UPDATE exam_answers
                 SET selected_option_id = :selected_option_id,
                     answer_text = :answer_text,
                     is_correct = :is_correct,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $statement->execute([
                ':selected_option_id' => $selected !== null && $selected !== '' ? (int) $selected : null,
                ':answer_text' => $text !== null && $text !== '' ? (string) $text : null,
                ':is_correct' => !empty($answer['is_correct']) ? 1 : 0,
                ':id' => (int) $row['id'],
            ]);
            $answer['id'] = (int) $row['id'];
        }

        return $answer;
    }

    public function findAnswers(int $attemptId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM exam_answers WHERE exam_attempt_id = :exam_attempt_id ORDER BY id ASC'
        );
        $statement->execute([':exam_attempt_id' => $attemptId]);

        return $statement->fetchAll() ?: [];
    }

    public function finalize(int $attemptId, array $result): ?array
    {
        $status = !empty($result['status']) ? $result['status'] : 'submitted';
        $statement = $this->pdo->prepare(
            "UPDATE exam_attempts
             SET status = :status,
                 score = :score,
                 percentage = :percentage,
                 passed = :passed,
                 submitted_at = :submitted_at
             WHERE id = :id AND status = 'in_progress'"
        );
        $statement->execute([
            ':status' => $status,
            ':score' => (float) ($result['score'] ?? 0),
            ':percentage' => (float) ($result['percentage'] ?? 0),
            ':passed' => !empty($result['passed']) ? 1 : 0,
            ':submitted_at' => $result['submitted_at'] ?? date('Y-m-d H:i:s'),
            ':id' => $attemptId,
        ]);

        return $this->findById($attemptId);
    }
}
