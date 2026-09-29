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
$certificateVerificationRateLimitService = $app['certificateVerificationRateLimitService'];
$payChanguService = $app['payChanguService'];
$platformSettingsRepository = $app['platformSettingsRepository'];
$paymentTransactionRepository = $app['paymentTransactionRepository'];
$studentMembershipRepository = $app['studentMembershipRepository'];
$paymentsConfig = $app['payments'];

use App\Core\Auth;
use App\Support\Csrf;

return [
    ['GET', '/', function () use ($courseRepository) {
        return [
            'view' => 'landing',
            'title' => 'FastPhunzira',
            'featured_courses' => array_slice($courseRepository->findPublished(), 0, 3),
        ];
    }],
    ['GET', '/courses', [$courseController, 'catalogue']],
    ['GET', '/courses/{id}', function (string $id) use ($courseController) {
        if (!ctype_digit($id)) {
            http_response_code(404);
            return ['view' => 'errors/not_found', 'title' => 'Course not found'];
        }

        return $courseController->detail((int) $id);
    }],
    ['GET', '/admin/courses', [$courseController, 'adminIndex']],
    ['GET', '/admin/courses/create', [$courseController, 'createForm']],
    ['GET', '/admin/courses/{id}/edit', function (string $id) use ($courseController) {
        if (!ctype_digit($id)) {
            http_response_code(404);
            return ['view' => 'errors/not_found', 'title' => 'Course not found'];
        }

        return $courseController->editForm((int) $id);
    }],
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
    ['GET', '/forgot-password', function () {
        return [
            'view' => 'auth/forgot-password',
            'title' => 'Forgot Password',
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
            $_SESSION['flash_error'] = $result['message'];

            return redirect_to('/login');
        }

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
    ['GET', '/courses/{id}/learn', function (string $courseId) use ($courseRepository, $moduleRepository, $lessonRepository, $enrollmentRepository, $learningService, $studentMembershipRepository) {
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

            return redirect_to('/courses/' . (int) $courseId . '?from=my-courses');
        }

        if (
            strtolower((string) ($course['access_tier'] ?? 'regular')) === 'premium'
            && ($studentMembershipRepository === null || !$studentMembershipRepository->isPremiumActive($studentId))
        ) {
            $_SESSION['flash_error'] = 'Your Premium membership is required to continue this course.';

            return redirect_to('/courses/' . (int) $courseId . '?from=my-courses');
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
    ['GET', '/lessons/{id}', function (string $lessonId) use ($lessonRepository, $enrollmentRepository, $courseRepository, $progressRepository, $studentMembershipRepository) {
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

        if (
            strtolower((string) ($course['access_tier'] ?? 'regular')) === 'premium'
            && ($studentMembershipRepository === null || !$studentMembershipRepository->isPremiumActive($studentId))
        ) {
            $_SESSION['flash_error'] = 'Your Premium membership is required to access this lesson.';

            return redirect_to('/courses/' . $courseId . '?from=my-courses');
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

    ['POST', '/admin/courses/{courseId}/modules/store', function (string $courseId) use ($learningService) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/admin/courses/' . (int) $courseId . '/edit');
        }
        $result = $learningService->createModule((int) $courseId, [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ], (int) Auth::userId());
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['message'];
        return redirect_to('/admin/courses/' . (int) $courseId . '/edit');
    }],
    ['POST', '/admin/modules/{moduleId}/update', function (string $moduleId) use ($learningService, $moduleRepository) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        $module = $moduleRepository->findById((int) $moduleId);
        $courseId = (int) ($module['course_id'] ?? 0);
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/admin/courses/' . $courseId . '/edit');
        }
        $result = $learningService->updateModule((int) $moduleId, [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['message'];
        return redirect_to('/admin/courses/' . $courseId . '/edit');
    }],
    ['POST', '/admin/modules/{moduleId}/delete', function (string $moduleId) use ($learningService, $moduleRepository) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        $module = $moduleRepository->findById((int) $moduleId);
        $courseId = (int) ($module['course_id'] ?? 0);
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/admin/courses/' . $courseId . '/edit');
        }
        $result = $learningService->deleteModule((int) $moduleId);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['message'];
        return redirect_to('/admin/courses/' . $courseId . '/edit');
    }],
    ['GET', '/admin/modules/{moduleId}/lessons/create', function (string $moduleId) use ($moduleRepository) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        $module = $moduleRepository->findById((int) $moduleId);
        if ($module === null) {
            return ['view' => 'errors/not_found', 'title' => 'Module not found'];
        }

        return [
            'view' => 'admin/lesson-form',
            'title' => 'Add Lesson',
            'moduleId' => (int) $moduleId,
            'courseId' => (int) ($module['course_id'] ?? 0),
        ];
    }],
    ['POST', '/admin/modules/{moduleId}/lessons/store', function (string $moduleId) use ($learningService, $moduleRepository) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/admin/modules/' . (int) $moduleId . '/lessons/create');
        }

        $result = $learningService->createLesson((int) $moduleId, [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'summary' => trim((string) ($_POST['summary'] ?? '')),
            'content' => trim((string) ($_POST['content'] ?? '')),
            'video_url' => trim((string) ($_POST['video_url'] ?? '')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ], (int) Auth::userId());

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return redirect_to('/admin/modules/' . (int) $moduleId . '/lessons/create');
        }

        $module = $moduleRepository->findById((int) $moduleId);
        $_SESSION['flash_success'] = 'Lesson created successfully.';

        return redirect_to('/admin/courses/' . (int) ($module['course_id'] ?? 0) . '/edit');
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
    ['POST', '/admin/quizzes/{id}/archive', function (string $quizId) use ($quizService, $quizRepository) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        $quiz = $quizRepository->findById((int) $quizId);
        $courseId = (int) ($quiz['course_id'] ?? 0);
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/admin/courses/' . $courseId . '/edit');
        }
        $result = $quizRepository->updateStatus((int) $quizId, 'archived');
        $_SESSION[$result !== null ? 'flash_success' : 'flash_error'] = $result !== null ? 'Quiz archived.' : 'Quiz could not be archived.';
        return redirect_to('/admin/courses/' . $courseId . '/edit');
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

    ['POST', '/admin/exams/{id}/archive', function (string $examId) use ($examRepository) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        $exam = $examRepository->findById((int) $examId);
        $courseId = (int) ($exam['course_id'] ?? 0);
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/admin/courses/' . $courseId . '/edit');
        }
        $result = $examRepository->updateStatus((int) $examId, 'archived');
        $_SESSION[$result !== null ? 'flash_success' : 'flash_error'] = $result !== null ? 'Exam archived.' : 'Exam could not be archived.';
        return redirect_to('/admin/courses/' . $courseId . '/edit');
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
    ['GET', '/verify/{certificate_number}', function (string $certificateNumber) use ($certificateService, $certificateVerificationRateLimitService) {
        $ipAddress = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        $verificationCode = trim((string) ($_GET['code'] ?? ''));

        if ($certificateVerificationRateLimitService->isThrottled($ipAddress)) {
            return [
                'view' => 'certificates/verify',
                'title' => 'Certificate Verification',
                'certificate_number' => $certificateNumber,
                'error' => 'Too many verification attempts. Please try again later.',
            ];
        }

        if ($verificationCode === '') {
            $certificateVerificationRateLimitService->recordAttempt($ipAddress, $certificateNumber, false);

            return [
                'view' => 'certificates/verify',
                'title' => 'Certificate Verification',
                'certificate_number' => $certificateNumber,
                'error' => 'A verification code is required.',
            ];
        }

        $certificate = $certificateService->verifyCertificate($certificateNumber, $verificationCode);
        $certificateVerificationRateLimitService->recordAttempt($ipAddress, $certificateNumber, $certificate !== null);

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
    ['GET', '/admin/settings', function () use ($platformSettingsRepository, $paymentsConfig, $payChanguService) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if ($platformSettingsRepository === null) {
            return ['view' => 'errors/not_found', 'title' => 'Settings unavailable'];
        }

        return [
            'view' => 'admin/settings',
            'title' => 'Platform Settings',
            'premiumPrice' => (float) ($platformSettingsRepository->get('premium_price', '0') ?? '0'),
            'premiumCurrency' => (string) ($platformSettingsRepository->get('premium_currency', $paymentsConfig['currency'] ?? 'MWK') ?? 'MWK'),
            'premiumDurationDays' => (int) ($platformSettingsRepository->get('premium_duration_days', '30') ?? '30'),
            'payChanguEnabled' => $payChanguService?->enabled() ?? false,
            'payChanguMode' => (string) ($paymentsConfig['mode'] ?? 'test'),
            'payChanguSecretConfigured' => trim((string) ($paymentsConfig['secret_key'] ?? '')) !== '',
            'payChanguWebhookConfigured' => trim((string) ($paymentsConfig['webhook_secret'] ?? '')) !== '',
        ];
    }],
    ['POST', '/admin/settings', function () use ($platformSettingsRepository) {
        if (!Auth::userCan('courses.manage')) {
            return redirect_to('/login');
        }

        if ($platformSettingsRepository === null || !Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid settings request.';

            return redirect_to('/admin/settings');
        }

        $price = max(0, (float) ($_POST['premium_price'] ?? 0));
        $currency = strtoupper(trim((string) ($_POST['premium_currency'] ?? 'MWK')));
        $duration = max(1, (int) ($_POST['premium_duration_days'] ?? 30));

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            $_SESSION['flash_error'] = 'Currency must be a valid three-letter code.';

            return redirect_to('/admin/settings');
        }

        $platformSettingsRepository->set('premium_price', number_format($price, 2, '.', ''));
        $platformSettingsRepository->set('premium_currency', $currency);
        $platformSettingsRepository->set('premium_duration_days', (string) $duration);

        $_SESSION['flash_success'] = 'Platform settings updated.';

        return redirect_to('/admin/settings');
    }],
    ['POST', '/premium/checkout', function () use ($payChanguService) {
        if (!Auth::check()) {
            return redirect_to('/login');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';

            return redirect_to('/dashboard');
        }

        if ($payChanguService === null) {
            $_SESSION['flash_error'] = 'Payment service is unavailable.';

            return redirect_to('/dashboard');
        }

        $result = $payChanguService->initiatePremiumCheckout(
            Auth::user(),
            base_url('payments/paychangu/callback'),
            base_url('dashboard')
        );

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];

            return redirect_to('/dashboard');
        }

        return ['redirect' => $result['checkout_url']];
    }],
    ['GET', '/payments/paychangu/callback', function () use ($payChanguService, $paymentTransactionRepository, $studentMembershipRepository) {
        $txRef = trim((string) ($_GET['tx_ref'] ?? ''));
        if ($txRef === '' || $payChanguService === null || $paymentTransactionRepository === null || $studentMembershipRepository === null) {
            $_SESSION['flash_error'] = 'Payment could not be confirmed.';

            return redirect_to('/dashboard');
        }

        $transaction = $paymentTransactionRepository->findByTxRef($txRef);
        if ($transaction === null || ($transaction['purpose'] ?? '') !== 'premium_membership') {
            $_SESSION['flash_error'] = 'Payment could not be confirmed.';

            return redirect_to(Auth::check() ? '/dashboard' : '/login');
        }

        if (($transaction['status'] ?? '') === 'successful') {
            $_SESSION['flash_success'] = 'Premium membership is already active.';

            return redirect_to('/dashboard');
        }

        $verification = $payChanguService->verify($txRef);
        $providerData = $verification['data']['data'] ?? [];

        $successful = $verification['success']
            && strtolower((string) ($providerData['status'] ?? '')) === 'success'
            && (string) ($providerData['tx_ref'] ?? '') === (string) $transaction['tx_ref']
            && strtoupper((string) ($providerData['currency'] ?? '')) === strtoupper((string) $transaction['currency'])
            && (float) ($providerData['amount'] ?? 0) >= (float) $transaction['amount'];

        if (!$successful) {
            $paymentTransactionRepository->updateStatus($txRef, 'failed');

            $_SESSION['flash_error'] = 'Payment was not confirmed by PayChangu.';

            return redirect_to('/dashboard');
        }

        $paymentTransactionRepository->updateStatus(
            $txRef,
            'successful',
            isset($providerData['reference']) ? (string) $providerData['reference'] : null
        );

        $days = $payChanguService->premiumDurationDays();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $days . ' days'));
        $studentMembershipRepository->activatePremium((int) $transaction['user_id'], $expiresAt);

        $_SESSION['flash_success'] = 'Premium membership activated successfully.';

        return redirect_to(Auth::check() ? '/dashboard' : '/login');
    }],
    ['POST', '/payments/paychangu/webhook', function () use ($payChanguService, $paymentTransactionRepository, $studentMembershipRepository, $paymentsConfig) {
        $payload = file_get_contents('php://input') ?: '';
        $signature = $_SERVER['HTTP_SIGNATURE'] ?? '';

        $secret = (string) ($paymentsConfig['webhook_secret'] ?? '');
        if ($secret === '' || !hash_equals(hash_hmac('sha256', $payload, $secret), $signature)) {
            http_response_code(401);
            exit('Invalid signature.');
        }

        $data = json_decode($payload, true);
        $txRef = trim((string) ($data['tx_ref'] ?? $data['data']['tx_ref'] ?? ''));

        if ($txRef === '' || $payChanguService === null || $paymentTransactionRepository === null || $studentMembershipRepository === null) {
            http_response_code(400);
            exit('Invalid payment notification.');
        }

        $transaction = $paymentTransactionRepository->findByTxRef($txRef);
        if ($transaction === null) {
            http_response_code(200);
            exit('Ignored.');
        }

        if (($transaction['status'] ?? '') === 'successful') {
            http_response_code(200);
            exit('Already processed.');
        }

        $verification = $payChanguService->verify($txRef);
        $providerData = $verification['data']['data'] ?? [];
        $successful = $verification['success']
            && strtolower((string) ($providerData['status'] ?? '')) === 'success'
            && strtoupper((string) ($providerData['currency'] ?? '')) === strtoupper((string) $transaction['currency'])
            && (float) ($providerData['amount'] ?? 0) >= (float) $transaction['amount'];

        if ($successful) {
            $paymentTransactionRepository->updateStatus(
                $txRef,
                'successful',
                isset($providerData['reference']) ? (string) $providerData['reference'] : null
            );

            $days = $payChanguService->premiumDurationDays();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $days . ' days'));
            $studentMembershipRepository->activatePremium((int) $transaction['user_id'], $expiresAt);
        }

        http_response_code(200);
        exit('OK.');
    }],
    ['GET', '/dashboard', function () use ($studentMembershipRepository, $payChanguService, $platformSettingsRepository, $paymentsConfig) {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Please log in to continue.';

            return redirect_to('/login');
        }

        $membership = $studentMembershipRepository !== null
            ? $studentMembershipRepository->findByUserId((int) Auth::userId())
            : ['plan' => 'regular', 'status' => 'active'];

        return [
            'view' => 'dashboard',
            'title' => 'Dashboard',
            'membership' => $membership,
            'payChanguEnabled' => $payChanguService?->enabled() ?? false,
            'premiumPrice' => (float) ($platformSettingsRepository?->get('premium_price', '0') ?? '0'),
            'premiumCurrency' => (string) ($platformSettingsRepository?->get('premium_currency', $paymentsConfig['currency'] ?? 'MWK') ?? 'MWK'),
        ];
    }],
];