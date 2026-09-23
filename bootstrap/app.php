<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Env;
use App\Core\Session;
use App\Controllers\CourseController;
use App\Repositories\CourseRepository;
use App\Repositories\InMemoryUserRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\CourseService;

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
$courseRepository = $pdo !== null ? new CourseRepository($pdo) : new \App\Repositories\InMemoryCourseRepository();
$courseService = new CourseService($courseRepository);
$courseController = new CourseController($courseService);

return [
    'config' => $config,
    'db' => $pdo,
    'security' => $securityConfig,
    'auth' => $authService,
    'courseService' => $courseService,
    'courseController' => $courseController,
];
