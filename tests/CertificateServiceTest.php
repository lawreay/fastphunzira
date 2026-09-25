<?php

namespace App\Tests;

use App\Core\Auth;
use App\Repositories\InMemoryAuditLogRepository;
use App\Repositories\InMemoryCertificateRepository;
use App\Repositories\InMemoryCourseRepository;
use App\Repositories\InMemoryEnrollmentRepository;
use App\Repositories\InMemoryExamAttemptRepository;
use App\Repositories\InMemoryExamRepository;
use App\Services\AuditLogService;
use App\Services\CertificateService;
use PHPUnit\Framework\TestCase;

final class CertificateServiceTest extends TestCase
{
    private InMemoryCourseRepository $courseRepository;
    private InMemoryEnrollmentRepository $enrollmentRepository;
    private InMemoryExamRepository $examRepository;
    private InMemoryExamAttemptRepository $attemptRepository;
    private InMemoryCertificateRepository $certificateRepository;
    private InMemoryAuditLogRepository $auditLogRepository;
    private CertificateService $service;

    protected function setUp(): void
    {
        $_SESSION = [];
        Auth::logout();

        $this->courseRepository = new InMemoryCourseRepository();
        $this->courseRepository->create([
            'title' => 'PHP Foundations',
            'slug' => 'php-foundations',
            'description' => 'Published course',
            'status' => 'published',
            'created_by' => 10,
        ]);

        $this->enrollmentRepository = new InMemoryEnrollmentRepository();
        $this->enrollmentRepository->create([
            'student_id' => 20,
            'course_id' => 1,
            'enrolled_at' => date('Y-m-d H:i:s'),
        ]);

        $this->examRepository = new InMemoryExamRepository();
        $this->attemptRepository = new InMemoryExamAttemptRepository();
        $this->certificateRepository = new InMemoryCertificateRepository();
        $this->auditLogRepository = new InMemoryAuditLogRepository();

        $this->service = new CertificateService(
            $this->courseRepository,
            $this->enrollmentRepository,
            $this->examRepository,
            $this->attemptRepository,
            $this->certificateRepository,
            new AuditLogService($this->auditLogRepository)
        );
    }

    public function testStudentCanReceiveAndVerifyACertificateForEligibleExamResult(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $exam = $this->examRepository->create([
            'course_id' => 1,
            'title' => 'Final assessment',
            'description' => 'Certification exam',
            'time_limit' => 45,
            'passing_score' => 70,
            'attempts_allowed' => 1,
            'status' => 'published',
            'created_by' => 10,
        ]);

        $attempt = $this->attemptRepository->create([
            'exam_id' => (int) $exam['id'],
            'user_id' => 20,
            'status' => 'submitted',
            'score' => 88,
            'percentage' => 88,
            'passed' => 1,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'full_name' => 'Jane Doe', 'role' => 'student']);
        $issued = $this->service->issueCertificate(20, 1, (int) $exam['id'], (int) $attempt['id']);

        $this->assertTrue($issued['success']);
        $this->assertMatchesRegularExpression('/^FP-\d{4}-\d{6}$/', $issued['data']['certificate_number']);
        $this->assertNotSame('', $issued['data']['verification_code']);

        $logs = $this->auditLogRepository->findRecent(10);
        $this->assertCount(1, $logs);
        $this->assertSame('certificate_issued', $logs[0]['action']);
        $this->assertSame((int) $issued['data']['id'], (int) $logs[0]['entity_id']);

        $verified = $this->service->verifyCertificate($issued['data']['certificate_number'], $issued['data']['verification_code']);

        $this->assertNotNull($verified);
        $this->assertSame('active', $verified['status']);
        $this->assertSame('PHP Foundations', $verified['course_name']);
        $this->assertSame('Jane Doe', $verified['student_name']);

        $duplicate = $this->service->issueCertificate(20, 1, (int) $exam['id'], (int) $attempt['id']);
        $this->assertFalse($duplicate['success']);
        $this->assertSame('already_issued', $duplicate['code']);
    }

    public function testInProgressAttemptCannotGenerateCertificate(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $exam = $this->examRepository->create([
            'course_id' => 1,
            'title' => 'Unfinalized attempt',
            'description' => 'No certificate until submitted',
            'time_limit' => 30,
            'passing_score' => 70,
            'attempts_allowed' => 1,
            'status' => 'published',
            'created_by' => 10,
        ]);

        $attempt = $this->attemptRepository->create([
            'exam_id' => (int) $exam['id'],
            'user_id' => 20,
            'status' => 'in_progress',
            'score' => 90,
            'percentage' => 90,
            'passed' => 1,
        ]);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $result = $this->service->issueCertificate(20, 1, (int) $exam['id'], (int) $attempt['id']);

        $this->assertFalse($result['success']);
        $this->assertSame('ineligible', $result['code']);
        $this->assertSame([], $this->certificateRepository->findAll());
    }

    public function testIneligibleAttemptsCannotGenerateCertificates(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $exam = $this->examRepository->create([
            'course_id' => 1,
            'title' => 'Failed attempt',
            'description' => 'No certificate',
            'time_limit' => 30,
            'passing_score' => 70,
            'attempts_allowed' => 1,
            'status' => 'published',
            'created_by' => 10,
        ]);

        $attempt = $this->attemptRepository->create([
            'exam_id' => (int) $exam['id'],
            'user_id' => 20,
            'status' => 'submitted',
            'score' => 55,
            'percentage' => 55,
            'passed' => 0,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $result = $this->service->issueCertificate(20, 1, (int) $exam['id'], (int) $attempt['id']);

        $this->assertFalse($result['success']);
        $this->assertSame('ineligible', $result['code']);
    }

    public function testAdminCanReviewAndRevokeCertificates(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $exam = $this->examRepository->create([
            'course_id' => 1,
            'title' => 'Admin review exam',
            'description' => 'Review flow',
            'time_limit' => 45,
            'passing_score' => 70,
            'attempts_allowed' => 1,
            'status' => 'published',
            'created_by' => 10,
        ]);

        $attempt = $this->attemptRepository->create([
            'exam_id' => (int) $exam['id'],
            'user_id' => 20,
            'status' => 'submitted',
            'score' => 90,
            'percentage' => 90,
            'passed' => 1,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'full_name' => 'Jane Doe', 'role' => 'student']);
        $issued = $this->service->issueCertificate(20, 1, (int) $exam['id'], (int) $attempt['id']);

        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $all = $this->service->getCertificatesForAdmin();
        $this->assertCount(1, $all);

        $revoked = $this->service->updateCertificateStatus(10, (int) $issued['data']['id'], 'revoked');
        $this->assertTrue($revoked['success']);
        $this->assertSame('revoked', $revoked['data']['status']);

        $logs = $this->auditLogRepository->findRecent(10);
        $this->assertCount(2, $logs);
        $this->assertSame('certificate_status_changed', $logs[0]['action']);
        $this->assertSame(['status' => 'active'], $logs[0]['old_values']);
        $this->assertSame(['status' => 'revoked'], $logs[0]['new_values']);

        $verified = $this->service->verifyCertificate($issued['data']['certificate_number'], $issued['data']['verification_code']);
        $this->assertNull($verified);
    }

    public function testStudentCannotChangeCertificateStatus(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $result = $this->service->updateCertificateStatus(20, 1, 'revoked');

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
    }

    public function testAdminCannotChangeCertificateStatusForAnotherActor(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $result = $this->service->updateCertificateStatus(99, 1, 'revoked');

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
    }

    public function testStudentCannotIssueCertificateForAnotherStudent(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $result = $this->service->issueCertificate(30, 1, 1, 1);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
    }

    public function testCertificateCannotUseAnExamFromAnotherCourse(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $this->courseRepository->create([
            'title' => 'JavaScript Foundations',
            'slug' => 'javascript-foundations',
            'description' => 'Second published course',
            'status' => 'published',
            'created_by' => 10,
        ]);

        $exam = $this->examRepository->create([
            'course_id' => 2,
            'title' => 'JavaScript final',
            'description' => 'Second course exam',
            'time_limit' => 45,
            'passing_score' => 70,
            'attempts_allowed' => 1,
            'status' => 'published',
            'created_by' => 10,
        ]);

        $attempt = $this->attemptRepository->create([
            'exam_id' => (int) $exam['id'],
            'user_id' => 20,
            'status' => 'submitted',
            'score' => 95,
            'percentage' => 95,
            'passed' => 1,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'full_name' => 'Jane Doe', 'role' => 'student']);

        $result = $this->service->issueCertificate(
            20,
            1,
            (int) $exam['id'],
            (int) $attempt['id']
        );

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
        $this->assertSame([], $this->certificateRepository->findAll());
    }

}
