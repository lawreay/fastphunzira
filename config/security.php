<?php

return [
    'session_lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 1800),
    'session_idle_timeout' => (int) (getenv('SESSION_IDLE_TIMEOUT') ?: 1800),
    'session_name' => getenv('SESSION_NAME') ?: 'fastphunzira_session',
    'csrf_token_name' => '_token',
    'password_algo' => PASSWORD_DEFAULT,
    'require_https' => filter_var(getenv('REQUIRE_HTTPS') ?: ((getenv('APP_ENV') ?: 'local') === 'production' ? 'true' : 'false'), FILTER_VALIDATE_BOOLEAN),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',

    'login_security' => [
        'window_seconds' => (int) (getenv('LOGIN_SECURITY_WINDOW') ?: 900),
        'max_failures_per_account' => (int) (getenv('LOGIN_MAX_FAILURES_PER_ACCOUNT') ?: 5),
        'max_failures_per_ip' => (int) (getenv('LOGIN_MAX_FAILURES_PER_IP') ?: 20),
        'throttle_seconds' => (int) (getenv('LOGIN_THROTTLE_SECONDS') ?: 900),
    ],
];
