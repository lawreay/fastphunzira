<?php

namespace App\Core;

final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }

    public static function validateForRuntime(string $appEnv, array $values = []): void
    {
        $env = strtolower(trim($appEnv) !== '' ? $appEnv : (string) ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'local'));
        if (!in_array($env, ['local', 'staging', 'production', 'testing'], true)) {
            $env = 'local';
        }

        $resolved = [];
        foreach (['APP_ENV', 'APP_URL', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
            $resolved[$key] = $values[$key] ?? $_ENV[$key] ?? getenv($key) ?: '';
        }

        if (in_array($env, ['staging', 'production'], true)) {
            $requiredKeys = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_URL'];

            foreach ($requiredKeys as $key) {
                $value = trim((string) ($resolved[$key] ?? ''));
                if ($value === '' || self::isPlaceholderValue($value)) {
                    throw new \RuntimeException("Environment validation failed: {$key} is missing or still contains a placeholder value.");
                }
            }

            $debugFlag = strtolower(trim((string) ($values['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'false')));
            if ($debugFlag === 'true') {
                throw new \RuntimeException('Environment validation failed: APP_DEBUG must be false in staging/production.');
            }
        }
    }

    private static function isPlaceholderValue(string $value): bool
    {
        $normalized = strtolower(trim($value));

        return $normalized === ''
            || str_starts_with($normalized, 'your_')
            || str_starts_with($normalized, 'replace_')
            || str_starts_with($normalized, 'change_')
            || str_contains($normalized, 'example')
            || str_contains($normalized, 'placeholder');
    }
}
