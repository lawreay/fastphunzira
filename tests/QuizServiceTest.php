<?php

namespace App\Tests;

use App\Core\Auth;
use App\Repositories\InMemoryCourseRepository;
use App\Repositories\InMemoryEnrollmentRepository;
use App\Repositories\InMemoryQuestionRepository;
use App\Repositories\InMemoryQuizAttemptRepository;
use App\Repositories\InMemoryQuizRepository;
use App\Services\QuizService;
use PHPUnit\Framework\TestCase;

final class QuizServiceTest extends TestCase
{
    private QuizService $service;
    private InMemoryQuizRepository $quizzes;
    private InMemoryQuestionRepository $questions;
    private InMemoryQuizAttemptRepository $attempts;

    protected function setUp(): void
    {
        $_SESSION = [];
        Auth::logout();

        $courses = new InMemoryCourseRepository();
        $courses->create([
            'title' => 'PHP Foundations',
            'slug' => 'php-foundations',
            'description' => 'Published course',
            'status' => 'published',
            'created_by' => 10,
        ]);

        $enrollments = new InMemoryEnrollmentRepository();
        $this->quizzes = new InMemoryQuizRepository();
        $this->questions = new InMemoryQuestionRepository();
        $this->attempts = new InMemoryQuizAttemptRepository();

        $this->service = new QuizService(
            $courses,
            $enrollments,
            $this->quizzes,
            $this->questions,
            $this->attempts
        );

        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $quiz = $this->service->createQuiz(1, [
            'title' => 'PHP Practice',
            'description' => 'Practice quiz',
            'pass_percentage' => 50,
            'attempts_allowed' => 1,
            'status' => 'published',
        ], 10);
        $this->assertTrue($quiz['success']);
        $this->assertSame('draft', $quiz['data']['status']);

        $question = $this->service->addQuestion(1, [
            'question_text' => 'What does PHP stand for?',
            'options' => [
                ['option_text' => 'PHP: Hypertext Preprocessor', 'is_correct' => true],
                ['option_text' => 'Private Home Page', 'is_correct' => false],
                ['option_text' => 'Personal Hypertext Parser', 'is_correct' => false],
                ['option_text' => 'Programmed HTML Processor', 'is_correct' => false],
            ],
        ], 10);
        $this->assertTrue($question['success']);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $this->getEnrollmentRepository()->create([
            'student_id' => 20,
            'course_id' => 1,
            'status' => 'active',
        ]);
    }

    private function getEnrollmentRepository(): InMemoryEnrollmentRepository
    {
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('enrollmentRepository');
        $property->setAccessible(true);
        return $property->getValue($this->service);
    }

    public function testStudentCanAccessPublishedQuizAfterEnrollment(): void
    {
        $quiz = $this->service->getQuizForStudent(20, 1);

        $this->assertNotNull($quiz);
        $this->assertCount(1, $quiz['questions']);
        $this->assertArrayNotHasKey('is_correct', $quiz['questions'][0]['options'][0]);
    }

    public function testStudentCanStartAndSubmitQuiz(): void
    {
        $attempt = $this->service->startAttempt(20, 1);

        $this->assertTrue($attempt['success']);

        $question = $this->questions->findByQuiz(1)[0];
        $correctOption = $question['options'][0];

        $result = $this->service->submitAttempt(20, (int) $attempt['data']['id'], [
            $question['id'] => $correctOption['id'],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('submitted', $result['data']['status']);
        $this->assertSame(100.0, (float) $result['data']['percentage']);
        $this->assertSame(1, (int) $result['data']['passed']);
    }

    public function testAttemptLimitIsEnforced(): void
    {
        $attempt = $this->service->startAttempt(20, 1);
        $question = $this->questions->findByQuiz(1)[0];
        $this->service->submitAttempt(20, (int) $attempt['data']['id'], [
            $question['id'] => $question['options'][0]['id'],
        ]);

        $second = $this->service->startAttempt(20, 1);

        $this->assertFalse($second['success']);
        $this->assertSame('attempt_limit', $second['code']);
    }

    public function testStudentCannotAccessQuizWithoutEnrollment(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('enrollmentRepository');
        $property->setAccessible(true);
        $repo = $property->getValue($this->service);
        $repo->create([
            'student_id' => 30,
            'course_id' => 1,
            'status' => 'active',
        ]);

        $this->assertNull($this->service->getQuizForStudent(30, 1));
    }

    public function testDuplicateSubmissionIsRejected(): void
    {
        $attempt = $this->service->startAttempt(20, 1);
        $question = $this->questions->findByQuiz(1)[0];
        $answers = [$question['id'] => $question['options'][1]['id']];

        $this->assertTrue($this->service->submitAttempt(20, (int) $attempt['data']['id'], $answers)['success']);
        $second = $this->service->submitAttempt(20, (int) $attempt['data']['id'], $answers);

        $this->assertFalse($second['success']);
        $this->assertSame('already_submitted', $second['code']);
    }
}
