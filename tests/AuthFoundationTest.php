<?php

namespace App\Tests;

use App\Services\AuthService;
use PHPUnit\Framework\TestCase;

final class AuthFoundationTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        $_SESSION = [];

        $repository = new class implements \App\Repositories\UserRepositoryInterface {
            private array $users = [];

            public function create(array $user): array
            {
                $this->users[$user['email']] = $user;

                return $user;
            }

            public function findByEmail(string $email): ?array
            {
                return $this->users[$email] ?? null;
            }

            public function findById(int $id): ?array
            {
                foreach ($this->users as $user) {
                    if ((int) ($user['id'] ?? 0) === $id) {
                        return $user;
                    }
                }

                return null;
            }

            public function userExists(string $email): bool
            {
                return isset($this->users[$email]);
            }
        };

        $this->authService = new AuthService($repository);
    }

    public function testValidRegistrationCreatesUserWithStudentRole(): void
    {
        $result = $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('student', $result['data']['role']);
        $this->assertNotSame('StrongPass123!', $result['data']['password_hash']);
    }

    public function testDuplicateEmailIsRejected(): void
    {
        $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $result = $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'AnotherPass123!',
            'password_confirmation' => 'AnotherPass123!',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('email', $result['errors'][0]['field']);
    }

    public function testValidLoginCreatesAuthenticatedSession(): void
    {
        $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $result = $this->authService->login([
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('jane@example.com', $_SESSION['user']['email']);
    }

    public function testWrongPasswordAndUnknownEmailAreRejected(): void
    {
        $wrongPassword = $this->authService->login([
            'email' => 'missing@example.com',
            'password' => 'StrongPass123!',
        ]);
        $this->assertFalse($wrongPassword['success']);

        $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $badPassword = $this->authService->login([
            'email' => 'jane@example.com',
            'password' => 'WrongPass123!',
        ]);

        $this->assertFalse($badPassword['success']);
    }

    public function testInactiveAccountIsRejected(): void
    {
        $repository = new class implements \App\Repositories\UserRepositoryInterface {
            public function create(array $user): array
            {
                return $user;
            }

            public function findByEmail(string $email): ?array
            {
                return [
                    'id' => 99,
                    'full_name' => 'Disabled User',
                    'email' => $email,
                    'password_hash' => password_hash('StrongPass123!', PASSWORD_DEFAULT),
                    'status' => 'disabled',
                    'role' => 'student',
                ];
            }

            public function findById(int $id): ?array
            {
                return null;
            }

            public function userExists(string $email): bool
            {
                return false;
            }
        };

        $service = new AuthService($repository);

        $result = $service->login([
            'email' => 'disabled@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('This account is not active.', $result['message']);
    }

    public function testLogoutClearsSessionAndProtectedCheckRejectsGuests(): void
    {
        $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $this->authService->login([
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->authService->logout();

        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertFalse($this->authService->isAuthenticated());
    }
}
