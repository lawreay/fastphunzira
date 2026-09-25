<?php

namespace App\Tests;

use App\Repositories\InMemoryCertificateVerificationAttemptRepository;
use App\Services\CertificateVerificationRateLimitService;
use PHPUnit\Framework\TestCase;

final class CertificateVerificationRateLimitServiceTest extends TestCase
{
    public function testIpIsThrottledAfterConfiguredNumberOfAttempts(): void
    {
        $repository = new InMemoryCertificateVerificationAttemptRepository();
        $service = new CertificateVerificationRateLimitService($repository, [
            'window_seconds' => 900,
            'max_attempts_per_ip' => 3,
        ]);

        $service->recordAttempt('203.0.113.10', 'FP-2026-000001', false);
        $service->recordAttempt('203.0.113.10', 'FP-2026-000002', false);
        $this->assertFalse($service->isThrottled('203.0.113.10'));

        $service->recordAttempt('203.0.113.10', 'FP-2026-000003', true);

        $this->assertTrue($service->isThrottled('203.0.113.10'));
    }

    public function testDifferentIpsHaveIndependentLimits(): void
    {
        $repository = new InMemoryCertificateVerificationAttemptRepository();
        $service = new CertificateVerificationRateLimitService($repository, [
            'window_seconds' => 900,
            'max_attempts_per_ip' => 2,
        ]);

        $service->recordAttempt('203.0.113.10', 'FP-2026-000001', false);
        $service->recordAttempt('203.0.113.10', 'FP-2026-000002', false);

        $this->assertTrue($service->isThrottled('203.0.113.10'));
        $this->assertFalse($service->isThrottled('203.0.113.11'));
    }

    public function testEmptyIpDoesNotCreateThrottle(): void
    {
        $repository = new InMemoryCertificateVerificationAttemptRepository();
        $service = new CertificateVerificationRateLimitService($repository, [
            'window_seconds' => 900,
            'max_attempts_per_ip' => 1,
        ]);

        $service->recordAttempt('', 'FP-2026-000001', false);

        $this->assertFalse($service->isThrottled(''));
    }
}
