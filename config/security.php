<?php

return [
    'session_lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 1800),
    'session_name' => getenv('SESSION_NAME') ?: 'fastphunzira_session',
    'csrf_token_name' => '_token',
    'password_algo' => PASSWORD_DEFAULT,
    'require_https' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
];
