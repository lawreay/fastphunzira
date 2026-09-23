<?php

namespace App\Repositories;

interface LessonProgressRepositoryInterface
{
    public function createOrUpdate(array $progress): array;

    public function findByStudentAndLesson(int $studentId, int $lessonId): ?array;

    public function findByStudentAndCourse(int $studentId, int $courseId): array;
}
