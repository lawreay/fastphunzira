<?php

use App\Core\Session;
use App\Repositories\InMemoryUserRepository;
use App\Services\AuthService;
use PHPUnit\Framework\TestCase;

final class AuthPasswordResetTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        Session::start();
    }

    public function testPasswordResetFlowWorks(): void
    {
        $repository = new InMemoryUserRepository();
        $repository->create([
            'id' => 42,
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password_hash' => password_hash('oldPass123', PASSWORD_DEFAULT),
            'status' => 'active',
            'role' => 'student',
        ]);

        $authService = new AuthService($repository);

        $requestResult = $authService->requestPasswordReset(['email' => 'jane@example.com']);
        $this->assertTrue($requestResult['success']);
        $this->assertArrayHasKey('token', $requestResult['data']);

        $token = (string) $requestResult['data']['token'];

        $resetResult = $authService->resetPassword($token, [
            'password' => 'newStrongPass123',
            'password_confirmation' => 'newStrongPass123',
        ]);

        $this->assertTrue($resetResult['success']);

        $updatedUser = $repository->findByEmail('jane@example.com');
        $this->assertNotNull($updatedUser);
        $this->assertTrue(password_verify('newStrongPass123', $updatedUser['password_hash']));
    }
}
