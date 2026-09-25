<?php

namespace App\Repositories;

interface CertificateRepositoryInterface
{
    public function create(array $certificate): array;

    public function findById(int $id): ?array;

    public function findAll(): array;

    public function findByNumber(string $certificateNumber): ?array;

    public function findByUser(int $userId): array;

    public function findByStudentAndCourse(int $studentId, int $courseId): ?array;

    public function updateStatus(int $id, string $status): ?array;

    public function countAll(): int;
}
