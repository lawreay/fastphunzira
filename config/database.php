<?php

return [
    'host' => getenv('DB_HOST') ?: 'sql205.infinityfree.com',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_DATABASE') ?: 'if0_43013735_stagingfastphunzira',
    'username' => getenv('DB_USERNAME') ?: 'if0_43013735',
    'password' => getenv('DB_PASSWORD') ?: 'lastBorn333',
    'charset' => 'utf8mb4',
    'dsn' => sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        getenv('DB_HOST') ?: 'sql205.infinityfree.com',
        getenv('DB_PORT') ?: '3306',
        getenv('DB_DATABASE') ?: 'if0_43013735_stagingfastphunzira',
        'utf8mb4'
    ),
];
