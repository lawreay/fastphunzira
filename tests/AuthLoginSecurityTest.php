<?php

namespace App\Tests;

use App\Core\Auth;
use App\Repositories\InMemoryAuditLogRepository;
use App\Repositories\InMemoryLoginHistoryRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuditLogService;
use App\Services\AuthService;
use App\Services\LoginSecurityService;
use PHPUnit\Framework\TestCase;

final class AuthLoginSecurityTest extends TestCase
{
    private InMemoryLoginHistoryRepository $loginHistory;
    private InMemoryAuditLogRepository $auditLogs;
    private AuthService $authService;

    protected function setUp(): void
    {
        $_SESSION = [];
        $_SERVER = [];
        Auth::logout();

        $this->loginHistory = new InMemoryLoginHistoryRepository();
        $this->auditLogs = new InMemoryAuditLogRepository();

        $users = [
            'student@example.com' => [
                'id' => 7,
                'full_name' => 'Jane Student',
                'email' => 'student@example.com',
                'password_hash' => password_hash('StrongPass123!', PASSWORD_DEFAULT),
                'status' => 'active',
                'role' => 'student',
            ],
            'disabled@example.com' => [
                'id' => 8,
                'full_name' => 'Disabled Student',
                'email' => 'disabled@example.com',
                'password_hash' => password_hash('StrongPass123!', PASSWORD_DEFAULT),
                'status' => 'disabled',
                'role' => 'student',
            ],
        ];

        $repository = new class($users) implements UserRepositoryInterface {
            public function __construct(private array $users)
            {
            }

            public function create(array $user): array
            {
                $this->users[$user['email']] = $user;

                return $user;
            }

            public function findByEmail(string $email): ?array
            {
                return $this->users[strtolower(trim($email))] ?? null;
            }

            public function findById(int $id): ?array
            {
                foreach ($this->users as $user) {
                    if ((int) $user['id'] === $id) {
                        return $user;
                    }
                }

                return null;
            }

            public function userExists(string $email): bool
            {
                return isset($this->users[strtolower(trim($email))]);
            }
        };

        $security = new LoginSecurityService($this->loginHistory, [
            'window_seconds' => 900,
            'max_failures_per_account' => 3,
            'max_failures_per_ip' => 5,
        ]);

        $this->authService = new AuthService(
            $repository,
            $security,
            new AuditLogService($this->auditLogs)
        );

        $_SERVER['REMOTE_ADDR'] = '192.0.2.50';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
    }

    public function testFailedLoginIsRecordedAndAuditedWithoutPassword(): void
    {
        $result = $this->authService->login([
            'email' => 'student@example.com',
            'password' => 'WrongPass123!',
        ]);

        $this->assertFalse($result['success']);

        $counts = $this->loginHistory->countFailuresSinceLastSuccessByEmail('student@example.com', 900);
        $this->assertSame(1, $counts);

        $logs = $this->auditLogs->findRecent(10);
        $this->assertCount(1, $logs);
        $this->assertSame('login_failed', $logs[0]['action']);
        $this->assertArrayNotHasKey('password', $logs[0]);
        $this->assertArrayNotHasKey('password_hash', $logs[0]);
        $this->assertStringNotContainsString('WrongPass123!', json_encode($logs[0]));
    }

    public function testUnknownEmailUsesGenericFailureResponseAndIsRecorded(): void
    {
        $result = $this->authService->login([
            'email' => 'missing@example.com',
            'password' => 'WrongPass123!',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid credentials.', $result['message']);
        $this->assertSame(1, $this->loginHistory->countFailuresSinceLastSuccessByEmail('missing@example.com', 900));
        $this->assertSame('login_failed', $this->auditLogs->findRecent(1)[0]['action']);
    }

    public function testInactiveAccountUsesSameGenericFailureResponse(): void
    {
        $result = $this->authService->login([
            'email' => 'disabled@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid credentials.', $result['message']);
    }

    public function testThresholdIsEnforcedBeforeAnotherPasswordCheck(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $result = $this->authService->login([
                'email' => 'student@example.com',
                'password' => 'WrongPass123!',
            ]);
            $this->assertFalse($result['success']);
        }

        $result = $this->authService->login([
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Invalid credentials.', $result['message']);
        $this->assertSame(3, $this->loginHistory->countFailuresSinceLastSuccessByEmail('student@example.com', 900));

        $logs = $this->auditLogs->findRecent(10);
        $this->assertSame('login_throttled', $logs[0]['action']);
    }

    public function testSuccessfulLoginRecordsSuccessAndResetsFailures(): void
    {
        $this->authService->login([
            'email' => 'student@example.com',
            'password' => 'WrongPass123!',
        ]);

        $result = $this->authService->login([
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(0, $this->loginHistory->countFailuresSinceLastSuccessByEmail('student@example.com', 900));

        $logs = $this->auditLogs->findRecent(10);
        $this->assertSame('login_success', $logs[0]['action']);
    }
}
