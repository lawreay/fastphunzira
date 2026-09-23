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
