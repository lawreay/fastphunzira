<?php

namespace App\Repositories;

final class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    private array $enrollments = [];

    public function create(array $enrollment): array
    {
        $id = count($this->enrollments) + 1;
        $enrollment['id'] = $id;
        $this->enrollments[$id] = $enrollment;

        return $enrollment;
    }

    public function findByStudentAndCourse(int $studentId, int $courseId): ?array
    {
        foreach ($this->enrollments as $enrollment) {
            if ((int) ($enrollment['student_id'] ?? 0) === $studentId && (int) ($enrollment['course_id'] ?? 0) === $courseId) {
                return $enrollment;
            }
        }

        return null;
    }

    public function findByStudent(int $studentId): array
    {
        return array_values(array_filter($this->enrollments, static fn (array $enrollment) => (int) ($enrollment['student_id'] ?? 0) === $studentId));
    }
}
