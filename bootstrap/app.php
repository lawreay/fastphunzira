<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Env;
use App\Core\Session;
use App\Controllers\CourseController;
use App\Repositories\AuditLogRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\CourseModuleRepository;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\ExamAttemptRepository;
use App\Repositories\ExamRepository;
use App\Repositories\InMemoryAuditLogRepository;
use App\Repositories\InMemoryCertificateRepository;
use App\Repositories\InMemoryExamAttemptRepository;
use App\Repositories\InMemoryExamRepository;
use App\Repositories\InMemoryCourseModuleRepository;
use App\Repositories\InMemoryEnrollmentRepository;
use App\Repositories\InMemoryLessonProgressRepository;
use App\Repositories\InMemoryLessonRepository;
use App\Repositories\InMemoryUserRepository;
use App\Repositories\LessonProgressRepository;
use App\Repositories\LessonRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\QuizAttemptRepository;
use App\Repositories\QuizRepository;
use App\Repositories\UserRepository;
use App\Services\AuditLogService;
use App\Services\AuthService;
use App\Services\CertificateService;
use App\Services\CourseService;
use App\Services\EnrollmentLearningService;
use App\Services\ExamService;
use App\Services\QuizService;

Env::load(__DIR__ . '/../.env');

$config = require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$securityConfig = require __DIR__ . '/../config/security.php';

Session::start($securityConfig);

if (!function_exists('redirect_to')) {
    function redirect_to(string $path): array
    {
        return ['redirect' => base_url($path)];
    }
}

try {
    $pdo = Database::connect($dbConfig);
} catch (Throwable $e) {
    error_log('Database connection failed: ' . $e->getMessage());

    if (($config['app_env'] ?? 'local') === 'production') {
        http_response_code(500);
        exit('Service temporarily unavailable.');
    }

    $pdo = null;
}

$userRepository = $pdo !== null ? new UserRepository($pdo) : new InMemoryUserRepository();
$authService = new AuthService($userRepository);
$courseRepository = $pdo !== null ? new CourseRepository($pdo) : new \App\Repositories\InMemoryCourseRepository();
$moduleRepository = $pdo !== null ? new CourseModuleRepository($pdo) : new InMemoryCourseModuleRepository();
$lessonRepository = $pdo !== null ? new LessonRepository($pdo) : new InMemoryLessonRepository();
$enrollmentRepository = $pdo !== null ? new EnrollmentRepository($pdo) : new InMemoryEnrollmentRepository();
$progressRepository = $pdo !== null ? new LessonProgressRepository($pdo) : new InMemoryLessonProgressRepository();
$quizRepository = $pdo !== null ? new QuizRepository($pdo) : new \App\Repositories\InMemoryQuizRepository();
$questionRepository = $pdo !== null ? new QuestionRepository($pdo) : new \App\Repositories\InMemoryQuestionRepository();
$quizAttemptRepository = $pdo !== null ? new QuizAttemptRepository($pdo) : new \App\Repositories\InMemoryQuizAttemptRepository();
$examRepository = $pdo !== null ? new ExamRepository($pdo) : new InMemoryExamRepository();
$examAttemptRepository = $pdo !== null ? new ExamAttemptRepository($pdo) : new InMemoryExamAttemptRepository();
$auditLogRepository = $pdo !== null ? new AuditLogRepository($pdo) : new InMemoryAuditLogRepository();
$certificateRepository = $pdo !== null ? new CertificateRepository($pdo) : new InMemoryCertificateRepository();
$courseService = new CourseService($courseRepository);
$courseController = new CourseController($courseService);
$quizService = new QuizService(
    $courseRepository,
    $enrollmentRepository,
    $quizRepository,
    $questionRepository,
    $quizAttemptRepository
);
$examService = new ExamService(
    $courseRepository,
    $enrollmentRepository,
    $examRepository,
    $questionRepository,
    $examAttemptRepository
);
$auditLogService = new AuditLogService($auditLogRepository);
$certificateService = new CertificateService(
    $courseRepository,
    $enrollmentRepository,
    $examRepository,
    $examAttemptRepository,
    $certificateRepository,
    $auditLogService
);
$enrollmentLearningService = new EnrollmentLearningService(
    $courseRepository,
    $enrollmentRepository,
    $moduleRepository,
    $lessonRepository,
    $progressRepository
);

return [
    'config' => $config,
    'db' => $pdo,
    'security' => $securityConfig,
    'auth' => $authService,
    'courseService' => $courseService,
    'courseController' => $courseController,
    'courseRepository' => $courseRepository,
    'moduleRepository' => $moduleRepository,
    'lessonRepository' => $lessonRepository,
    'enrollmentRepository' => $enrollmentRepository,
    'progressRepository' => $progressRepository,
    'enrollmentLearningService' => $enrollmentLearningService,
    'quizRepository' => $quizRepository,
    'questionRepository' => $questionRepository,
    'quizAttemptRepository' => $quizAttemptRepository,
    'quizService' => $quizService,
    'examRepository' => $examRepository,
    'examAttemptRepository' => $examAttemptRepository,
    'examService' => $examService,
    'auditLogRepository' => $auditLogRepository,
    'auditLogService' => $auditLogService,
    'certificateRepository' => $certificateRepository,
    'certificateService' => $certificateService,
];
