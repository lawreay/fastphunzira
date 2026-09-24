<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\CourseRepositoryInterface;
use App\Repositories\EnrollmentRepositoryInterface;
use App\Repositories\ExamAttemptRepositoryInterface;
use App\Repositories\ExamRepositoryInterface;
use App\Repositories\QuestionRepositoryInterface;
use DateTimeImmutable;

final class ExamService
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        private EnrollmentRepositoryInterface $enrollmentRepository,
        private ExamRepositoryInterface $examRepository,
        private QuestionRepositoryInterface $questionRepository,
        private ExamAttemptRepositoryInterface $attemptRepository
    ) {
    }

    public function createExam(int $courseId, array $data, ?int $actorId = null): array
    {
        if (!Auth::userCan('courses.manage')) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can manage exams.'];
        }

        $course = $this->courseRepository->findById($courseId);
        if ($course === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Course not found.'];
        }

        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'Exam title is required.'];
        }

        $timeLimit = (int) ($data['time_limit'] ?? 60);
        $passingScore = (float) ($data['passing_score'] ?? 70);
        $attemptsAllowed = (int) ($data['attempts_allowed'] ?? 1);

        if ($timeLimit <= 0 || $passingScore < 0 || $passingScore > 100 || $attemptsAllowed < 1) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'Exam settings are invalid.'];
        }

        $exam = $this->examRepository->create([
            'course_id' => $courseId,
            'title' => $title,
            'description' => trim((string) ($data['description'] ?? '')),
            'time_limit' => $timeLimit,
            'passing_score' => $passingScore,
            'attempts_allowed' => $attemptsAllowed,
            'status' => 'draft',
            'created_by' => $actorId ?? (int) Auth::userId(),
        ]);

        return ['success' => true, 'message' => 'Exam created.', 'data' => $exam];
    }

    public function publishExam(int $examId): array
    {
        if (!Auth::userCan('courses.manage')) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can publish exams.'];
        }

        $exam = $this->examRepository->findById($examId);
        if ($exam === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Exam not found.'];
        }

        if (count($this->examRepository->findQuestions($examId)) === 0) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'An exam must contain at least one question before publishing.'];
        }

        $updated = $this->examRepository->updateStatus($examId, 'published');

        return [
            'success' => $updated !== null,
            'message' => $updated !== null ? 'Exam published.' : 'Exam could not be published.',
            'data' => $updated,
        ];
    }

    public function addQuestion(int $examId, array $data): array
    {
        if (!Auth::userCan('courses.manage')) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can manage exam questions.'];
        }

        $exam = $this->examRepository->findById($examId);
        if ($exam === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Exam not found.'];
        }

        if (strtolower((string) ($exam['status'] ?? 'draft')) === 'published') {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Published exams cannot be edited.'];
        }

        $questionText = trim((string) ($data['question_text'] ?? ''));
        $marks = (float) ($data['marks'] ?? 1);
        $options = $data['options'] ?? [];

        if ($questionText === '' || !is_array($options) || count($options) < 2 || $marks <= 0) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'A question and at least two options are required.'];
        }

        $correctOptions = 0;
        foreach ($options as $option) {
            if (trim((string) ($option['option_text'] ?? '')) === '') {
                return ['success' => false, 'code' => 'validation_failed', 'message' => 'All options must contain text.'];
            }

            if (!empty($option['is_correct'])) {
                $correctOptions++;
            }
        }

        if ($correctOptions !== 1) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'Each question must have exactly one correct answer.'];
        }

        $question = $this->questionRepository->create([
            'question_text' => $questionText,
            'question_type' => 'multiple_choice',
            'created_by' => (int) Auth::userId(),
        ]);

        foreach (array_values($options) as $index => $option) {
            $this->questionRepository->addOption((int) $question['id'], [
                'option_text' => trim((string) ($option['option_text'] ?? '')),
                'is_correct' => !empty($option['is_correct']),
                'sort_order' => $index + 1,
            ]);
        }

        $this->examRepository->addQuestion($examId, (int) $question['id'], $marks, count($this->examRepository->findQuestions($examId)) + 1);

        return ['success' => true, 'message' => 'Question added to exam.', 'data' => $question];
    }

    public function getExamForStudent(int $studentId, int $examId): ?array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return null;
        }

        $exam = $this->examRepository->findById($examId);
        if ($exam === null || strtolower((string) ($exam['status'] ?? 'draft')) !== 'published') {
            return null;
        }

        $course = $this->courseRepository->findById((int) ($exam['course_id'] ?? 0));
        if ($course === null || strtolower((string) ($course['status'] ?? 'draft')) !== 'published') {
            return null;
        }

        if ($this->enrollmentRepository->findByStudentAndCourse($studentId, (int) ($exam['course_id'] ?? 0)) === null) {
            return null;
        }

        $exam['questions'] = [];
        foreach ($this->examRepository->findQuestions($examId) as $link) {
            $question = $this->questionRepository->findById((int) $link['question_id']);
            if ($question === null) {
                continue;
            }

            foreach (($question['options'] ?? []) as $index => $option) {
                unset($question['options'][$index]['is_correct']);
            }

            $exam['questions'][] = $question;
        }

        return $exam;
    }

    public function startAttempt(int $studentId, int $examId): array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'You must be logged in as the student to start this exam.'];
        }

        $exam = $this->getExamForStudent($studentId, $examId);
        if ($exam === null) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Exam is not available to this student.'];
        }

        if ($this->attemptRepository->findActiveByStudentAndExam($studentId, $examId) !== null) {
            return ['success' => false, 'code' => 'duplicate_attempt', 'message' => 'You already have an active attempt for this exam.'];
        }

        $attemptCount = $this->attemptRepository->countByStudentAndExam($studentId, $examId);
        $limit = (int) ($exam['attempts_allowed'] ?? 1);
        if ($attemptCount >= $limit) {
            return ['success' => false, 'code' => 'attempt_limit', 'message' => 'You have used all attempts for this exam.'];
        }

        $startedAt = new DateTimeImmutable('now');
        $expiresAt = $startedAt->modify('+' . max(1, (int) ($exam['time_limit'] ?? 60)) . ' minutes');

        $attempt = $this->attemptRepository->create([
            'exam_id' => $examId,
            'user_id' => $studentId,
            'status' => 'in_progress',
            'started_at' => $startedAt->format('Y-m-d H:i:s'),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'score' => 0,
            'percentage' => 0,
            'passed' => 0,
        ]);

        return ['success' => true, 'message' => 'Exam attempt started.', 'data' => $attempt];
    }

    public function saveAnswer(int $studentId, int $attemptId, array $answer): array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'You are not allowed to answer this exam.'];
        }

        $attempt = $this->attemptRepository->findById($attemptId);
        if ($attempt === null || (int) ($attempt['user_id'] ?? 0) !== $studentId) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Attempt not found.'];
        }

        if (($attempt['status'] ?? '') !== 'in_progress') {
            return ['success' => false, 'code' => 'already_submitted', 'message' => 'This exam attempt has already been finalized.'];
        }

        if ($this->attemptExpired($attempt)) {
            return $this->submitAttempt($studentId, $attemptId, []);
        }

        $questionId = (int) ($answer['question_id'] ?? 0);
        if ($questionId <= 0) {
            return ['success' => false, 'code' => 'invalid_question', 'message' => 'Question is required.'];
        }

        $exam = $this->examRepository->findById((int) ($attempt['exam_id'] ?? 0));
        if ($exam === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Exam not found.'];
        }

        $validQuestionIds = [];
        foreach ($this->examRepository->findQuestions((int) $exam['id']) as $examQuestion) {
            $validQuestionIds[(int) $examQuestion['question_id']] = $examQuestion;
        }

        if (!isset($validQuestionIds[$questionId])) {
            return ['success' => false, 'code' => 'invalid_question', 'message' => 'This question does not belong to the exam.'];
        }

        $question = $this->questionRepository->findById($questionId);
        if ($question === null) {
            return ['success' => false, 'code' => 'invalid_question', 'message' => 'Question was not found.'];
        }

        $selectedOptionId = (int) ($answer['selected_option_id'] ?? 0);
        $selectedOption = null;
        foreach ($question['options'] ?? [] as $option) {
            if ((int) ($option['id'] ?? 0) === $selectedOptionId) {
                $selectedOption = $option;
                break;
            }
        }

        if ($selectedOption === null) {
            return ['success' => false, 'code' => 'invalid_option', 'message' => 'Selected option is not valid for this question.'];
        }

        $saved = $this->attemptRepository->saveAnswer([
            'exam_attempt_id' => $attemptId,
            'question_id' => $questionId,
            'selected_option_id' => $selectedOptionId,
            'answer_text' => $answer['answer_text'] ?? null,
            'is_correct' => !empty($selectedOption['is_correct']),
        ]);

        return ['success' => true, 'message' => 'Answer saved.', 'data' => $saved];
    }

    public function submitAttempt(int $studentId, int $attemptId, array $answers): array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'You are not allowed to submit this exam.'];
        }

        $attempt = $this->attemptRepository->findById($attemptId);
        if ($attempt === null || (int) ($attempt['user_id'] ?? 0) !== $studentId) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Attempt not found.'];
        }

        if (($attempt['status'] ?? '') !== 'in_progress') {
            return ['success' => false, 'code' => 'already_submitted', 'message' => 'This exam attempt has already been submitted.'];
        }

        $exam = $this->examRepository->findById((int) ($attempt['exam_id'] ?? 0));
        if ($exam === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Exam not found.'];
        }

        $autoSubmitted = $this->attemptExpired($attempt);
        $score = 0.0;
        $totalMarks = 0.0;
        $normalized = $this->normalizeAnswers($answers);
        $validQuestionIds = [];

        foreach ($this->examRepository->findQuestions((int) $exam['id']) as $examQuestion) {
            $questionId = (int) $examQuestion['question_id'];
            $validQuestionIds[$questionId] = $examQuestion;
            $totalMarks += (float) ($examQuestion['marks'] ?? 1);
        }

        foreach ($normalized as $questionId => $answer) {
            if (!isset($validQuestionIds[(int) $questionId])) {
                return ['success' => false, 'code' => 'invalid_question', 'message' => 'Question does not belong to this exam.'];
            }
        }

        foreach ($validQuestionIds as $questionId => $examQuestion) {
            $answer = $normalized[$questionId] ?? null;
            if ($answer === null) {
                continue;
            }

            $question = $this->questionRepository->findById($questionId);
            if ($question === null) {
                return ['success' => false, 'code' => 'invalid_question', 'message' => 'Question does not belong to this exam.'];
            }

            $selectedOptionId = (int) ($answer['selected_option_id'] ?? 0);
            if ($selectedOptionId <= 0) {
                return ['success' => false, 'code' => 'invalid_option', 'message' => 'Selected option is invalid.'];
            }

            $validOption = null;
            foreach ($question['options'] ?? [] as $option) {
                if ((int) ($option['id'] ?? 0) === $selectedOptionId) {
                    $validOption = $option;
                    break;
                }
            }

            if ($validOption === null) {
                return ['success' => false, 'code' => 'invalid_option', 'message' => 'Selected option is not valid for this question.'];
            }

            $isCorrect = !empty($validOption['is_correct']);
            if ($isCorrect) {
                $score += (float) ($examQuestion['marks'] ?? 1);
            }

            $this->attemptRepository->saveAnswer([
                'exam_attempt_id' => $attemptId,
                'question_id' => $questionId,
                'selected_option_id' => $selectedOptionId,
                'answer_text' => $answer['answer_text'] ?? null,
                'is_correct' => $isCorrect,
            ]);
        }

        $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100, 1) : 0.0;
        $passed = $percentage >= (float) ($exam['passing_score'] ?? 50);

        $result = $this->attemptRepository->finalize($attemptId, [
            'score' => $score,
            'percentage' => $percentage,
            'passed' => $passed,
            'status' => $autoSubmitted ? 'submitted' : 'submitted',
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'message' => $autoSubmitted ? 'Exam expired and was auto-submitted.' : 'Exam submitted successfully.',
            'data' => $result,
            'auto_submitted' => $autoSubmitted,
        ];
    }

    public function getAttemptResult(int $studentId, int $attemptId): ?array
    {
        $attempt = $this->attemptRepository->findById($attemptId);
        if ($attempt === null || (int) ($attempt['user_id'] ?? 0) !== $studentId) {
            return null;
        }

        return $attempt;
    }

    private function normalizeAnswers(array $answers): array
    {
        $normalized = [];
        foreach ($answers as $answer) {
            if (!is_array($answer)) {
                continue;
            }

            $questionId = (int) ($answer['question_id'] ?? 0);
            if ($questionId <= 0) {
                continue;
            }

            $normalized[$questionId] = [
                'question_id' => $questionId,
                'selected_option_id' => (int) ($answer['selected_option_id'] ?? 0),
                'answer_text' => $answer['answer_text'] ?? null,
            ];
        }

        return $normalized;
    }

    private function attemptExpired(array $attempt): bool
    {
        $expiresAt = $attempt['expires_at'] ?? null;
        if ($expiresAt === null || $expiresAt === '') {
            return false;
        }

        try {
            $expires = new DateTimeImmutable((string) $expiresAt);
            return $expires <= new DateTimeImmutable('now');
        } catch (\Exception $e) {
            return false;
        }
    }
}
