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
        return $courseController->store($_POST);
    }],
    ['POST', '/admin/courses/update', function () use ($courseController) {
        $id = (int) ($_POST['id'] ?? 0);

        return $courseController->update($id, $_POST);
    }],
    ['POST', '/admin/courses/publish', [$courseController, 'publish']],
    ['GET', '/login', function () {
        return [
            'view' => 'auth/login',
            'title' => 'Login',
        ];
    }],
    ['POST', '/login', function () use ($authService) {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $token = $_POST['_token'] ?? null;

        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return ['redirect' => '/login'];
        }

        $result = $authService->login(['email' => $email, 'password' => $password]);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return ['redirect' => '/login'];
        }

        $_SESSION['flash_success'] = 'Welcome back!';

        return ['redirect' => '/dashboard'];
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

            return ['redirect' => '/register'];
        }

        $result = $authService->register([
            'full_name' => trim((string) ($_POST['full_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ]);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['errors'][0]['message'] ?? $result['message'];

            return ['redirect' => '/register'];
        }

        $_SESSION['flash_success'] = 'Registration successful. Please log in.';

        return ['redirect' => '/login'];
    }],
    ['POST', '/logout', function () use ($authService) {
        $token = $_POST['_token'] ?? null;

        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return ['redirect' => '/dashboard'];
        }

        $authService->logout();
        $_SESSION['flash_success'] = 'You have been logged out.';

        return ['redirect' => '/login'];
    }],
    ['POST', '/courses/{id}/enroll', function (string $courseId) use ($learningService) {
        $studentId = Auth::userId();

        if ($studentId === null) {
            $_SESSION['flash_error'] = 'Please log in to enroll in a course.';

            return ['redirect' => '/login'];
        }

        $result = $learningService->enrollStudentInCourse($studentId, (int) $courseId);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return ['redirect' => '/courses/' . (int) $courseId];
        }

        $_SESSION['flash_success'] = 'You have been enrolled successfully.';

        return ['redirect' => '/my-courses'];
    }],
    ['GET', '/my-courses', function () use ($courseRepository, $learningService) {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';

            return ['redirect' => '/login'];
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

            return ['redirect' => '/login'];
        }

        $studentId = (int) Auth::userId();
        $course = $courseRepository->findById((int) $courseId);

        if ($course === null) {
            return ['view' => 'errors/not_found', 'title' => 'Course not found'];
        }

        if ($enrollmentRepository->findByStudentAndCourse($studentId, (int) $courseId) === null) {
            $_SESSION['flash_error'] = 'You must enroll in this course before accessing lessons.';

            return ['redirect' => '/courses/' . (int) $courseId];
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

            return ['redirect' => '/login'];
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

            return ['redirect' => '/courses'];
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

            return ['redirect' => '/login'];
        }

        $token = $_POST['_token'] ?? null;
        if (!Csrf::validate($token)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return ['redirect' => '/lessons/' . (int) $lessonId];
        }

        $result = $learningService->markLessonComplete($studentId, (int) $lessonId);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return ['redirect' => '/lessons/' . (int) $lessonId];
        }

        $_SESSION['flash_success'] = 'Lesson marked complete.';

        return ['redirect' => '/lessons/' . (int) $lessonId];
    }],

    ['GET', '/courses/{id}/quizzes', function (string $courseId) use ($courseRepository, $quizRepository, $enrollmentRepository) {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return ['redirect' => '/login'];
        }

        $studentId = (int) Auth::userId();
        $course = $courseRepository->findById((int) $courseId);
        if ($course === null) {
            return ['view' => 'errors/not_found', 'title' => 'Course not found'];
        }

        if ($enrollmentRepository->findByStudentAndCourse($studentId, (int) $courseId) === null) {
            $_SESSION['flash_error'] = 'You must enroll in the course first.';
            return ['redirect' => '/courses/' . (int) $courseId];
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
            return ['redirect' => '/login'];
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
            return ['redirect' => '/login'];
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return ['redirect' => '/quizzes/' . (int) $quizId];
        }

        $result = $quizService->startAttempt((int) $studentId, (int) $quizId);
        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return ['redirect' => '/quizzes/' . (int) $quizId];
        }

        return ['redirect' => '/quizzes/' . (int) $quizId . '?attempt=' . (int) $result['data']['id']];
    }],
    ['POST', '/quiz-attempts/{id}/submit', function (string $attemptId) use ($quizService) {
        $studentId = Auth::userId();
        if ($studentId === null) {
            $_SESSION['flash_error'] = 'Please log in to continue.';
            return ['redirect' => '/login'];
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return ['redirect' => '/dashboard'];
        }

        $result = $quizService->submitAttempt(
            (int) $studentId,
            (int) $attemptId,
            is_array($_POST['answers'] ?? null) ? $_POST['answers'] : []
        );

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return ['redirect' => '/dashboard'];
        }

        return ['redirect' => '/quiz-attempts/' . (int) $attemptId . '/result'];
    }],
    ['GET', '/quiz-attempts/{id}/result', function (string $attemptId) use ($quizService) {
        $studentId = Auth::userId();
        if ($studentId === null) {
            return ['redirect' => '/login'];
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
            return ['redirect' => '/login'];
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
            return ['redirect' => '/login'];
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return ['redirect' => '/dashboard'];
        }

        $courseId = (int) ($_POST['course_id'] ?? 0);
        $result = $quizService->createQuiz($courseId, $_POST, (int) Auth::userId());

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return ['redirect' => '/admin/courses/' . $courseId . '/quizzes/create'];
        }

        $_SESSION['flash_success'] = 'Quiz created. Add questions before publishing it for learners.';
        return ['redirect' => '/admin/quizzes/' . (int) $result['data']['id'] . '/questions/create'];
    }],
    ['GET', '/admin/quizzes/{id}/questions/create', function (string $quizId) use ($quizRepository) {
        if (!Auth::userCan('courses.manage')) {
            return ['redirect' => '/login'];
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
            return ['redirect' => '/login'];
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return ['redirect' => '/dashboard'];
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

        return ['redirect' => '/admin/quizzes/' . (int) $quizId . '/questions/create'];
    }],
    ['GET', '/dashboard', function () {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';

            return ['redirect' => '/login'];
        }

        return [
            'view' => 'dashboard',
            'title' => 'Dashboard',
        ];
    }],
];
