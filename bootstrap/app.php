<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Env;
use App\Core\Session;
use App\Repositories\InMemoryUserRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;

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

$userRepository = $pdo !== null ? new UserRepository($pdo) : new InMemoryUserRepository();
$authService = new AuthService($userRepository);

return [
    'config' => $config,
    'db' => $pdo,
    'security' => $securityConfig,
    'auth' => $authService,
];
