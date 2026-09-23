<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Env;
use App\Core\Session;

Env::load(__DIR__ . '/../.env');

$config = require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$securityConfig = require __DIR__ . '/../config/security.php';

Session::start($securityConfig);

try {
    $pdo = Database::connect($dbConfig);
} catch (Throwable $e) {
    $pdo = null;
}

return [
    'config' => $config,
    'db' => $pdo,
    'security' => $securityConfig,
];
