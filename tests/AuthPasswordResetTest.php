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

    public function testUnknownEmailDoesNotRevealWhetherAnAccountExists(): void
    {
        $authService = new AuthService(new InMemoryUserRepository());

        $result = $authService->requestPasswordReset(['email' => 'unknown@example.com']);

        $this->assertTrue($result['success']);
        $this->assertSame(
            'If an account exists for that email, password reset instructions will be sent when email delivery is available.',
            $result['message']
        );
        $this->assertArrayNotHasKey('token', $result['data']);
    }

    public function testResetTokenCannotBeReused(): void
    {
        $repository = new InMemoryUserRepository();
        $repository->create([
            'id' => 43,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'password_hash' => password_hash('oldPass123', PASSWORD_DEFAULT),
            'status' => 'active',
            'role' => 'student',
        ]);

        $authService = new AuthService($repository);
        $requestResult = $authService->requestPasswordReset(['email' => 'john@example.com']);
        $token = (string) $requestResult['data']['token'];

        $firstReset = $authService->resetPassword($token, [
            'password' => 'newStrongPass123',
            'password_confirmation' => 'newStrongPass123',
        ]);
        $secondReset = $authService->resetPassword($token, [
            'password' => 'anotherStrongPass123',
            'password_confirmation' => 'anotherStrongPass123',
        ]);

        $this->assertTrue($firstReset['success']);
        $this->assertFalse($secondReset['success']);
        $this->assertSame('This reset link is invalid or has expired.', $secondReset['message']);
    }
}
