<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\UserRepositoryInterface;

final class AuthService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
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

        $user = $this->userRepository->findByEmail($email);

        if ($user === null || !isset($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid credentials.'];
        }

        if (($user['status'] ?? 'active') !== 'active') {
            return ['success' => false, 'message' => 'This account is not active.'];
        }

        $user['role'] = $user['role'] ?? 'student';
        $sessionUser = $user;
        unset($sessionUser['password_hash']);
        Auth::login($sessionUser);

        return [
            'success' => true,
            'message' => 'Login successful.',
            'data' => $sessionUser,
        ];
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
}
