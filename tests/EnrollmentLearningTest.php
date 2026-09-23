<?php

namespace App\Tests;

use App\Core\Auth;
use App\Repositories\InMemoryCourseModuleRepository;
use App\Repositories\InMemoryCourseRepository;
use App\Repositories\InMemoryEnrollmentRepository;
use App\Repositories\InMemoryLessonProgressRepository;
use App\Repositories\InMemoryLessonRepository;
use App\Services\EnrollmentLearningService;
use PHPUnit\Framework\TestCase;

final class EnrollmentLearningTest extends TestCase
{
    private EnrollmentLearningService $service;

    protected function setUp(): void
    {
        $_SESSION = [];
        Auth::logout();

        $courseRepository = new InMemoryCourseRepository();
        $courseRepository->create([
            'title' => 'PHP Foundations',
            'slug' => 'php-foundations',
            'description' => 'Published course',
            'status' => 'published',
            'created_by' => 10,
        ]);

        $this->service = new EnrollmentLearningService(
            $courseRepository,
            new InMemoryEnrollmentRepository(),
            new InMemoryCourseModuleRepository(),
            new InMemoryLessonRepository(),
            new InMemoryLessonProgressRepository()
        );
    }

    public function testStudentCanEnrollInPublishedCourse(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $result = $this->service->enrollStudentInCourse(20, 1);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['data']['course_id']);
    }

    public function testDuplicateEnrollmentIsRejected(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $this->service->enrollStudentInCourse(20, 1);
        $result = $this->service->enrollStudentInCourse(20, 1);

        $this->assertFalse($result['success']);
        $this->assertSame('duplicate_enrollment', $result['code']);
    }

    public function testStudentCannotEnrollInUnpublishedCourse(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $this->service->getCourseRepository()->create([
            'title' => 'Private course',
            'slug' => 'private-course',
            'description' => 'Not public',
            'status' => 'draft',
            'created_by' => 10,
        ]);

        $result = $this->service->enrollStudentInCourse(20, 2);

        $this->assertFalse($result['success']);
        $this->assertSame('course_unavailable', $result['code']);
    }

    public function testUnauthorizedUserCannotEnroll(): void
    {
        Auth::logout();

        $result = $this->service->enrollStudentInCourse(20, 1);

        $this->assertFalse($result['success']);
        $this->assertSame('unauthorized', $result['code']);
    }

    public function testAdminCanCreateModule(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $result = $this->service->createModule(1, [
            'title' => 'Introduction',
            'description' => 'Intro module',
            'sort_order' => 1,
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('Introduction', $result['data']['title']);
    }

    public function testStudentCannotModifyModule(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $module = $this->service->createModule(1, [
            'title' => 'Introduction',
            'description' => 'Intro module',
            'sort_order' => 1,
        ], 10);

        $result = $this->service->updateModule((int) $module['data']['id'], ['title' => 'Blocked']);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
    }

    public function testAdminCanCreateLesson(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $module = $this->service->createModule(1, [
            'title' => 'Introduction',
            'description' => 'Intro module',
            'sort_order' => 1,
        ]);

        $result = $this->service->createLesson((int) $module['data']['id'], [
            'title' => 'Lesson 1',
            'content' => 'Welcome content',
            'sort_order' => 1,
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('Lesson 1', $result['data']['title']);
    }

    public function testStudentCannotAccessUnenrolledLesson(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $module = $this->service->createModule(1, [
            'title' => 'Introduction',
            'description' => 'Intro module',
            'sort_order' => 1,
        ], 10);
        $lesson = $this->service->createLesson((int) $module['data']['id'], [
            'title' => 'Lesson 1',
            'content' => 'Welcome content',
            'sort_order' => 1,
        ], 10);

        $result = $this->service->getLessonForStudent(20, (int) $lesson['data']['id']);

        $this->assertNull($result);
    }

    public function testLessonCompletionAndProgressCalculation(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $this->service->enrollStudentInCourse(20, 1);

        $module = $this->service->createModule(1, [
            'title' => 'Introduction',
            'description' => 'Intro module',
            'sort_order' => 1,
        ], 10);
        $lesson = $this->service->createLesson((int) $module['data']['id'], [
            'title' => 'Lesson 1',
            'content' => 'Welcome content',
            'sort_order' => 1,
        ], 10);

        $complete = $this->service->markLessonComplete(20, (int) $lesson['data']['id']);
        $progress = $this->service->getCourseProgress(20, 1);

        $this->assertTrue($complete['success']);
        $this->assertSame(100.0, $progress['percent']);
        $this->assertSame(1, $progress['completed_lessons']);
    }

    public function testCourseWithNoLessonsIsNotMarkedComplete(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $this->service->enrollStudentInCourse(20, 1);

        $progress = $this->service->getCourseProgress(20, 1);

        $this->assertSame(0.0, $progress['percent']);
        $this->assertSame(0, $progress['completed_lessons']);
        $this->assertSame(0, $progress['total_lessons']);
    }
}
