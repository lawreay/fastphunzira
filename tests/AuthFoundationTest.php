<?php

namespace App\Tests;

use App\Core\Auth;
use App\Repositories\CourseModuleRepositoryInterface;
use App\Repositories\CourseRepositoryInterface;
use App\Repositories\EnrollmentRepositoryInterface;
use App\Repositories\LessonProgressRepositoryInterface;
use App\Repositories\LessonRepositoryInterface;
use App\Services\AuthService;
use App\Services\EnrollmentLearningService;
use PDO;
use PHPUnit\Framework\TestCase;

final class AuthFoundationTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        $_SESSION = [];

        $repository = new class implements \App\Repositories\UserRepositoryInterface {
            private array $users = [];

            public function create(array $user): array
            {
                $this->users[$user['email']] = $user;

                return $user;
            }

            public function findByEmail(string $email): ?array
            {
                return $this->users[$email] ?? null;
            }

            public function findById(int $id): ?array
            {
                foreach ($this->users as $user) {
                    if ((int) ($user['id'] ?? 0) === $id) {
                        return $user;
                    }
                }

                return null;
            }

            public function userExists(string $email): bool
            {
                return isset($this->users[$email]);
            }
        };

        $this->authService = new AuthService($repository);
    }

    public function testValidRegistrationCreatesUserWithStudentRole(): void
    {
        $result = $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('student', $result['data']['role']);
        $this->assertArrayNotHasKey('password_hash', $result['data']);
    }

    public function testDuplicateEmailIsRejected(): void
    {
        $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $result = $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'AnotherPass123!',
            'password_confirmation' => 'AnotherPass123!',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('email', $result['errors'][0]['field']);
    }

    public function testValidLoginCreatesAuthenticatedSession(): void
    {
        $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $result = $this->authService->login([
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('jane@example.com', $_SESSION['user']['email']);
    }

    public function testWrongPasswordAndUnknownEmailAreRejected(): void
    {
        $wrongPassword = $this->authService->login([
            'email' => 'missing@example.com',
            'password' => 'StrongPass123!',
        ]);
        $this->assertFalse($wrongPassword['success']);

        $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $badPassword = $this->authService->login([
            'email' => 'jane@example.com',
            'password' => 'WrongPass123!',
        ]);

        $this->assertFalse($badPassword['success']);
    }

    public function testInactiveAccountIsRejected(): void
    {
        $repository = new class implements \App\Repositories\UserRepositoryInterface {
            public function create(array $user): array
            {
                return $user;
            }

            public function findByEmail(string $email): ?array
            {
                return [
                    'id' => 99,
                    'full_name' => 'Disabled User',
                    'email' => $email,
                    'password_hash' => password_hash('StrongPass123!', PASSWORD_DEFAULT),
                    'status' => 'disabled',
                    'role' => 'student',
                ];
            }

            public function findById(int $id): ?array
            {
                return null;
            }

            public function userExists(string $email): bool
            {
                return false;
            }
        };

        $service = new AuthService($repository);

        $result = $service->login([
            'email' => 'disabled@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('This account is not active.', $result['message']);
    }

    public function testModuleCreationRejectsNonAdminActorId(): void
    {
        Auth::login([
            'id' => 42,
            'email' => 'student@example.com',
            'role' => 'student',
        ]);

        $courseRepository = new class implements CourseRepositoryInterface {
            public function create(array $course): array { return $course; }
            public function update(int $id, array $course): ?array { return $course; }
            public function findById(int $id): ?array { return ['id' => $id, 'status' => 'published']; }
            public function findBySlug(string $slug): ?array { return null; }
            public function findAll(): array { return []; }
            public function findPublished(): array { return []; }
            public function getAll(): array { return []; }
            public function getPublished(): array { return []; }
            public function getBySlug(string $slug): ?array { return null; }
        };

        $moduleRepository = new class implements CourseModuleRepositoryInterface {
            public function create(array $module): array { return $module; }
            public function update(int $id, array $data): ?array { return $data; }
            public function findById(int $id): ?array { return null; }
            public function findByCourse(int $courseId): array { return []; }
        };

        $enrollmentRepository = new class implements EnrollmentRepositoryInterface {
            public function create(array $enrollment): array { return $enrollment; }
            public function findByStudentAndCourse(int $studentId, int $courseId): ?array { return []; }
            public function findByStudent(int $studentId): array { return []; }
        };

        $lessonRepository = new class implements LessonRepositoryInterface {
            public function create(array $lesson): array { return $lesson; }
            public function update(int $id, array $data): ?array { return $data; }
            public function findById(int $id): ?array { return null; }
            public function findByModule(int $moduleId): array { return []; }
            public function findByCourse(int $courseId): array { return []; }
        };

        $progressRepository = new class implements LessonProgressRepositoryInterface {
            public function createOrUpdate(array $progress): array { return $progress; }
            public function findByStudentAndLesson(int $studentId, int $lessonId): ?array { return null; }
            public function findByStudentAndCourse(int $studentId, int $courseId): array { return []; }
        };

        $service = new EnrollmentLearningService(
            $courseRepository,
            $enrollmentRepository,
            $moduleRepository,
            $lessonRepository,
            $progressRepository
        );

        $result = $service->createModule(1, ['title' => 'Module A', 'description' => 'A module'], 99);

        $this->assertFalse($result['success']);
        $this->assertSame('Only administrators can manage modules.', $result['message']);
    }

    public function testCourseRepositoryUpdateIgnoresUnknownColumns(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE courses (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, slug TEXT, description TEXT, status TEXT, created_by INTEGER)');
        $pdo->exec("INSERT INTO courses (title, slug, description, status, created_by) VALUES ('Old title', 'old-title', 'Old description', 'draft', 1)");

        $repository = new \App\Repositories\CourseRepository($pdo);

        $updated = $repository->update(1, [
            'title' => 'New title',
            'status' => 'published',
            'secret' => 'should-not-update',
        ]);

        $this->assertNotNull($updated);
        $this->assertSame('New title', $updated['title']);
        $this->assertSame('published', $updated['status']);
        $this->assertArrayNotHasKey('secret', $updated);
    }

    public function testLogoutClearsSessionAndProtectedCheckRejectsGuests(): void
    {
        $this->authService->register([
            'full_name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $this->authService->login([
            'email' => 'jane@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->authService->logout();

        $this->assertArrayNotHasKey('user', $_SESSION);
        $this->assertFalse($this->authService->isAuthenticated());
    }
}
