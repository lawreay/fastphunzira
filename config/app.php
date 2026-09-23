<?php

return [
    'app_name' => getenv('APP_NAME') ?: 'FastPhunzira',
    'app_env' => getenv('APP_ENV') ?: 'local',
    'app_debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    'app_url' => getenv('APP_URL') ?: 'http://localhost',
    'session_name' => getenv('SESSION_NAME') ?: 'fastphunzira_session',
    'session_lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 1800),
];
