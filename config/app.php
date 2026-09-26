<?php

return [
    'app_name' => getenv('APP_NAME') ?: 'FastPhunzira',
    'app_env' => getenv('APP_ENV') ?: 'staging',
    'app_debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'app_url' => getenv('APP_URL') ?: 'https://staging-fastphunzira.lovestoblog.com',
    'session_name' => getenv('SESSION_NAME') ?: 'fastphunzira_staging_session',
    'session_lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 1800),
];
