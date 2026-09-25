<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\CourseRepositoryInterface;
use App\Repositories\EnrollmentRepositoryInterface;
use App\Repositories\QuestionRepositoryInterface;
use App\Repositories\QuizAttemptRepositoryInterface;
use App\Repositories\QuizRepositoryInterface;

final class QuizService
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        private EnrollmentRepositoryInterface $enrollmentRepository,
        private QuizRepositoryInterface $quizRepository,
        private QuestionRepositoryInterface $questionRepository,
        private QuizAttemptRepositoryInterface $attemptRepository
    ) {
    }

    public function createQuiz(int $courseId, array $data, ?int $actorId = null): array
    {
        if (!Auth::userCan('courses.manage')) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can manage quizzes.'];
        }

        $course = $this->courseRepository->findById($courseId);
        if ($course === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Course not found.'];
        }

        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'Quiz title is required.'];
        }

        $passPercentage = (float) ($data['pass_percentage'] ?? 50);
        $attemptsAllowed = (int) ($data['attempts_allowed'] ?? 1);

        if ($passPercentage < 0 || $passPercentage > 100 || $attemptsAllowed < 1) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'Quiz settings are invalid.'];
        }

        $quiz = $this->quizRepository->create([
            'course_id' => $courseId,
            'title' => $title,
            'description' => trim((string) ($data['description'] ?? '')),
            'pass_percentage' => $passPercentage,
            'attempts_allowed' => $attemptsAllowed,
            'status' => 'draft',
            'created_by' => $actorId ?? (int) Auth::userId(),
        ]);

        return ['success' => true, 'message' => 'Quiz created.', 'data' => $quiz];
    }


    public function publishQuiz(int $quizId): array
    {
        if (!Auth::userCan('courses.manage')) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can publish quizzes.'];
        }

        $quiz = $this->quizRepository->findById($quizId);
        if ($quiz === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Quiz not found.'];
        }

        if (count($this->questionRepository->findByQuiz($quizId)) === 0) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'A quiz must contain at least one question before publishing.'];
        }

        $updated = $this->quizRepository->updateStatus($quizId, 'published');

        return [
            'success' => $updated !== null,
            'message' => $updated !== null ? 'Quiz published.' : 'Quiz could not be published.',
            'data' => $updated,
        ];
    }

    public function addQuestion(int $quizId, array $data, ?int $actorId = null): array
    {
        if (!Auth::userCan('courses.manage')) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can manage questions.'];
        }

        $quiz = $this->quizRepository->findById($quizId);
        if ($quiz === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Quiz not found.'];
        }

        if (strtolower((string) ($quiz['status'] ?? 'draft')) === 'published') {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Published quizzes cannot be edited.'];
        }

        $text = trim((string) ($data['question_text'] ?? ''));
        $options = $data['options'] ?? [];
        if ($text === '' || !is_array($options) || count($options) < 2) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'A question and at least two options are required.'];
        }

        foreach ($options as $option) {
            if (trim((string) ($option['option_text'] ?? '')) === '') {
                return ['success' => false, 'code' => 'validation_failed', 'message' => 'All question options must contain text.'];
            }
        }

        $correctCount = 0;
        foreach ($options as $option) {
            $optionText = trim((string) ($option['option_text'] ?? ''));
            if ($optionText === '') {
                return ['success' => false, 'code' => 'validation_failed', 'message' => 'Each answer option must contain text.'];
            }

            if (!empty($option['is_correct'])) {
                $correctCount++;
            }
        }

        if ($correctCount !== 1) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'Each multiple-choice question must have exactly one correct option.'];
        }

        $question = $this->questionRepository->create([
            'question_text' => $text,
            'question_type' => 'multiple_choice',
            'created_by' => $actorId ?? (int) Auth::userId(),
        ]);

        foreach (array_values($options) as $index => $option) {
            $this->questionRepository->addOption((int) $question['id'], [
                'option_text' => trim((string) ($option['option_text'] ?? '')),
                'is_correct' => !empty($option['is_correct']),
                'sort_order' => $index + 1,
            ]);
        }

        $this->questionRepository->attachToQuiz($quizId, (int) $question['id'], count($this->questionRepository->findByQuiz($quizId)) + 1);

        return ['success' => true, 'message' => 'Question added.', 'data' => $this->questionRepository->findById((int) $question['id'])];
    }

    public function getQuizForStudent(int $studentId, int $quizId): ?array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return null;
        }

        $quiz = $this->quizRepository->findById($quizId);
        if ($quiz === null || strtolower((string) ($quiz['status'] ?? 'draft')) !== 'published') {
            return null;
        }

        $course = $this->courseRepository->findById((int) $quiz['course_id']);
        if ($course === null || strtolower((string) ($course['status'] ?? 'draft')) !== 'published') {
            return null;
        }

        if ($this->enrollmentRepository->findByStudentAndCourse($studentId, (int) $quiz['course_id']) === null) {
            return null;
        }

        $quiz['questions'] = $this->questionRepository->findByQuiz($quizId);
        foreach ($quiz['questions'] as $questionIndex => $question) {
            $sanitizedOptions = [];
            foreach (($question['options'] ?? []) as $option) {
                $sanitized = $option;
                unset($sanitized['is_correct']);
                $sanitizedOptions[] = $sanitized;
            }
            $quiz['questions'][$questionIndex]['options'] = $sanitizedOptions;
        }

        return $quiz;
    }

    public function startAttempt(int $studentId, int $quizId): array
    {
        $quiz = $this->getQuizForStudent($studentId, $quizId);
        if ($quiz === null) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Quiz is not available to this student.'];
        }

        $active = $this->attemptRepository->findActiveByStudentAndQuiz($studentId, $quizId);
        if ($active !== null) {
            return ['success' => true, 'message' => 'Existing quiz attempt resumed.', 'data' => $active];
        }

        $attempts = $this->attemptRepository->countByStudentAndQuiz($studentId, $quizId);
        $limit = (int) ($quiz['attempts_allowed'] ?? 1);
        if ($attempts >= $limit) {
            return ['success' => false, 'code' => 'attempt_limit', 'message' => 'You have used all allowed attempts for this quiz.'];
        }

        $attempt = $this->attemptRepository->create([
            'quiz_id' => $quizId,
            'user_id' => $studentId,
            'attempt_number' => $attempts + 1,
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'message' => 'Quiz attempt started.', 'data' => $attempt];
    }

    public function submitAttempt(int $studentId, int $attemptId, array $answers): array
    {
        $attempt = $this->attemptRepository->findById($attemptId);
        if ($attempt === null || (int) $attempt['user_id'] !== $studentId) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Quiz attempt not found.'];
        }

        if (($attempt['status'] ?? '') !== 'in_progress') {
            return ['success' => false, 'code' => 'already_submitted', 'message' => 'This quiz attempt has already been submitted.'];
        }

        $quiz = $this->getQuizForStudent($studentId, (int) $attempt['quiz_id']);
        if ($quiz === null) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Quiz is no longer available.'];
        }

        $rawQuestions = $this->questionRepository->findByQuiz((int) $attempt['quiz_id']);
        $questions = [];
        foreach ($rawQuestions as $question) {
            $questions[(int) $question['id']] = $question;
        }

        $score = 0;
        foreach ($questions as $questionId => $question) {
            $optionId = isset($answers[$questionId]) ? (int) $answers[$questionId] : 0;
            $validOption = null;

            foreach (($question['options'] ?? []) as $option) {
                if ((int) $option['id'] === $optionId) {
                    $validOption = $option;
                    break;
                }
            }

            $isCorrect = $validOption !== null && !empty($validOption['is_correct']);
            if ($isCorrect) {
                $score++;
            }

            if ($optionId > 0 && $validOption !== null) {
                $this->attemptRepository->saveAnswer([
                    'attempt_id' => $attemptId,
                    'question_id' => $questionId,
                    'option_id' => $optionId,
                    'is_correct' => $isCorrect,
                ]);
            }
        }

        $total = count($questions);
        $percentage = $total === 0 ? 0.0 : round(($score / $total) * 100, 1);
        $passed = $percentage >= (float) ($quiz['pass_percentage'] ?? 50);

        $finalized = $this->attemptRepository->finalize($attemptId, [
            'score' => $score,
            'percentage' => $percentage,
            'passed' => $passed,
        ]);

        return [
            'success' => $finalized !== null,
            'message' => $passed ? 'Quiz passed.' : 'Quiz submitted.',
            'data' => $finalized,
        ];
    }

    public function getAttemptForStudent(int $studentId, int $attemptId): ?array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return null;
        }

        $attempt = $this->attemptRepository->findById($attemptId);
        if ($attempt === null || (int) $attempt['user_id'] !== $studentId) {
            return null;
        }

        $attempt['answers'] = $this->attemptRepository->findAnswers($attemptId);
        return $attempt;
    }
}
