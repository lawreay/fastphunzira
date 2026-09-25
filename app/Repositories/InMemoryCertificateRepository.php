<?php

namespace App\Repositories;

final class InMemoryCertificateRepository implements CertificateRepositoryInterface
{
    private array $certificates = [];
    private int $nextId = 1;

    public function create(array $certificate): array
    {
        $certificate['id'] = $this->nextId++;
        $certificate['status'] = $certificate['status'] ?? 'active';
        $certificate['issued_at'] = $certificate['issued_at'] ?? date('Y-m-d H:i:s');
        $this->certificates[$certificate['id']] = $certificate;

        return $certificate;
    }

    public function findById(int $id): ?array
    {
        return $this->certificates[$id] ?? null;
    }

    public function findByNumber(string $certificateNumber): ?array
    {
        foreach ($this->certificates as $certificate) {
            if (strtoupper((string) ($certificate['certificate_number'] ?? '')) === strtoupper($certificateNumber)) {
                return $certificate;
            }
        }

        return null;
    }

    public function findByUser(int $userId): array
    {
        return array_values(array_filter(
            $this->certificates,
            static fn (array $certificate): bool => (int) ($certificate['user_id'] ?? 0) === $userId
        ));
    }

    public function findByStudentAndCourse(int $studentId, int $courseId): ?array
    {
        $matches = array_values(array_filter(
            $this->certificates,
            static fn (array $certificate): bool =>
                (int) ($certificate['user_id'] ?? 0) === $studentId &&
                (int) ($certificate['course_id'] ?? 0) === $courseId
        ));

        return $matches !== [] ? $matches[0] : null;
    }

    public function countAll(): int
    {
        return count($this->certificates);
    }
}
