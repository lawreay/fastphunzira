<?php

return [
    'session_lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 1800),
    'session_idle_timeout' => (int) (getenv('SESSION_IDLE_TIMEOUT') ?: 1800),
    'session_name' => getenv('SESSION_NAME') ?: 'fastphunzira_session',
    'csrf_token_name' => '_token',
    'password_algo' => PASSWORD_DEFAULT,
    'require_https' => filter_var(getenv('REQUIRE_HTTPS') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
];
