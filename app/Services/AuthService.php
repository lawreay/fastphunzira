<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\UserRepositoryInterface;

final class AuthService
{
    private const DUMMY_PASSWORD_HASH = '$2y$12$0EJ29ChY72blbaXigdv4LOog4CXT9E4zriIn8g5ab0eikzsLIdcz2';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ?LoginSecurityService $loginSecurity = null,
        private ?AuditLogService $auditLog = null
    ) {
    }

    public function register(array $data): array
    {
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $passwordConfirmation = (string) ($data['password_confirmation'] ?? '');

        $errors = [];

        if ($fullName === '') {
            $errors[] = ['field' => 'full_name', 'message' => 'Full name is required.'];
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = ['field' => 'email', 'message' => 'A valid email is required.'];
        }

        if ($password === '' || strlen($password) < 8) {
            $errors[] = ['field' => 'password', 'message' => 'Password must be at least 8 characters long.'];
        }

        if ($password !== $passwordConfirmation) {
            $errors[] = ['field' => 'password_confirmation', 'message' => 'Passwords do not match.'];
        }

        if ($this->userRepository->userExists($email)) {
            $errors[] = ['field' => 'email', 'message' => 'An account with that email already exists.'];
        }

        if ($errors !== []) {
            return ['success' => false, 'message' => 'Registration failed.', 'errors' => $errors];
        }

        $user = [
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'status' => 'active',
            'role' => 'student',
            'force_password_change' => 1,
        ];

        $createdUser = $this->userRepository->create($user);

        return [
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'id' => $createdUser['id'] ?? null,
                'full_name' => $createdUser['full_name'],
                'email' => $createdUser['email'],
                'status' => $createdUser['status'] ?? 'active',
                'role' => $createdUser['role'] ?? 'student',
            ],
        ];
    }

    public function login(array $data): array
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }

        if ($this->loginSecurity !== null && $this->loginSecurity->isThrottled($email, $this->clientIp())) {
            $this->auditSecurityEvent('login_throttled', $email);

            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        $user = $this->userRepository->findByEmail($email);
        $passwordHash = is_array($user) && isset($user['password_hash'])
            ? (string) $user['password_hash']
            : self::DUMMY_PASSWORD_HASH;

        $passwordValid = password_verify($password, $passwordHash);

        if ($user === null || !$passwordValid || !isset($user['password_hash'])) {
            $this->recordFailedLogin($email, is_array($user) ? (int) ($user['id'] ?? 0) : null);
            $this->auditSecurityEvent('login_failed', $email, is_array($user) ? (int) ($user['id'] ?? 0) : null);

            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        if (($user['status'] ?? 'active') !== 'active') {
            $this->recordFailedLogin($email, (int) ($user['id'] ?? 0));
            $this->auditSecurityEvent('login_failed', $email, (int) ($user['id'] ?? 0));

            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        $user['role'] = $user['role'] ?? 'student';
        $sessionUser = $user;
        unset($sessionUser['password_hash']);
        Auth::login($sessionUser);

        $this->recordSuccessfulLogin($email, (int) ($user['id'] ?? 0));
        $this->auditSecurityEvent('login_success', $email, (int) ($user['id'] ?? 0));

        return [
            'success' => true,
            'message' => 'Login successful.',
            'data' => $sessionUser,
        ];
    }

    public function requestPasswordReset(array $data): array
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'A valid email is required.'];
        }

        $user = $this->userRepository->findByEmail($email);
        if ($user === null) {
            return [
                'success' => true,
                'message' => 'If an account exists for that email, password reset instructions will be sent when email delivery is available.',
                'data' => ['email' => $email],
            ];
        }

        $token = bin2hex(random_bytes(24));
        $expiresAt = time() + 3600;

        $_SESSION['password_reset_tokens'] = $_SESSION['password_reset_tokens'] ?? [];
        $_SESSION['password_reset_tokens'][$token] = [
            'email' => $email,
            'expires_at' => $expiresAt,
        ];

        return [
            'success' => true,
            'message' => 'If an account exists for that email, password reset instructions will be sent when email delivery is available.',
            'data' => ['email' => $email, 'token' => $token],
        ];
    }

    public function resetPassword(string $token, array $data): array
    {
        $normalizedToken = trim((string) $token);
        $password = (string) ($data['password'] ?? '');
        $passwordConfirmation = (string) ($data['password_confirmation'] ?? '');

        if ($normalizedToken === '') {
            return ['success' => false, 'message' => 'A valid reset token is required.'];
        }

        $resetTokens = $_SESSION['password_reset_tokens'] ?? [];

        if (!isset($resetTokens[$normalizedToken])) {
            return ['success' => false, 'message' => 'This reset link is invalid or has expired.'];
        }

        $record = $resetTokens[$normalizedToken];
        if ((int) ($record['expires_at'] ?? 0) < time()) {
            unset($resetTokens[$normalizedToken]);
            $_SESSION['password_reset_tokens'] = $resetTokens;

            return ['success' => false, 'message' => 'This reset link has expired.'];
        }

        if ($password === '' || strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        if ($password !== $passwordConfirmation) {
            return ['success' => false, 'message' => 'Passwords do not match.'];
        }

        $user = $this->userRepository->findByEmail((string) ($record['email'] ?? ''));
        if ($user === null) {
            return ['success' => false, 'message' => 'We could not find that account.'];
        }

        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updated = $this->userRepository->updatePassword((int) ($user['id'] ?? 0), $newHash);

        unset($resetTokens[$normalizedToken]);
        $_SESSION['password_reset_tokens'] = $resetTokens;

        if (!$updated) {
            return ['success' => false, 'message' => 'Password reset failed. Please try again.'];
        }

        return ['success' => true, 'message' => 'Your password has been reset successfully.'];
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    public function currentUser(): ?array
    {
        return Auth::user();
    }

    private function recordFailedLogin(string $email, ?int $userId): void
    {
        if ($this->loginSecurity === null) {
            return;
        }

        try {
            $this->loginSecurity->recordFailure($email, $userId ?: null);
        } catch (\Throwable $e) {
            error_log('Login failure history could not be recorded: ' . $e->getMessage());
        }
    }

    private function recordSuccessfulLogin(string $email, int $userId): void
    {
        if ($this->loginSecurity === null) {
            return;
        }

        try {
            $this->loginSecurity->recordSuccess($email, $userId);
        } catch (\Throwable $e) {
            error_log('Login success history could not be recorded: ' . $e->getMessage());
        }
    }

    private function auditSecurityEvent(string $action, string $email, ?int $userId = null): void
    {
        if ($this->auditLog === null) {
            return;
        }

        try {
            $this->auditLog->record([
                'user_id' => $userId ?: null,
                'action' => $action,
                'entity_type' => 'authentication',
                'entity_id' => $userId ?: null,
                'new_values' => [
                    'identifier_hash' => hash('sha256', $email),
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('Authentication security event could not be audited: ' . $e->getMessage());
        }
    }

    private function clientIp(): string
    {
        $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
}
