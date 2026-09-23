<?php

namespace App\Support;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function validate(?string $token): bool
    {
        $expected = self::token();

        return is_string($token) && hash_equals($expected, $token);
    }
}
