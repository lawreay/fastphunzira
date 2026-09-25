<?php

namespace App\Repositories;

use PDO;

final class CertificateRepository implements CertificateRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $certificate): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO certificates (
                user_id,
                course_id,
                exam_id,
                certificate_number,
                verification_code,
                student_name,
                course_name,
                score,
                issued_at,
                status,
                file_path
            ) VALUES (
                :user_id,
                :course_id,
                :exam_id,
                :certificate_number,
                :verification_code,
                :student_name,
                :course_name,
                :score,
                :issued_at,
                :status,
                :file_path
            )'
        );

        $statement->execute([
            ':user_id' => (int) ($certificate['user_id'] ?? 0),
            ':course_id' => (int) ($certificate['course_id'] ?? 0),
            ':exam_id' => (int) ($certificate['exam_id'] ?? 0),
            ':certificate_number' => (string) ($certificate['certificate_number'] ?? ''),
            ':verification_code' => (string) ($certificate['verification_code'] ?? ''),
            ':student_name' => (string) ($certificate['student_name'] ?? ''),
            ':course_name' => (string) ($certificate['course_name'] ?? ''),
            ':score' => (float) ($certificate['score'] ?? 0),
            ':issued_at' => $certificate['issued_at'] ?? date('Y-m-d H:i:s'),
            ':status' => $certificate['status'] ?? 'active',
            ':file_path' => $certificate['file_path'] ?? '',
        ]);

        $certificate['id'] = (int) $this->pdo->lastInsertId();

        return $certificate;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM certificates WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $certificate = $statement->fetch();

        return $certificate === false ? null : $certificate;
    }

    public function findByNumber(string $certificateNumber): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM certificates WHERE certificate_number = :certificate_number LIMIT 1');
        $statement->execute([':certificate_number' => $certificateNumber]);
        $certificate = $statement->fetch();

        return $certificate === false ? null : $certificate;
    }

    public function findByUser(int $userId): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM certificates WHERE user_id = :user_id ORDER BY issued_at DESC');
        $statement->execute([':user_id' => $userId]);

        return $statement->fetchAll() ?: [];
    }

    public function findByStudentAndCourse(int $studentId, int $courseId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM certificates WHERE user_id = :user_id AND course_id = :course_id ORDER BY issued_at DESC LIMIT 1');
        $statement->execute([
            ':user_id' => $studentId,
            ':course_id' => $courseId,
        ]);
        $certificate = $statement->fetch();

        return $certificate === false ? null : $certificate;
    }

    public function countAll(): int
    {
        $statement = $this->pdo->query('SELECT COUNT(*) FROM certificates');

        return (int) $statement->fetchColumn();
    }
}
