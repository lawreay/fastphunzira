<?php

namespace App\Tests;

use App\Core\Auth;
use App\Repositories\InMemoryCertificateRepository;
use App\Repositories\InMemoryCourseRepository;
use App\Repositories\InMemoryEnrollmentRepository;
use App\Repositories\InMemoryExamAttemptRepository;
use App\Repositories\InMemoryExamRepository;
use App\Services\CertificateService;
use PHPUnit\Framework\TestCase;

final class CertificateServiceTest extends TestCase
{
    private InMemoryCourseRepository $courseRepository;
    private InMemoryEnrollmentRepository $enrollmentRepository;
    private InMemoryExamRepository $examRepository;
    private InMemoryExamAttemptRepository $attemptRepository;
    private InMemoryCertificateRepository $certificateRepository;
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

        $this->service = new CertificateService(
            $this->courseRepository,
            $this->enrollmentRepository,
            $this->examRepository,
            $this->attemptRepository,
            $this->certificateRepository
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

        $verified = $this->service->verifyCertificate($issued['data']['certificate_number'], $issued['data']['verification_code']);

        $this->assertNotNull($verified);
        $this->assertSame('active', $verified['status']);
        $this->assertSame('PHP Foundations', $verified['course_name']);
        $this->assertSame('Jane Doe', $verified['student_name']);

        $duplicate = $this->service->issueCertificate(20, 1, (int) $exam['id'], (int) $attempt['id']);
        $this->assertFalse($duplicate['success']);
        $this->assertSame('already_issued', $duplicate['code']);
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
}
