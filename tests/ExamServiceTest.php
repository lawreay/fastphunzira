<?php

namespace App\Tests;

use App\Core\Auth;
use App\Repositories\InMemoryCourseRepository;
use App\Repositories\InMemoryEnrollmentRepository;
use App\Repositories\InMemoryExamAttemptRepository;
use App\Repositories\InMemoryExamRepository;
use App\Repositories\InMemoryQuestionRepository;
use App\Services\ExamService;
use PHPUnit\Framework\TestCase;

final class ExamServiceTest extends TestCase
{
    private InMemoryCourseRepository $courseRepository;
    private InMemoryEnrollmentRepository $enrollmentRepository;
    private InMemoryExamRepository $examRepository;
    private InMemoryQuestionRepository $questionRepository;
    private InMemoryExamAttemptRepository $attemptRepository;
    private ExamService $service;

    protected function setUp(): void
    {
        $_SESSION = [];
        Auth::logout();

        $this->courseRepository = new InMemoryCourseRepository();
        $this->courseRepository->create([
            'title' => 'PHP Foundations',
            'slug' => 'php-foundations',
            'description' => 'Published course',
            'status' => 'published',
            'created_by' => 10,
        ]);

        $this->enrollmentRepository = new InMemoryEnrollmentRepository();
        $this->enrollmentRepository->create([
            'student_id' => 20,
            'course_id' => 1,
            'enrolled_at' => date('Y-m-d H:i:s'),
        ]);

        $this->questionRepository = new InMemoryQuestionRepository();
        $this->examRepository = new InMemoryExamRepository();
        $this->attemptRepository = new InMemoryExamAttemptRepository();

        $this->service = new ExamService(
            $this->courseRepository,
            $this->enrollmentRepository,
            $this->examRepository,
            $this->questionRepository,
            $this->attemptRepository
        );
    }

    public function testAdminCanCreateExamAndAssignQuestion(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $exam = $this->service->createExam(1, [
            'title' => 'Midterm exam',
            'description' => 'Capstone assessment',
            'time_limit' => 60,
            'passing_score' => 70,
            'attempts_allowed' => 1,
        ]);

        $question = $this->service->addQuestion((int) $exam['data']['id'], [
            'question_text' => 'What does PHP stand for?',
            'marks' => 10,
            'options' => [
                ['option_text' => 'Personal Home Page', 'is_correct' => true],
                ['option_text' => 'Public Hyperlink Processor', 'is_correct' => false],
            ],
        ]);

        $this->assertTrue($exam['success']);
        $this->assertTrue($question['success']);
        $this->assertSame('Midterm exam', $exam['data']['title']);
    }

    public function testStudentMustBeEnrolledBeforeStartingExam(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $exam = $this->service->createExam(1, [
            'title' => 'Access exam',
            'description' => 'Needs enrollment',
            'time_limit' => 30,
            'passing_score' => 60,
            'attempts_allowed' => 1,
        ]);
        $this->service->addQuestion((int) $exam['data']['id'], [
            'question_text' => 'Which option is valid?',
            'marks' => 10,
            'options' => [
                ['option_text' => 'A', 'is_correct' => true],
                ['option_text' => 'B', 'is_correct' => false],
            ],
        ]);
        $this->service->publishExam((int) $exam['data']['id']);

        Auth::login(['id' => 30, 'email' => 'other@example.com', 'role' => 'student']);
        $result = $this->service->startAttempt(30, (int) $exam['data']['id']);

        $this->assertFalse($result['success']);
        $this->assertSame('forbidden', $result['code']);
    }

    public function testExamQuestionsAreSanitizedForStudents(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $exam = $this->service->createExam(1, [
            'title' => 'Exam with sanitized question',
            'description' => 'Question view',
            'time_limit' => 30,
            'passing_score' => 60,
            'attempts_allowed' => 1,
        ]);
        $this->service->addQuestion((int) $exam['data']['id'], [
            'question_text' => 'Which option is correct?',
            'marks' => 1,
            'options' => [
                ['option_text' => 'Option A', 'is_correct' => true],
                ['option_text' => 'Option B', 'is_correct' => false],
            ],
        ]);
        $this->service->publishExam((int) $exam['data']['id']);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $view = $this->service->getExamForStudent(20, (int) $exam['data']['id']);

        $this->assertNotNull($view);
        $this->assertArrayNotHasKey('is_correct', $view['questions'][0]['options'][0]);
    }

    public function testStudentCanStartAttemptWithServerExpiry(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $exam = $this->service->createExam(1, [
            'title' => 'Timed exam',
            'description' => 'Server controlled',
            'time_limit' => 15,
            'passing_score' => 60,
            'attempts_allowed' => 1,
        ]);
        $this->service->addQuestion((int) $exam['data']['id'], [
            'question_text' => 'What is 1 + 1?',
            'marks' => 5,
            'options' => [
                ['option_text' => '2', 'is_correct' => true],
                ['option_text' => '3', 'is_correct' => false],
            ],
        ]);
        $this->service->publishExam((int) $exam['data']['id']);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $result = $this->service->startAttempt(20, (int) $exam['data']['id']);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['data']['expires_at']);
        $this->assertSame('in_progress', $result['data']['status']);
    }

    public function testInvalidOptionAndWrongExamQuestionAreRejected(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $exam = $this->service->createExam(1, [
            'title' => 'Validation exam',
            'description' => 'QA',
            'time_limit' => 45,
            'passing_score' => 60,
            'attempts_allowed' => 1,
        ]);
        $this->service->addQuestion((int) $exam['data']['id'], [
            'question_text' => 'What is 2 + 2?',
            'marks' => 10,
            'options' => [
                ['option_text' => '4', 'is_correct' => true],
                ['option_text' => '5', 'is_correct' => false],
            ],
        ]);
        $this->service->publishExam((int) $exam['data']['id']);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $attempt = $this->service->startAttempt(20, (int) $exam['data']['id'])['data'];

        $badOption = $this->service->submitAttempt(20, (int) $attempt['id'], [
            ['question_id' => 999, 'selected_option_id' => 1],
        ]);
        $this->assertFalse($badOption['success']);
        $this->assertSame('invalid_question', $badOption['code']);
    }

    public function testExpiredAttemptAutoSubmitsAndCalculatesResult(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);
        $exam = $this->service->createExam(1, [
            'title' => 'Expired exam',
            'description' => 'Loop',
            'time_limit' => 1,
            'passing_score' => 60,
            'attempts_allowed' => 1,
        ]);
        $this->service->addQuestion((int) $exam['data']['id'], [
            'question_text' => 'Capital of France?',
            'marks' => 10,
            'options' => [
                ['option_text' => 'Paris', 'is_correct' => true],
                ['option_text' => 'Rome', 'is_correct' => false],
            ],
        ]);
        $this->service->publishExam((int) $exam['data']['id']);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $attempt = $this->attemptRepository->create([
            'exam_id' => (int) $exam['data']['id'],
            'user_id' => 20,
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s', time() - 120),
            'expires_at' => date('Y-m-d H:i:s', time() - 5),
        ]);

        $result = $this->service->submitAttempt(20, (int) $attempt['id'], [
            ['question_id' => 1, 'selected_option_id' => 1],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(100.0, $result['data']['percentage']);
        $this->assertTrue((bool) $result['data']['passed']);
    }

    public function testStudentsCannotCreateOrPublishExams(): void
    {
        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);

        $created = $this->service->createExam(1, [
            'title' => 'Unauthorized exam',
            'time_limit' => 30,
            'passing_score' => 60,
            'attempts_allowed' => 1,
        ]);

        $this->assertFalse($created['success']);
        $this->assertSame('forbidden', $created['code']);
    }

    public function testDuplicateSubmissionIsRejectedAfterFinalization(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $exam = $this->service->createExam(1, [
            'title' => 'Duplicate submission exam',
            'time_limit' => 30,
            'passing_score' => 50,
            'attempts_allowed' => 1,
        ]);

        $this->service->addQuestion((int) $exam['data']['id'], [
            'question_text' => 'Pick A',
            'marks' => 1,
            'options' => [
                ['option_text' => 'A', 'is_correct' => true],
                ['option_text' => 'B', 'is_correct' => false],
            ],
        ]);
        $this->service->publishExam((int) $exam['data']['id']);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $attempt = $this->service->startAttempt(20, (int) $exam['data']['id'])['data'];

        $first = $this->service->submitAttempt(20, (int) $attempt['id'], [
            ['question_id' => 1, 'selected_option_id' => 1],
        ]);
        $second = $this->service->submitAttempt(20, (int) $attempt['id'], [
            ['question_id' => 1, 'selected_option_id' => 1],
        ]);

        $this->assertTrue($first['success']);
        $this->assertFalse($second['success']);
        $this->assertSame('already_submitted', $second['code']);
    }

    public function testInvalidQuestionDoesNotPersistEarlierAnswers(): void
    {
        Auth::login(['id' => 10, 'email' => 'admin@example.com', 'role' => 'admin']);

        $exam = $this->service->createExam(1, [
            'title' => 'Atomic validation exam',
            'time_limit' => 30,
            'passing_score' => 50,
            'attempts_allowed' => 1,
        ]);

        $this->service->addQuestion((int) $exam['data']['id'], [
            'question_text' => 'Pick A',
            'marks' => 1,
            'options' => [
                ['option_text' => 'A', 'is_correct' => true],
                ['option_text' => 'B', 'is_correct' => false],
            ],
        ]);
        $this->service->publishExam((int) $exam['data']['id']);

        Auth::login(['id' => 20, 'email' => 'student@example.com', 'role' => 'student']);
        $attempt = $this->service->startAttempt(20, (int) $exam['data']['id'])['data'];

        $result = $this->service->submitAttempt(20, (int) $attempt['id'], [
            ['question_id' => 1, 'selected_option_id' => 1],
            ['question_id' => 999, 'selected_option_id' => 1],
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('invalid_question', $result['code']);
        $this->assertCount(0, $this->attemptRepository->findAnswers((int) $attempt['id']));
    }

}
