<?php

namespace App\Repositories;

use PDO;

final class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $enrollment): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO enrollments (user_id, course_id, status, enrolled_at)
             VALUES (:user_id, :course_id, :status, :enrolled_at)'
        );

        $statement->execute([
            ':user_id' => (int) ($enrollment['user_id'] ?? $enrollment['student_id'] ?? 0),
            ':course_id' => (int) ($enrollment['course_id'] ?? 0),
            ':status' => $enrollment['status'] ?? 'active',
            ':enrolled_at' => $enrollment['enrolled_at'] ?? date('Y-m-d H:i:s'),
        ]);

        $enrollment['id'] = (int) $this->pdo->lastInsertId();
        $enrollment['user_id'] = (int) ($enrollment['user_id'] ?? $enrollment['student_id'] ?? 0);
        $enrollment['status'] = $enrollment['status'] ?? 'active';

        return $enrollment;
    }

    public function findByStudentAndCourse(int $studentId, int $courseId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM enrollments
             WHERE user_id = :user_id AND course_id = :course_id
             LIMIT 1'
        );

        $statement->execute([
            ':user_id' => $studentId,
            ':course_id' => $courseId,
        ]);

        $enrollment = $statement->fetch();

        return $enrollment === false ? null : $enrollment;
    }

    public function findByStudent(int $studentId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM enrollments
             WHERE user_id = :user_id
             ORDER BY enrolled_at DESC, id DESC'
        );

        $statement->execute([':user_id' => $studentId]);

        return $statement->fetchAll() ?: [];
    }
}
