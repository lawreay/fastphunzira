<?php

namespace App\Repositories;

use PDO;

final class LessonProgressRepository implements LessonProgressRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createOrUpdate(array $progress): array
    {
        $userId = (int) ($progress['user_id'] ?? $progress['student_id'] ?? 0);
        $courseId = (int) ($progress['course_id'] ?? 0);
        $lessonId = (int) ($progress['lesson_id'] ?? 0);
        $completed = (int) ($progress['completed'] ?? 1);
        $completedAt = $progress['completed_at'] ?? date('Y-m-d H:i:s');

        $statement = $this->pdo->prepare(
            'INSERT INTO lesson_progress
                (user_id, course_id, lesson_id, completed, completed_at)
             VALUES
                (:user_id, :course_id, :lesson_id, :completed, :completed_at)
             ON DUPLICATE KEY UPDATE
                course_id = VALUES(course_id),
                completed = VALUES(completed),
                completed_at = VALUES(completed_at),
                updated_at = CURRENT_TIMESTAMP'
        );

        $statement->execute([
            ':user_id' => $userId,
            ':course_id' => $courseId,
            ':lesson_id' => $lessonId,
            ':completed' => $completed,
            ':completed_at' => $completedAt,
        ]);

        $result = $this->findByStudentAndLesson($userId, $lessonId);

        return $result ?? [
            'user_id' => $userId,
            'course_id' => $courseId,
            'lesson_id' => $lessonId,
            'completed' => $completed,
            'completed_at' => $completedAt,
        ];
    }

    public function findByStudentAndLesson(int $studentId, int $lessonId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM lesson_progress
             WHERE user_id = :user_id AND lesson_id = :lesson_id
             LIMIT 1'
        );

        $statement->execute([
            ':user_id' => $studentId,
            ':lesson_id' => $lessonId,
        ]);

        $progress = $statement->fetch();

        return $progress === false ? null : $progress;
    }

    public function findByStudentAndCourse(int $studentId, int $courseId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM lesson_progress
             WHERE user_id = :user_id AND course_id = :course_id
             ORDER BY lesson_id ASC'
        );

        $statement->execute([
            ':user_id' => $studentId,
            ':course_id' => $courseId,
        ]);

        return $statement->fetchAll() ?: [];
    }
}
