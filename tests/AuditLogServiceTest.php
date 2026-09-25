<?php

namespace App\Tests;

use App\Core\Auth;
use App\Repositories\InMemoryAuditLogRepository;
use App\Services\AuditLogService;
use PHPUnit\Framework\TestCase;

final class AuditLogServiceTest extends TestCase
{
    private InMemoryAuditLogRepository $repository;
    private AuditLogService $service;

    protected function setUp(): void
    {
        $_SESSION = [];
        Auth::logout();
        $this->repository = new InMemoryAuditLogRepository();
        $this->service = new AuditLogService($this->repository);
    }

    public function testAdminCanReviewRecentAuditLogs(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $this->service->record([
            'user_id' => 10,
            'action' => 'certificate_issued',
            'entity_type' => 'certificate',
            'entity_id' => 1,
            'details' => 'Certificate issued to student',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->service->record([
            'user_id' => 10,
            'action' => 'exam_published',
            'entity_type' => 'exam',
            'entity_id' => 3,
            'details' => 'Exam published',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $logs = $this->service->getRecentForAdmin();

        $this->assertCount(2, $logs);
        $this->assertSame('exam_published', $logs[0]['action']);
    }

    public function testNonAdminCannotAccessAuditLogs(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $logs = $this->service->getRecentForAdmin();

        $this->assertSame([], $logs);
    }
}
