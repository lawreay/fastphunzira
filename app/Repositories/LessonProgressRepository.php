<?php

namespace App\Repositories;

final class LessonProgressRepository implements LessonProgressRepositoryInterface
{
    private array $progress = [];

    public function createOrUpdate(array $progress): array
    {
        $key = (int) ($progress['student_id'] ?? 0) . ':' . (int) ($progress['lesson_id'] ?? 0);
        $this->progress[$key] = $progress;

        return $progress;
    }

    public function findByStudentAndLesson(int $studentId, int $lessonId): ?array
    {
        $key = $studentId . ':' . $lessonId;

        return $this->progress[$key] ?? null;
    }

    public function findByStudentAndCourse(int $studentId, int $courseId): array
    {
        return array_values(array_filter($this->progress, static fn (array $entry) => (int) ($entry['student_id'] ?? 0) === $studentId && (int) ($entry['course_id'] ?? 0) === $courseId));
    }
}
