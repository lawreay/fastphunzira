<?php

namespace App\Core;

final class Session
{
    public static function start(array $config = []): void
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (session_status() === PHP_SESSION_DISABLED || PHP_SAPI === 'cli' || headers_sent()) {
            $_SESSION = [];

            return;
        }

        $name = $config['session_name'] ?? 'fastphunzira_session';
        $lifetime = (int) ($config['session_lifetime'] ?? 1800);
        $requireHttps = (bool) ($config['require_https'] ?? false);
        $httponly = (bool) ($config['cookie_httponly'] ?? true);
        $samesite = $config['cookie_samesite'] ?? 'Lax';

        if (session_id() === '') {
            session_name($name);
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'secure' => $requireHttps,
                'httponly' => $httponly,
                'samesite' => $samesite,
            ]);
        }

        session_start();

        self::enforceIdleTimeout((int) ($config['session_idle_timeout'] ?? $lifetime));
    }

    public static function enforceIdleTimeout(int $timeout): void
    {
        if ($timeout <= 0 || PHP_SAPI === 'cli' || session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $now = time();
        $lastActivity = (int) ($_SESSION['_last_activity_at'] ?? 0);

        if ($lastActivity > 0 && ($now - $lastActivity) > $timeout) {
            $_SESSION = [];
            session_regenerate_id(true);
        }

        $_SESSION['_last_activity_at'] = $now;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        if (session_id() !== '') {
            session_unset();
            session_destroy();
        }
    }
}
