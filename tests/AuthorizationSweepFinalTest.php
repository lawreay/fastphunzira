<?php

namespace App\Tests;

use App\Core\Auth;
use App\Repositories\InMemoryCourseModuleRepository;
use App\Repositories\InMemoryCourseRepository;
use App\Repositories\InMemoryEnrollmentRepository;
use App\Repositories\InMemoryLessonProgressRepository;
use App\Repositories\InMemoryLessonRepository;
use App\Repositories\InMemoryQuestionRepository;
use App\Repositories\InMemoryQuizAttemptRepository;
use App\Repositories\InMemoryQuizRepository;
use App\Services\CourseService;
use App\Services\EnrollmentLearningService;
use App\Services\QuizService;
use PHPUnit\Framework\TestCase;

final class AuthorizationSweepFinalTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        Auth::logout();
    }

    public function testCourseCreationRequiresActorToMatchAuthenticatedAdministrator(): void
    {
        $repository = new InMemoryCourseRepository();
        $service = new CourseService($repository);

        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $result = $service->createCourse([
            'title' => 'PHP Security',
            'slug' => 'php-security',
            'description' => 'Security course',
        ], 99);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
        $this->assertSame([], $repository->findAll());
    }

    public function testCourseUpdateCannotChangeCreatedBy(): void
    {
        $repository = new InMemoryCourseRepository();
        $course = $repository->create([
            'title' => 'PHP Security',
            'slug' => 'php-security',
            'description' => 'Security course',
            'status' => 'draft',
            'created_by' => 10,
        ]);
        $service = new CourseService($repository);

        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $result = $service->updateCourse((int) $course['id'], [
            'title' => 'Updated',
            'created_by' => 99,
        ], 10);

        $this->assertTrue($result['success']);
        $this->assertSame(10, (int) $result['data']['created_by']);
    }

    public function testModuleCreationRejectsSpoofedActor(): void
    {
        $courses = new InMemoryCourseRepository();
        $courses->create([
            'title' => 'PHP Foundations',
            'slug' => 'php-foundations',
            'description' => 'Published course',
            'status' => 'published',
            'created_by' => 10,
        ]);

        $service = new EnrollmentLearningService(
            $courses,
            new InMemoryEnrollmentRepository(),
            new InMemoryCourseModuleRepository(),
            new InMemoryLessonRepository(),
            new InMemoryLessonProgressRepository()
        );

        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $result = $service->createModule(1, [
            'title' => 'Introduction',
            'description' => 'Intro',
        ], 99);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
    }

    public function testLessonCreationRejectsSpoofedActor(): void
    {
        $courses = new InMemoryCourseRepository();
        $courses->create([
            'title' => 'PHP Foundations',
            'slug' => 'php-foundations',
            'description' => 'Published course',
            'status' => 'published',
            'created_by' => 10,
        ]);
        $modules = new InMemoryCourseModuleRepository();

        $service = new EnrollmentLearningService(
            $courses,
            new InMemoryEnrollmentRepository(),
            $modules,
            new InMemoryLessonRepository(),
            new InMemoryLessonProgressRepository()
        );

        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $module = $service->createModule(1, [
            'title' => 'Introduction',
            'description' => 'Intro',
        ], 10);
        $this->assertTrue($module['success']);

        $result = $service->createLesson((int) $module['data']['id'], [
            'title' => 'Lesson 1',
            'content' => 'Content',
        ], 99);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
    }

    public function testQuizCreationRejectsSpoofedActor(): void
    {
        $courses = new InMemoryCourseRepository();
        $courses->create([
            'title' => 'PHP Foundations',
            'slug' => 'php-foundations',
            'description' => 'Published course',
            'status' => 'published',
            'created_by' => 10,
        ]);
        $quizzes = new InMemoryQuizRepository();

        $service = new QuizService(
            $courses,
            new InMemoryEnrollmentRepository(),
            $quizzes,
            new InMemoryQuestionRepository(),
            new InMemoryQuizAttemptRepository()
        );

        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $result = $service->createQuiz(1, ['title' => 'Protected Quiz'], 99);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
    }

    public function testQuizQuestionCreationRejectsSpoofedActor(): void
    {
        $courses = new InMemoryCourseRepository();
        $courses->create([
            'title' => 'PHP Foundations',
            'slug' => 'php-foundations',
            'description' => 'Published course',
            'status' => 'published',
            'created_by' => 10,
        ]);
        $quizzes = new InMemoryQuizRepository();
        $questions = new InMemoryQuestionRepository();

        $service = new QuizService(
            $courses,
            new InMemoryEnrollmentRepository(),
            $quizzes,
            $questions,
            new InMemoryQuizAttemptRepository()
        );

        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $quiz = $service->createQuiz(1, ['title' => 'Protected Quiz'], 10);
        $this->assertTrue($quiz['success']);

        $result = $service->addQuestion((int) $quiz['data']['id'], [
            'question_text' => 'Test?',
            'options' => [
                ['option_text' => 'Yes', 'is_correct' => true],
                ['option_text' => 'No', 'is_correct' => false],
            ],
        ], 99);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
        $this->assertSame([], $questions->findByQuiz((int) $quiz['data']['id']));
    }
}
