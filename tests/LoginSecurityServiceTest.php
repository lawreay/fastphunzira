<?php

namespace App\Tests;

use App\Repositories\InMemoryLoginHistoryRepository;
use App\Services\LoginSecurityService;
use PHPUnit\Framework\TestCase;

final class LoginSecurityServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [];
    }

    public function testAccountThresholdThrottlesRepeatedFailures(): void
    {
        $repository = new InMemoryLoginHistoryRepository();
        $service = new LoginSecurityService($repository, [
            'window_seconds' => 900,
            'max_failures_per_account' => 3,
            'max_failures_per_ip' => 20,
        ]);

        $_SERVER['REMOTE_ADDR'] = '192.0.2.10';

        $service->recordFailure('student@example.com');
        $service->recordFailure('student@example.com');
        $this->assertFalse($service->isThrottled('student@example.com', '192.0.2.10'));

        $service->recordFailure('student@example.com');

        $this->assertTrue($service->isThrottled('student@example.com', '192.0.2.10'));
    }

    public function testIpThresholdThrottlesDistributedAccountFailures(): void
    {
        $repository = new InMemoryLoginHistoryRepository();
        $service = new LoginSecurityService($repository, [
            'window_seconds' => 900,
            'max_failures_per_account' => 50,
            'max_failures_per_ip' => 3,
        ]);

        $_SERVER['REMOTE_ADDR'] = '192.0.2.20';

        $service->recordFailure('one@example.com');
        $service->recordFailure('two@example.com');
        $this->assertFalse($service->isThrottled('three@example.com', '192.0.2.20'));

        $service->recordFailure('three@example.com');

        $this->assertTrue($service->isThrottled('another@example.com', '192.0.2.20'));
    }

    public function testSuccessfulLoginResetsAccountFailureWindow(): void
    {
        $repository = new InMemoryLoginHistoryRepository();
        $service = new LoginSecurityService($repository, [
            'window_seconds' => 900,
            'max_failures_per_account' => 3,
            'max_failures_per_ip' => 20,
        ]);

        $_SERVER['REMOTE_ADDR'] = '192.0.2.30';

        $service->recordFailure('student@example.com');
        $service->recordFailure('student@example.com');
        $this->assertSame(
            ['account' => 2, 'ip' => 2],
            $service->failureCounts('student@example.com', '192.0.2.30')
        );

        $service->recordSuccess('student@example.com', 7);

        $this->assertSame(
            ['account' => 0, 'ip' => 0],
            $service->failureCounts('student@example.com', '192.0.2.30')
        );
        $this->assertFalse($service->isThrottled('student@example.com', '192.0.2.30'));
    }

    public function testChangingEmailDoesNotBypassIpThreshold(): void
    {
        $repository = new InMemoryLoginHistoryRepository();
        $service = new LoginSecurityService($repository, [
            'window_seconds' => 900,
            'max_failures_per_account' => 50,
            'max_failures_per_ip' => 2,
        ]);

        $_SERVER['REMOTE_ADDR'] = '192.0.2.40';

        $service->recordFailure('one@example.com');
        $service->recordFailure('two@example.com');

        $this->assertTrue($service->isThrottled('new@example.com', '192.0.2.40'));
    }

    public function testEmptyIpDoesNotCreateIpThrottle(): void
    {
        $repository = new InMemoryLoginHistoryRepository();
        $service = new LoginSecurityService($repository, [
            'window_seconds' => 900,
            'max_failures_per_account' => 2,
            'max_failures_per_ip' => 1,
        ]);

        $service->recordFailure('student@example.com');
        $this->assertFalse($service->isThrottled('other@example.com', ''));
    }
}
