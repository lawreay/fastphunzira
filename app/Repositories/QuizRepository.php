<?php

namespace App\Repositories;

use PDO;

final class QuizRepository implements QuizRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $quiz): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO quizzes (course_id, title, description, pass_percentage, attempts_allowed, status, created_by)
             VALUES (:course_id, :title, :description, :pass_percentage, :attempts_allowed, :status, :created_by)'
        );
        $statement->execute([
            ':course_id' => (int) ($quiz['course_id'] ?? 0),
            ':title' => trim((string) ($quiz['title'] ?? '')),
            ':description' => trim((string) ($quiz['description'] ?? '')),
            ':pass_percentage' => (float) ($quiz['pass_percentage'] ?? 50),
            ':attempts_allowed' => (int) ($quiz['attempts_allowed'] ?? 1),
            ':status' => $quiz['status'] ?? 'draft',
            ':created_by' => (int) ($quiz['created_by'] ?? 0),
        ]);
        $quiz['id'] = (int) $this->pdo->lastInsertId();
        return $quiz;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM quizzes WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $quiz = $statement->fetch();
        return $quiz === false ? null : $quiz;
    }

    public function updateStatus(int $id, string $status): ?array
    {
        $statement = $this->pdo->prepare('UPDATE quizzes SET status = :status WHERE id = :id');
        $statement->execute([':status' => $status, ':id' => $id]);
        return $this->findById($id);
    }

    public function findByCourse(int $courseId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM quizzes WHERE course_id = :course_id ORDER BY id ASC'
        );
        $statement->execute([':course_id' => $courseId]);
        return $statement->fetchAll() ?: [];
    }
}
