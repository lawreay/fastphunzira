<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\CertificateRepositoryInterface;
use App\Repositories\CourseRepositoryInterface;
use App\Repositories\EnrollmentRepositoryInterface;
use App\Repositories\ExamAttemptRepositoryInterface;
use App\Repositories\ExamRepositoryInterface;

final class CertificateService
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        private EnrollmentRepositoryInterface $enrollmentRepository,
        private ExamRepositoryInterface $examRepository,
        private ExamAttemptRepositoryInterface $attemptRepository,
        private CertificateRepositoryInterface $certificateRepository
    ) {
    }

    public function issueCertificate(int $studentId, int $courseId, int $examId, int $attemptId): array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'You must be logged in as the student to receive a certificate.'];
        }

        $course = $this->courseRepository->findById($courseId);
        if ($course === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Course not found.'];
        }

        if ($this->enrollmentRepository->findByStudentAndCourse($studentId, $courseId) === null) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'You must be enrolled in the course before earning a certificate.'];
        }

        $exam = $this->examRepository->findById($examId);
        if ($exam === null || strtolower((string) ($exam['status'] ?? 'draft')) !== 'published') {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Exam not found or not published.'];
        }

        $attempt = $this->attemptRepository->findById($attemptId);
        if ($attempt === null || (int) ($attempt['user_id'] ?? 0) !== $studentId || (int) ($attempt['exam_id'] ?? 0) !== $examId) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Attempt not found for this student.'];
        }

        if ($this->certificateRepository->findByStudentAndCourse($studentId, $courseId) !== null) {
            return ['success' => false, 'code' => 'already_issued', 'message' => 'A certificate for this course has already been issued.'];
        }

        if (!empty($attempt['passed']) || (int) ($attempt['passed'] ?? 0) === 1) {
            $score = (float) ($attempt['percentage'] ?? 0);
        } else {
            return ['success' => false, 'code' => 'ineligible', 'message' => 'This exam result does not meet the certificate eligibility rules.'];
        }

        $certificateNumber = $this->generateCertificateNumber();
        $verificationCode = bin2hex(random_bytes(16));
        $studentName = trim((string) (Auth::user()['full_name'] ?? 'Learner'));

        $certificate = $this->certificateRepository->create([
            'user_id' => $studentId,
            'course_id' => $courseId,
            'exam_id' => $examId,
            'certificate_number' => $certificateNumber,
            'verification_code' => $verificationCode,
            'student_name' => $studentName,
            'course_name' => (string) ($course['title'] ?? ''),
            'score' => $score,
            'issued_at' => date('Y-m-d H:i:s'),
            'status' => 'active',
            'file_path' => '',
        ]);

        return ['success' => true, 'message' => 'Certificate issued successfully.', 'data' => $certificate];
    }

    public function verifyCertificate(string $certificateNumber, string $verificationCode): ?array
    {
        $certificate = $this->certificateRepository->findByNumber($certificateNumber);
        if ($certificate === null) {
            return null;
        }

        if (strtolower((string) ($certificate['status'] ?? 'active')) !== 'active') {
            return null;
        }

        if (!hash_equals((string) ($certificate['verification_code'] ?? ''), trim((string) $verificationCode))) {
            return null;
        }

        return [
            'certificate_number' => (string) ($certificate['certificate_number'] ?? ''),
            'student_name' => (string) ($certificate['student_name'] ?? ''),
            'course_name' => (string) ($certificate['course_name'] ?? ''),
            'score' => (float) ($certificate['score'] ?? 0),
            'issued_at' => (string) ($certificate['issued_at'] ?? ''),
            'status' => strtolower((string) ($certificate['status'] ?? 'active')),
        ];
    }

    public function getStudentCertificates(int $studentId): array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return [];
        }

        return $this->certificateRepository->findByUser($studentId);
    }

    public function getCertificatesForAdmin(): array
    {
        if (!Auth::userCan('courses.manage')) {
            return [];
        }

        return $this->certificateRepository->findAll();
    }

    public function updateCertificateStatus(int $adminId, int $certificateId, string $status): array
    {
        if (!Auth::userCan('courses.manage') || (int) Auth::userId() !== $adminId) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can manage certificate status.'];
        }

        $certificate = $this->certificateRepository->findById($certificateId);
        if ($certificate === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Certificate not found.'];
        }

        $normalizedStatus = strtolower(trim($status));
        $allowedStatuses = ['active', 'revoked', 'expired', 'invalid'];
        if (!in_array($normalizedStatus, $allowedStatuses, true)) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'Certificate status is invalid.'];
        }

        $certificate['status'] = $normalizedStatus;
        $this->certificateRepository->updateStatus($certificateId, $normalizedStatus);

        return ['success' => true, 'message' => 'Certificate status updated.', 'data' => $certificate];
    }

    private function generateCertificateNumber(): string
    {
        $year = date('Y');
        $count = $this->certificateRepository->countAll() + 1;

        return 'FP-' . $year . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }
}
