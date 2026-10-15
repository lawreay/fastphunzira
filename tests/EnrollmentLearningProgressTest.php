<?php

use App\Core\Auth;
use App\Core\Session;
use App\Repositories\InMemoryCourseModuleRepository;
use App\Repositories\InMemoryCourseRepository;
use App\Repositories\InMemoryEnrollmentRepository;
use App\Repositories\InMemoryLessonProgressRepository;
use App\Repositories\InMemoryLessonRepository;
use App\Services\EnrollmentLearningService;
use PHPUnit\Framework\TestCase;

final class EnrollmentLearningProgressTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        Session::start();
        Auth::login([
            'id' => 7,
            'full_name' => 'Test Student',
            'email' => 'student@example.test',
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    private function fixture(string $status = 'published', string $accessTier = 'regular', bool $enroll = true): array
    {
        $courses = new InMemoryCourseRepository();
        $modules = new InMemoryCourseModuleRepository();
        $enrollments = new InMemoryEnrollmentRepository();
        $lessons = new InMemoryLessonRepository();
        $progress = new InMemoryLessonProgressRepository();

        $course = $courses->create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'status' => $status,
            'access_tier' => $accessTier,
        ]);
        $module = $modules->create([
            'course_id' => $course['id'],
            'title' => 'Test Module',
            'sort_order' => 1,
        ]);
        $lesson = $lessons->create([
            'module_id' => $module['id'],
            'course_id' => $course['id'],
            'title' => 'Lesson One',
            'content' => 'Lesson content',
            'sort_order' => 1,
        ]);

        if ($enroll) {
            $enrollments->create([
                'student_id' => 7,
                'course_id' => $course['id'],
            ]);
        }

        $service = new EnrollmentLearningService(
            $courses,
            $enrollments,
            $modules,
            $lessons,
            $progress,
            null
        );

        return compact('courses', 'modules', 'enrollments', 'lessons', 'progress', 'course', 'module', 'lesson', 'service');
    }

    public function testLessonCompletionPersistsForAnEnrolledStudent(): void
    {
        $fixture = $this->fixture();

        $result = $fixture['service']->markLessonComplete(7, (int) $fixture['lesson']['id']);

        self::assertTrue($result['success']);
        self::assertSame(1, (int) $fixture['progress']->findByStudentAndLesson(7, (int) $fixture['lesson']['id'])['completed']);

        $courseProgress = $fixture['service']->getCourseProgress(7, (int) $fixture['course']['id']);
        self::assertSame(1, $courseProgress['completed_lessons']);
        self::assertSame(1, $courseProgress['total_lessons']);
        self::assertSame(100.0, $courseProgress['percent']);
    }

    public function testLessonCompletionRequiresEnrollment(): void
    {
        $fixture = $this->fixture(enroll: false);

        $result = $fixture['service']->markLessonComplete(7, (int) $fixture['lesson']['id']);

        self::assertFalse($result['success']);
        self::assertSame('forbidden', $result['code']);
        self::assertNull($fixture['progress']->findByStudentAndLesson(7, (int) $fixture['lesson']['id']));
    }

    public function testLessonCompletionRejectsDraftAndPremiumCoursesWithoutActiveMembership(): void
    {
        $draft = $this->fixture(status: 'draft');
        $draftResult = $draft['service']->markLessonComplete(7, (int) $draft['lesson']['id']);
        self::assertFalse($draftResult['success']);
        self::assertSame('course_unavailable', $draftResult['code']);

        $premium = $this->fixture(accessTier: 'premium');
        $premiumResult = $premium['service']->markLessonComplete(7, (int) $premium['lesson']['id']);
        self::assertFalse($premiumResult['success']);
        self::assertSame('premium_required', $premiumResult['code']);
    }

    public function testCourseProgressCountsOnlyCompletedLessons(): void
    {
        $fixture = $this->fixture();
        $second = $fixture['lessons']->create([
            'module_id' => $fixture['module']['id'],
            'course_id' => $fixture['course']['id'],
            'title' => 'Lesson Two',
            'content' => 'More content',
            'sort_order' => 2,
        ]);
        $third = $fixture['lessons']->create([
            'module_id' => $fixture['module']['id'],
            'course_id' => $fixture['course']['id'],
            'title' => 'Lesson Three',
            'content' => 'Final content',
            'sort_order' => 3,
        ]);

        $fixture['progress']->createOrUpdate([
            'student_id' => 7,
            'course_id' => $fixture['course']['id'],
            'lesson_id' => $fixture['lesson']['id'],
            'completed' => 1,
        ]);
        $fixture['progress']->createOrUpdate([
            'student_id' => 7,
            'course_id' => $fixture['course']['id'],
            'lesson_id' => $second['id'],
            'completed' => 0,
        ]);

        $result = $fixture['service']->getCourseProgress(7, (int) $fixture['course']['id']);

        self::assertSame(1, $result['completed_lessons']);
        self::assertSame(3, $result['total_lessons']);
        self::assertSame(33.3, $result['percent']);
        self::assertNull($fixture['progress']->findByStudentAndLesson(7, (int) $third['id']));
    }
}
