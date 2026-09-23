<?php

namespace App\Repositories;

interface EnrollmentRepositoryInterface
{
    public function create(array $enrollment): array;

    public function findByStudentAndCourse(int $studentId, int $courseId): ?array;

    public function findByStudent(int $studentId): array;
}
