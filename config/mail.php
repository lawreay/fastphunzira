<?php

return [
    'enabled' => filter_var(getenv('MAIL_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'host' => trim((string) (getenv('MAIL_HOST') ?: '')),
    'port' => (int) (getenv('MAIL_PORT') ?: 587),
    'username' => (string) (getenv('MAIL_USERNAME') ?: ''),
    'password' => (string) (getenv('MAIL_PASSWORD') ?: ''),
    'encryption' => strtolower(trim((string) (getenv('MAIL_ENCRYPTION') ?: 'tls'))),
    'from_address' => trim((string) (getenv('MAIL_FROM_ADDRESS') ?: '')),
    'from_name' => trim((string) (getenv('MAIL_FROM_NAME') ?: 'FastPhunzira')),
    'timeout' => max(5, (int) (getenv('MAIL_TIMEOUT') ?: 15)),
];