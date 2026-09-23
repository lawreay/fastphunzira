<?php

namespace App\Tests;

use App\Core\Auth;
use App\Services\CourseService;
use PHPUnit\Framework\TestCase;

final class CourseManagementTest extends TestCase
{
    private CourseService $courseService;

    protected function setUp(): void
    {
        $_SESSION = [];
        Auth::logout();

        $this->courseService = new CourseService(new class implements \App\Repositories\CourseRepositoryInterface {
            private array $courses = [];

            public function create(array $course): array
            {
                $id = count($this->courses) + 1;
                $course['id'] = $id;
                $course['status'] = $course['status'] ?? 'draft';
                $this->courses[$id] = $course;

                return $course;
            }

            public function update(int $id, array $course): ?array
            {
                if (!isset($this->courses[$id])) {
                    return null;
                }

                $this->courses[$id] = array_merge($this->courses[$id], $course);

                return $this->courses[$id];
            }

            public function findById(int $id): ?array
            {
                return $this->courses[$id] ?? null;
            }

            public function findAll(): array
            {
                return array_values($this->courses);
            }

            public function findPublished(): array
            {
                return array_values(array_filter($this->courses, static fn (array $course) => ($course['status'] ?? 'draft') === 'published'));
            }

            public function getAll(): array
            {
                return $this->findAll();
            }

            public function getPublished(): array
            {
                return $this->findPublished();
            }
        });
    }

    public function testAdminCanCreateCourse(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $result = $this->courseService->createCourse([
            'title' => 'Intro to PHP',
            'slug' => 'intro-to-php',
            'description' => 'Learn PHP fundamentals',
            'status' => 'draft',
        ], 10);

        $this->assertTrue($result['success']);
        $this->assertSame('Intro to PHP', $result['data']['title']);
    }

    public function testStudentCannotCreateCourse(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $result = $this->courseService->createCourse([
            'title' => 'Should fail',
            'slug' => 'should-fail',
            'description' => 'No access',
            'status' => 'draft',
        ], 20);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
    }

    public function testPublishedCourseCatalogueExcludesDrafts(): void
    {
        $this->courseService->createCourse([
            'title' => 'Visible course',
            'slug' => 'visible-course',
            'description' => 'Published example',
            'status' => 'published',
        ], 10, true);

        $this->courseService->createCourse([
            'title' => 'Hidden course',
            'slug' => 'hidden-course',
            'description' => 'Draft example',
            'status' => 'draft',
        ], 10, true);

        $courses = $this->courseService->getPublishedCourses();

        $this->assertCount(1, $courses);
        $this->assertSame('Visible course', $courses[0]['title']);
    }

    public function testAdminCanPublishAndUpdateCourse(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $created = $this->courseService->createCourse([
            'title' => 'Course to publish',
            'slug' => 'course-to-publish',
            'description' => 'Draft content',
            'status' => 'draft',
        ], 10);

        $updated = $this->courseService->updateCourse((int) $created['data']['id'], ['status' => 'published'], 10);

        $this->assertTrue($updated['success']);
        $this->assertSame('published', $updated['data']['status']);
    }
}
