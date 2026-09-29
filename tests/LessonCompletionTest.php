<?php

namespace {
    if (!function_exists('base_url')) {
        function base_url(string $path = ''): string
        {
            return '/' . ltrim($path, '/');
        }
    }
}

namespace App\Tests {

use App\Core\Auth;
use App\Repositories\InMemoryCourseModuleRepository;
use App\Repositories\InMemoryCourseRepository;
use App\Repositories\InMemoryEnrollmentRepository;
use App\Repositories\InMemoryLessonProgressRepository;
use App\Repositories\InMemoryLessonRepository;
use App\Services\EnrollmentLearningService;
use PHPUnit\Framework\TestCase;

final class LessonCompletionTest extends TestCase
{
    private EnrollmentLearningService $service;
    private InMemoryEnrollmentRepository $enrollmentRepository;
    private InMemoryLessonProgressRepository $progressRepository;
    private int $firstLessonId;
    private int $secondLessonId;
    private int $laterModuleLessonId;

    protected function setUp(): void
    {
        $_SESSION = [];
        Auth::logout();

        $courseRepository = new InMemoryCourseRepository();
        $courseRepository->create([
            'title' => 'QA Fundamentals',
            'slug' => 'qa-fundamentals',
            'description' => 'Published course',
            'status' => 'published',
            'created_by' => 10,
        ]);

        $moduleRepository = new InMemoryCourseModuleRepository();
        $lessonRepository = new InMemoryLessonRepository();
        $this->enrollmentRepository = new InMemoryEnrollmentRepository();
        $this->progressRepository = new InMemoryLessonProgressRepository();
        $this->service = new EnrollmentLearningService(
            $courseRepository,
            $this->enrollmentRepository,
            $moduleRepository,
            $lessonRepository,
            $this->progressRepository
        );

        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $laterModule = $this->service->createModule(1, [
            'title' => 'Later module',
            'description' => 'Appears second in course order',
            'sort_order' => 20,
        ]);
        $laterLesson = $this->service->createLesson((int) $laterModule['data']['id'], [
            'title' => 'Later module lesson',
            'content' => 'Lesson in the later module',
            'sort_order' => 10,
        ]);

        $firstModule = $this->service->createModule(1, [
            'title' => 'First module',
            'description' => 'Appears first in course order',
            'sort_order' => 10,
        ]);
        $firstLesson = $this->service->createLesson((int) $firstModule['data']['id'], [
            'title' => 'First lesson',
            'content' => 'First lesson content',
            'sort_order' => 10,
        ]);
        $secondLesson = $this->service->createLesson((int) $firstModule['data']['id'], [
            'title' => 'Second lesson',
            'content' => 'Second lesson content',
            'sort_order' => 20,
        ]);

        $this->firstLessonId = (int) $firstLesson['data']['id'];
        $this->secondLessonId = (int) $secondLesson['data']['id'];
        $this->laterModuleLessonId = (int) $laterLesson['data']['id'];

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
    }

    public function testLoggedInStudentCanCompleteEnrolledLesson(): void
    {
        $this->enrollStudent();

        $result = $this->service->markLessonComplete(20, $this->firstLessonId);

        $this->assertTrue($result['success']);
        $this->assertSame('Lesson marked complete.', $result['message']);
    }

    public function testCompletionIsStoredWithDisplayableStatusAndTimestamp(): void
    {
        $this->enrollStudent();

        $this->service->markLessonComplete(20, $this->firstLessonId);
        $progress = $this->progressRepository->findByStudentAndLesson(20, $this->firstLessonId);

        $this->assertNotNull($progress);
        $this->assertSame(1, (int) $progress['completed']);
        $this->assertNotEmpty($progress['completed_at']);
    }

    public function testRepeatedCompletionDoesNotCreateDuplicateProgressRows(): void
    {
        $this->enrollStudent();

        $this->service->markLessonComplete(20, $this->firstLessonId);
        $this->service->markLessonComplete(20, $this->firstLessonId);

        $this->assertCount(1, $this->progressRepository->findByStudentAndCourse(20, 1));
    }

    public function testNonEnrolledStudentCannotCompleteLesson(): void
    {
        $result = $this->service->markLessonComplete(20, $this->firstLessonId);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
        $this->assertNull($this->progressRepository->findByStudentAndLesson(20, $this->firstLessonId));
    }

    public function testNextLessonUsesModuleAndLessonOrderInsteadOfIds(): void
    {
        $this->enrollStudent();

        $nextLesson = $this->service->getNextLessonForStudent(20, $this->secondLessonId);

        $this->assertNotNull($nextLesson);
        $this->assertSame($this->laterModuleLessonId, (int) $nextLesson['id']);
    }

    public function testLastLessonHasNoNextLesson(): void
    {
        $this->enrollStudent();

        $this->assertNull($this->service->getNextLessonForStudent(20, $this->laterModuleLessonId));
    }

    public function testCompletedLessonViewShowsStatusTimestampAndNextLesson(): void
    {
        $completedAt = '2026-09-29 09:30:00';
        $html = $this->renderLessonView([
            'course' => ['id' => 1, 'title' => 'QA Fundamentals'],
            'lesson' => ['id' => 12, 'title' => 'Completed lesson', 'content' => 'Lesson body'],
            'progress' => ['completed' => 1, 'completed_at' => $completedAt],
            'completed' => true,
            'nextLesson' => ['id' => 47, 'title' => 'Next lesson'],
            'materials' => [],
            'blocks' => [],
        ]);

        $this->assertStringContainsString('<strong>Lesson completed</strong>', $html);
        $this->assertStringContainsString('Completed ' . $completedAt, $html);
        $this->assertStringContainsString('href="/lessons/47"', $html);
        $this->assertStringContainsString('Continue to Next lesson', $html);
    }

    public function testIncompleteLessonViewPostsToStudentCompletionRoute(): void
    {
        $html = $this->renderLessonView([
            'course' => ['id' => 1, 'title' => 'QA Fundamentals'],
            'lesson' => ['id' => 12, 'title' => 'Incomplete lesson', 'content' => 'Lesson body'],
            'progress' => null,
            'completed' => false,
            'nextLesson' => null,
            'materials' => [],
            'blocks' => [],
        ]);

        $this->assertStringContainsString('action="/student/lessons/12/complete"', $html);
        $this->assertStringContainsString('name="_token"', $html);
    }

    private function renderLessonView(array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../resources/views/lessons/view.php';

        return (string) ob_get_clean();
    }

    private function enrollStudent(): void
    {
        $result = $this->service->enrollStudentInCourse(20, 1);
        $this->assertTrue($result['success']);
    }
}
}