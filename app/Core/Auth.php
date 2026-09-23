<?php

namespace App\Core;

final class Auth
{
    public static function check(): bool
    {
        return !empty(self::userId());
    }

    public static function user(): ?array
    {
        $user = Session::get('user');

        return is_array($user) ? $user : null;
    }

    public static function userId(): ?int
    {
        $userId = Session::get('user_id');

        if ($userId === null) {
            return null;
        }

        return (int) $userId;
    }

    public static function userHasRole(string $role): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }

        return strtolower((string) ($user['role'] ?? '')) === strtolower($role);
    }

    public static function isAdmin(): bool
    {
        return self::userHasRole('admin');
    }

    public static function userCan(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }

        $role = strtolower((string) (self::user()['role'] ?? ''));

        if ($permission === 'courses.manage') {
            return $role === 'admin';
        }

        if ($permission === 'courses.view') {
            return in_array($role, ['admin', 'student'], true);
        }

        if ($permission === 'dashboard.view') {
            return in_array($role, ['admin', 'student'], true);
        }

        return false;
    }

    public static function login(array $user): void
    {
        Session::start();

        if (session_status() === PHP_SESSION_ACTIVE && PHP_SAPI !== 'cli') {
            session_regenerate_id(true);
        }

        Session::set('user_id', (int) ($user['id'] ?? 0));
        Session::set('user', $user);
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::forget('user');
        Session::destroy();
    }
}
