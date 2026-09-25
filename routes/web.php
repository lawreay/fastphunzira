<?php

$app = require __DIR__ . '/../bootstrap/app.php';
$authService = $app['auth'];
$courseController = $app['courseController'];
$courseRepository = $app['courseRepository'];
$moduleRepository = $app['moduleRepository'];
$lessonRepository = $app['lessonRepository'];
$enrollmentRepository = $app['enrollmentRepository'];
$progressRepository = $app['progressRepository'];
$learningService = $app['enrollmentLearningService'];
$quizService = $app['quizService'];
$quizAttemptRepository = $app['quizAttemptRepository'];
$examRepository = $app['examRepository'];
$examAttemptRepository = $app['examAttemptRepository'];
$examService = $app['examService'];
$auditLogService = $app['auditLogService'];
$certificateService = $app['certificateService'];

use App\Core\Auth;
use App\Support\Csrf;

return [
    ['GET', '/', function () {
        return [
            'view' => 'landing',
            'title' => 'FastPhunzira',
        ];
    }],
    ['GET', '/courses', [$courseController, 'catalogue']],
    ['GET', '/courses/{id}', [$courseController, 'detail']],
    ['GET', '/admin/courses', [$courseController, 'adminIndex']],
    ['GET', '/admin/courses/create', [$courseController, 'createForm']],
    ['GET', '/admin/courses/{id}/edit', [$courseController, 'editForm']],
    ['POST', '/admin/courses/store', function () use ($courseController) {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/admin/courses');
        }

        return $courseController->store($_POST);
    }],
    ['POST', '/admin/courses/update', function () use ($courseController) {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/admin/courses');
        }

        $id = (int) ($_POST['id'] ?? 0);

        return $courseController->update($id, $_POST);
    }],
    ['POST', '/admin/courses/publish', function () use ($courseController) {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/admin/courses');
        }

        return $courseController->publish();
    }],
    ['GET', '/login', function () {
        return [
            'view' => 'auth/login',
            'title' => 'Login',
        ];
    }],
    ['POST', '/login', function () use ($authService, $auditLogService) {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $token = $_POST['_token'] ?? null;

        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/login');
        }

        $result = $authService->login(['email' => $email, 'password' => $password]);

        if (!$result['success']) {
            $auditLogService->record([
                'user_id' => null,
                'action' => 'login_failed',
                'entity_type' => 'user',
                'entity_id' => null,
                'details' => ['email' => $email],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            $_SESSION['flash_error'] = $result['message'];

            return redirect_to('/login');
        }

        $auditLogService->record([
            'user_id' => (int) ($result['data']['id'] ?? 0),
            'action' => 'login_success',
            'entity_type' => 'user',
            'entity_id' => (int) ($result['data']['id'] ?? 0),
            'details' => ['email' => $email],
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        $_SESSION['flash_success'] = 'Welcome back!';

        return redirect_to('/dashboard');
    }],
    ['GET', '/register', function () {
        return [
            'view' => 'auth/register',
            'title' => 'Register',
        ];
    }],
    ['POST', '/register', function () use ($authService) {
        $token = $_POST['_token'] ?? null;

        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/register');
        }

        $result = $authService->register([
            'full_name' => trim((string) ($_POST['full_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ]);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['errors'][0]['message'] ?? $result['message'];

            return redirect_to('/register');
        }

        $_SESSION['flash_success'] = 'Registration successful. Please log in.';

        return redirect_to('/login');
    }],
    ['POST', '/logout', function () use ($authService) {
        $token = $_POST['_token'] ?? null;

        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/dashboard');
        }

        $authService->logout();
        $_SESSION['flash_success'] = 'You have been logged out.';

        return redirect_to('/login');
    }],
    ['POST', '/courses/{id}/enroll', function (string $courseId) use ($learningService) {
        $studentId = Auth::userId();

        if ($studentId === null) {
            $_SESSION['flash_error'] = 'Please log in to enroll in a course.';

            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/courses/' . (int) $courseId);
        }

        $result = $learningService->enrollStudentInCourse($studentId, (int) $courseId);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return redirect_to('/courses/' . (int) $courseId);
        }

        $_SESSION['flash_success'] = 'You have been enrolled successfully.';

        return redirect_to('/my-courses');
    }],
    ['GET', '/my-courses', function () use ($courseRepository, $learningService) {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';

            return redirect_to('/login');
        }

        $studentId = (int) Auth::userId();
        $enrollments = $learningService->getStudentEnrollments($studentId);
        $courses = [];

        foreach ($enrollments as $enrollment) {
            $courseId = (int) ($enrollment['course_id'] ?? 0);
            $course = $courseRepository->findById($courseId);

            if ($course === null) {
                continue;
            }

            $courses[] = [
                'course' => $course,
                'progress' => $learningService->getCourseProgress($studentId, $courseId),
            ];
        }

        return [
            'view' => 'student/my-courses',
            'title' => 'My Courses',
            'courses' => $courses,
        ];
    }],
    ['GET', '/courses/{id}/learn', function (string $courseId) use ($courseRepository, $moduleRepository, $lessonRepository, $enrollmentRepository, $learningService) {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to access course content.';

            return redirect_to('/login');
        }

        $studentId = (int) Auth::userId();
        $course = $courseRepository->findById((int) $courseId);

        if ($course === null) {
            return ['view' => 'errors/not_found', 'title' => 'Course not found'];
        }

        if ($enrollmentRepository->findByStudentAndCourse($studentId, (int) $courseId) === null) {
            $_SESSION['flash_error'] = 'You must enroll in this course before accessing lessons.';

            return redirect_to('/courses/' . (int) $courseId);
        }

        $modules = $moduleRepository->findByCourse((int) $courseId);
        $moduleData = [];
        foreach ($modules as $module) {
            $moduleData[] = [
                'module' => $module,
                'lessons' => $lessonRepository->findByModule((int) ($module['id'] ?? 0)),
            ];
        }

        return [
            'view' => 'courses/learn',
            'title' => $course['title'],
            'course' => $course,
            'modules' => $moduleData,
            'progress' => $learningService->getCourseProgress($studentId, (int) $courseId),
        ];
    }],
    ['GET', '/lessons/{id}', function (string $lessonId) use ($lessonRepository, $enrollmentRepository, $courseRepository, $progressRepository) {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';

            return redirect_to('/login');
        }

        $studentId = (int) Auth::userId();
        $lesson = $lessonRepository->findById((int) $lessonId);

        if ($lesson === null) {
            return ['view' => 'errors/not_found', 'title' => 'Lesson not found'];
        }

        $courseId = (int) ($lesson['course_id'] ?? 0);
        $course = $courseRepository->findById($courseId);

        if ($course === null || $enrollmentRepository->findByStudentAndCourse($studentId, $courseId) === null) {
            $_SESSION['flash_error'] = 'You are not enrolled for this lesson.';

            return redirect_to('/courses');
        }

        return [
            'view' => 'lessons/view',
            'title' => $lesson['title'],
            'lesson' => $lesson,
            'course' => $course,
            'completed' => $progressRepository->findByStudentAndLesson($studentId, (int) $lessonId) !== null,
        ];
    }],
    ['POST', '/lessons/{id}/complete', function (string $lessonId) use ($learningService) {
        $studentId = Auth::userId();

        if ($studentId === null) {
            $_SESSION['flash_error'] = 'Please log in to continue.';

            return redirect_to('/login');
        }

        $token = $_POST['_token'] ?? null;
        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/lessons/' . (int) $lessonId);
        }

        $result = $learningService->markLessonComplete($studentId, (int) $lessonId);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return redirect_to('/lessons/' . (int) $lessonId);
        }

        $_SESSION['flash_success'] = 'Lesson marked complete.';

        return redirect_to('/lessons/' . (int) $lessonId);
    }],

    ['GET', '/courses/{id}/quizzes', function (string $courseId) use ($courseRepository, $quizRepository, $enrollmentRepository) {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return redirect_to('/login');
        }

        $studentId = (int) Auth::userId();
        $course = $courseRepository->findById((int) $courseId);
        if ($course === null) {
            return ['view' => 'errors/not_found', 'title' => 'Course not found'];
        }

        if ($enrollmentRepository->findByStudentAndCourse($studentId, (int) $courseId) === null) {
            $_SESSION['flash_error'] = 'You must enroll in the course first.';
            return redirect_to('/courses/' . (int) $courseId);
        }

        $quizzes = array_values(array_filter(
            $quizRepository->findByCourse((int) $courseId),
            fn(array $quiz): bool => strtolower((string) ($quiz['status'] ?? 'draft')) === 'published'
        ));

        return [
            'view' => 'student/quizzes',
            'title' => 'Course Quizzes',
            'course' => $course,
            'quizzes' => $quizzes,
        ];
    }],
    ['GET', '/quizzes/{id}', function (string $quizId) use ($quizService, $quizAttemptRepository) {
        $studentId = Auth::userId();
        if ($studentId === null) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return redirect_to('/login');
        }

        $quiz = $quizService->getQuizForStudent((int) $studentId, (int) $quizId);
        if ($quiz === null) {
            return ['view' => 'errors/not_found', 'title' => 'Quiz not found'];
        }

        $attempt = $quizAttemptRepository->findActiveByStudentAndQuiz((int) $studentId, (int) $quizId);

        return [
            'view' => 'student/quiz',
            'title' => $quiz['title'],
            'quiz' => $quiz,
            'attempt' => $attempt,
        ];
    }],
    ['POST', '/quizzes/{id}/start', function (string $quizId) use ($quizService) {
        $studentId = Auth::userId();
        if ($studentId === null) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/quizzes/' . (int) $quizId);
        }

        $result = $quizService->startAttempt((int) $studentId, (int) $quizId);
        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return redirect_to('/quizzes/' . (int) $quizId);
        }

        return redirect_to('/quizzes/' . (int) $quizId . '?attempt=' . (int) $result['data']['id']);
    }],
    ['POST', '/quiz-attempts/{id}/submit', function (string $attemptId) use ($quizService) {
        $studentId = Auth::userId();
        if ($studentId === null) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/dashboard');
        }

        $result = $quizService->submitAttempt(
            (int) $studentId,
            (int) $attemptId,
            is_array($_POST['answers'] ?? null) ? $_POST['answers'] : []
        );

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return redirect_to('/dashboard');
        }

        return redirect_to('/quiz-attempts/' . (int) $attemptId . '/result');
    }],
    ['GET', '/quiz-attempts/{id}/result', function (string $attemptId) use ($quizService) {
        $studentId = Auth::userId();
        if ($studentId === null) {
            return redirect_to('/login');
        }

        $attempt = $quizService->getAttemptForStudent((int) $studentId, (int) $attemptId);
        if ($attempt === null) {
            return ['view' => 'errors/not_found', 'title' => 'Result not found'];
        }

        return [
            'view' => 'student/quiz-result',
            'title' => 'Quiz Result',
            'attempt' => $attempt,
        ];
    }],

    ['GET', '/admin/courses/{id}/quizzes/create', function (string $courseId) use ($courseRepository) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        $course = $courseRepository->findById((int) $courseId);
        if ($course === null) {
            return ['view' => 'errors/not_found', 'title' => 'Course not found'];
        }

        return [
            'view' => 'admin/quiz-form',
            'title' => 'Create Quiz',
            'course' => $course,
        ];
    }],
    ['POST', '/admin/quizzes/store', function () use ($quizService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/dashboard');
        }

        $courseId = (int) ($_POST['course_id'] ?? 0);
        $result = $quizService->createQuiz($courseId, $_POST, (int) Auth::userId());

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return redirect_to('/admin/courses/' . $courseId . '/quizzes/create');
        }

        $_SESSION['flash_success'] = 'Quiz created. Add questions before publishing it for learners.';
        return redirect_to('/admin/quizzes/' . (int) $result['data']['id'] . '/questions/create');
    }],
    ['GET', '/admin/quizzes/{id}/questions/create', function (string $quizId) use ($quizRepository) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        $quiz = $quizRepository->findById((int) $quizId);
        if ($quiz === null) {
            return ['view' => 'errors/not_found', 'title' => 'Quiz not found'];
        }

        return [
            'view' => 'admin/question-form',
            'title' => 'Add Quiz Question',
            'quiz' => $quiz,
        ];
    }],
    ['POST', '/admin/quizzes/{id}/questions/store', function (string $quizId) use ($quizService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/dashboard');
        }

        $correct = (string) ($_POST['correct_option'] ?? '');
        $rawOptions = is_array($_POST['options'] ?? null) ? $_POST['options'] : [];
        $options = [];

        foreach ($rawOptions as $letter => $option) {
            $options[] = [
                'option_text' => trim((string) ($option['option_text'] ?? '')),
                'is_correct' => $letter === $correct,
            ];
        }

        $result = $quizService->addQuestion(
            (int) $quizId,
            [
                'question_text' => $_POST['question_text'] ?? '',
                'options' => $options,
            ],
            (int) Auth::userId()
        );

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
        } else {
            $_SESSION['flash_success'] = 'Question added successfully.';
        }

        return redirect_to('/admin/quizzes/' . (int) $quizId . '/questions/create');
    }],
    ['POST', '/admin/quizzes/{id}/publish', function (string $quizId) use ($quizService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/dashboard');
        }

        $result = $quizService->publishQuiz((int) $quizId);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['message'];

        return redirect_to('/admin/quizzes/' . (int) $quizId . '/questions/create');
    }],
    ['GET', '/courses/{id}/exams', function (string $courseId) use ($courseRepository, $examRepository, $enrollmentRepository) {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return redirect_to('/login');
        }

        $studentId = (int) Auth::userId();
        $course = $courseRepository->findById((int) $courseId);

        if ($course === null) {
            return ['view' => 'errors/not_found', 'title' => 'Course not found'];
        }

        if ($enrollmentRepository->findByStudentAndCourse($studentId, (int) $courseId) === null) {
            $_SESSION['flash_error'] = 'You must enroll in the course first.';
            return redirect_to('/courses/' . (int) $courseId);
        }

        $exams = array_values(array_filter(
            $examRepository->findByCourse((int) $courseId),
            fn(array $exam): bool => strtolower((string) ($exam['status'] ?? 'draft')) === 'published'
        ));

        return [
            'view' => 'student/exams',
            'title' => 'Course Exams',
            'course' => $course,
            'exams' => $exams,
        ];
    }],
    ['GET', '/exams/{id}', function (string $examId) use ($examService, $examAttemptRepository) {
        $studentId = Auth::userId();

        if ($studentId === null) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return redirect_to('/login');
        }

        $exam = $examService->getExamForStudent((int) $studentId, (int) $examId);

        if ($exam === null) {
            return ['view' => 'errors/not_found', 'title' => 'Exam not found'];
        }

        $attempt = $examAttemptRepository->findActiveByStudentAndExam((int) $studentId, (int) $examId);

        return [
            'view' => 'student/exam',
            'title' => $exam['title'],
            'exam' => $exam,
            'attempt' => $attempt,
        ];
    }],
    ['POST', '/exams/{id}/start', function (string $examId) use ($examService) {
        $studentId = Auth::userId();

        if ($studentId === null) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/exams/' . (int) $examId);
        }

        $result = $examService->startAttempt((int) $studentId, (int) $examId);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return redirect_to('/exams/' . (int) $examId);
        }

        return redirect_to('/exams/' . (int) $examId . '?attempt=' . (int) $result['data']['id']);
    }],
    ['POST', '/exam-attempts/{id}/answers', function (string $attemptId) use ($examService, $examAttemptRepository) {
        $studentId = Auth::userId();

        if ($studentId === null) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/dashboard');
        }

        $questionId = (int) ($_POST['question_id'] ?? 0);
        $selectedOptionId = (int) ($_POST['selected_option_id'] ?? 0);
        $attempt = $examAttemptRepository->findById((int) $attemptId);

        if ($attempt === null || (int) ($attempt['user_id'] ?? 0) !== (int) $studentId) {
            $_SESSION['flash_error'] = 'Attempt not found.';
            return redirect_to('/dashboard');
        }

        $result = $examService->saveAnswer((int) $studentId, (int) $attemptId, [
            'question_id' => $questionId,
            'selected_option_id' => $selectedOptionId,
            'answer_text' => $_POST['answer_text'] ?? null,
        ]);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
        }

        return redirect_to('/exams/' . (int) ($attempt['exam_id'] ?? 0));
    }],
    ['POST', '/exam-attempts/{id}/submit', function (string $attemptId) use ($examService, $examAttemptRepository) {
        $studentId = Auth::userId();

        if ($studentId === null) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/dashboard');
        }

        $answers = [];
        $rawAnswers = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];

        foreach ($rawAnswers as $questionId => $answer) {
            $answers[] = [
                'question_id' => (int) $questionId,
                'selected_option_id' => (int) (is_array($answer) ? ($answer['selected_option_id'] ?? 0) : $answer),
                'answer_text' => is_array($answer) ? ($answer['answer_text'] ?? null) : null,
            ];
        }

        $result = $examService->submitAttempt((int) $studentId, (int) $attemptId, $answers);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return redirect_to('/dashboard');
        }

        return redirect_to('/exam-attempts/' . (int) $attemptId . '/result');
    }],
    ['GET', '/exam-attempts/{id}/result', function (string $attemptId) use ($examService) {
        $studentId = Auth::userId();

        if ($studentId === null) {
            return redirect_to('/login');
        }

        $attempt = $examService->getAttemptResult((int) $studentId, (int) $attemptId);

        if ($attempt === null) {
            return ['view' => 'errors/not_found', 'title' => 'Exam result not found'];
        }

        return [
            'view' => 'student/exam-result',
            'title' => 'Exam Result',
            'attempt' => $attempt,
        ];
    }],
    ['GET', '/admin/courses/{id}/exams/create', function (string $courseId) use ($courseRepository) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        $course = $courseRepository->findById((int) $courseId);

        if ($course === null) {
            return ['view' => 'errors/not_found', 'title' => 'Course not found'];
        }

        return [
            'view' => 'admin/exam-form',
            'title' => 'Create Exam',
            'course' => $course,
        ];
    }],
    ['POST', '/admin/exams/store', function () use ($examService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/dashboard');
        }

        $courseId = (int) ($_POST['course_id'] ?? 0);
        $result = $examService->createExam($courseId, $_POST, (int) Auth::userId());

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return redirect_to('/admin/courses/' . $courseId . '/exams/create');
        }

        $_SESSION['flash_success'] = 'Exam created. Add questions before publishing.';
        return redirect_to('/admin/exams/' . (int) $result['data']['id'] . '/questions/create');
    }],
    ['GET', '/admin/exams/{id}/questions/create', function (string $examId) use ($examRepository) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        $exam = $examRepository->findById((int) $examId);

        if ($exam === null) {
            return ['view' => 'errors/not_found', 'title' => 'Exam not found'];
        }

        return [
            'view' => 'admin/exam-question-form',
            'title' => 'Add Exam Question',
            'exam' => $exam,
        ];
    }],
    ['POST', '/admin/exams/{id}/questions/store', function (string $examId) use ($examService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/dashboard');
        }

        $rawOptions = is_array($_POST['options'] ?? null) ? $_POST['options'] : [];
        $correct = (string) ($_POST['correct_option'] ?? '');
        $options = [];

        foreach ($rawOptions as $letter => $option) {
            $options[] = [
                'option_text' => trim((string) ($option['option_text'] ?? '')),
                'is_correct' => $letter === $correct,
            ];
        }

        $result = $examService->addQuestion((int) $examId, [
            'question_text' => $_POST['question_text'] ?? '',
            'marks' => $_POST['marks'] ?? 1,
            'options' => $options,
        ]);

        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['message'];

        return redirect_to('/admin/exams/' . (int) $examId . '/questions/create');
    }],
    ['POST', '/admin/exams/{id}/publish', function (string $examId) use ($examService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/dashboard');
        }

        $result = $examService->publishExam((int) $examId);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['message'];

        return redirect_to('/admin/exams/' . (int) $examId . '/questions/create');
    }],

    ['GET', '/student/certificates', function () use ($certificateService) {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return redirect_to('/login');
        }

        $certificates = $certificateService->getStudentCertificates((int) Auth::userId());

        return [
            'view' => 'student/certificates',
            'title' => 'My Certificates',
            'certificates' => $certificates,
        ];
    }],
    ['GET', '/verify/{certificate_number}', function (string $certificateNumber) use ($certificateService) {
        $verificationCode = trim((string) ($_GET['code'] ?? ''));

        if ($verificationCode === '') {
            return [
                'view' => 'certificates/verify',
                'title' => 'Certificate Verification',
                'certificate_number' => $certificateNumber,
                'error' => 'A verification code is required.',
            ];
        }

        $certificate = $certificateService->verifyCertificate($certificateNumber, $verificationCode);

        if ($certificate === null) {
            return [
                'view' => 'certificates/verify',
                'title' => 'Certificate Verification',
                'certificate_number' => $certificateNumber,
                'error' => 'The certificate number or verification code is invalid.',
            ];
        }

        return [
            'view' => 'certificates/verify',
            'title' => 'Certificate Verification',
            'certificate_number' => $certificateNumber,
            'certificate' => $certificate,
        ];
    }],
    ['GET', '/admin/certificates', function () use ($certificateService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        return [
            'view' => 'admin/certificates',
            'title' => 'Certificate Review',
            'certificates' => $certificateService->getCertificatesForAdmin(),
        ];
    }],
    ['POST', '/admin/certificates/{id}/status', function (string $certificateId) use ($certificateService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/admin/certificates');
        }

        $result = $certificateService->updateCertificateStatus(
            (int) Auth::userId(),
            (int) $certificateId,
            (string) ($_POST['status'] ?? 'active')
        );

        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['message'];

        return redirect_to('/admin/certificates');
    }],
    ['GET', '/admin/audit-logs', function () use ($auditLogService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        return [
            'view' => 'admin/audit-logs',
            'title' => 'Audit Logs',
            'logs' => $auditLogService->getRecentForAdmin(),
        ];
    }],
    ['GET', '/dashboard', function () {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';

            return redirect_to('/login');
        }

        return [
            'view' => 'dashboard',
            'title' => 'Dashboard',
        ];
    }],
];
