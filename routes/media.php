<?php

use App\Core\Auth;
use App\Support\Csrf;
use App\Services\MediaStorageInterface;

$app = require __DIR__ . '/../bootstrap/app.php';
$learningService = $app['enrollmentLearningService'];
$lessonRepository = $app['lessonRepository'];
$moduleRepository = $app['moduleRepository'];
$courseRepository = $app['courseRepository'];
$enrollmentRepository = $app['enrollmentRepository'];
$studentMembershipRepository = $app['studentMembershipRepository'];
$materialRepository = $app['lessonMaterialRepository'];
$mediaStorage = $app['mediaStorage'];
$mediaConfig = $app['media'];

$studentCanAccessLesson = static function (int $lessonId) use ($lessonRepository, $courseRepository, $enrollmentRepository, $studentMembershipRepository): ?array {
    if (!Auth::check()) return null;

    $lesson = $lessonRepository->findById($lessonId);
    if ($lesson === null) return null;

    $courseId = (int) ($lesson['course_id'] ?? 0);
    $course = $courseRepository->findById($courseId);
    if ($course === null || strtolower((string) ($course['status'] ?? 'draft')) !== 'published') return null;

    $studentId = (int) Auth::userId();
    if ($enrollmentRepository->findByStudentAndCourse($studentId, $courseId) === null) return null;

    if (
        strtolower((string) ($course['access_tier'] ?? 'regular')) === 'premium'
        && ($studentMembershipRepository === null || !$studentMembershipRepository->isPremiumActive($studentId))
    ) {
        return null;
    }

    return $lesson;
};

$materialType = static function (string $mime): string {
    return match (true) {
        $mime === 'application/pdf' => 'pdf',
        str_starts_with($mime, 'audio/') => 'audio',
        str_starts_with($mime, 'video/') => 'video',
        $mime === 'text/html' => 'html',
        in_array($mime, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ], true) => 'document',
        default => 'other',
    };
};

$redirectToLessonEditor = static function (int $courseId): array {
    return redirect_to('/admin/courses/' . $courseId . '/edit?tab=modules');
};

return [
    ['GET', '/admin/modules/{moduleId}/lessons/create-media', function (string $moduleId) use ($moduleRepository) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        $module = $moduleRepository->findById((int) $moduleId);
        if ($module === null) return ['view' => 'errors/not_found', 'title' => 'Module not found'];
        return [
            'view' => 'admin/lesson-form',
            'title' => 'Create Lesson',
            'moduleId' => (int) $moduleId,
            'courseId' => (int) ($module['course_id'] ?? 0),
            'lesson' => null,
            'materials' => [],
        ];
    }],
    ['GET', '/admin/lessons/{lessonId}/edit-media', function (string $lessonId) use ($lessonRepository, $moduleRepository, $materialRepository) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        $lesson = $lessonRepository->findById((int) $lessonId);
        if ($lesson === null) return ['view' => 'errors/not_found', 'title' => 'Lesson not found'];
        $module = $moduleRepository->findById((int) ($lesson['module_id'] ?? 0));
        if ($module === null) return ['view' => 'errors/not_found', 'title' => 'Module not found'];
        return [
            'view' => 'admin/lesson-form',
            'title' => 'Edit Lesson',
            'lesson' => $lesson,
            'moduleId' => (int) ($lesson['module_id'] ?? 0),
            'courseId' => (int) ($module['course_id'] ?? 0),
            'materials' => $materialRepository->findByLesson((int) $lessonId),
        ];
    }],
    ['POST', '/admin/modules/{moduleId}/lessons/store-media', function (string $moduleId) use ($learningService, $moduleRepository, $lessonRepository, $mediaStorage, $mediaConfig, $materialRepository, $materialType, $redirectToLessonEditor) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            $module = $moduleRepository->findById((int) $moduleId);
            return $redirectToLessonEditor((int) ($module['course_id'] ?? 0));
        }

        $module = $moduleRepository->findById((int) $moduleId);
        if ($module === null) return ['view' => 'errors/not_found', 'title' => 'Module not found'];

        $result = $learningService->createLesson((int) $moduleId, [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'summary' => trim((string) ($_POST['summary'] ?? '')),
            'content' => trim((string) ($_POST['content'] ?? '')),
            'video_url' => trim((string) ($_POST['video_url'] ?? '')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ], (int) Auth::userId());

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return $redirectToLessonEditor((int) $module['course_id']);
        }

        $lessonId = (int) ($result['data']['id'] ?? 0);
        try {
            if (!empty($_FILES['video_file']['name'])) {
                $upload = $mediaStorage->store($_FILES['video_file'], 'videos', $mediaConfig['video_mimes'], (int) $mediaConfig['max_upload_bytes']);
                $lessonRepository->update($lessonId, [
                    'video_url' => null,
                    'video_original_name' => $upload['original_name'],
                    'video_mime_type' => $upload['mime_type'],
                    'video_file_size' => $upload['file_size'],
                    'file_path' => $upload['storage_path'],
                ]);
            }

            if (!empty($_FILES['material_file']['name'])) {
                $upload = $mediaStorage->store($_FILES['material_file'], 'materials', $mediaConfig['material_mimes'], (int) $mediaConfig['max_upload_bytes']);
                $title = trim((string) ($_POST['material_title'] ?? '')) ?: $upload['original_name'];
                $materialRepository->create([
                    'lesson_id' => $lessonId,
                    'title' => $title,
                    'type' => $materialType($upload['mime_type']),
                    'original_name' => $upload['original_name'],
                    'stored_name' => $upload['stored_name'],
                    'mime_type' => $upload['mime_type'],
                    'file_size' => $upload['file_size'],
                    'storage_path' => $upload['storage_path'],
                    'download_allowed' => isset($_POST['download_allowed']) ? 1 : 0,
                    'sort_order' => (int) ($_POST['material_sort_order'] ?? 0),
                ]);
            }
        } catch (Throwable $e) {
            error_log('Lesson media upload failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Lesson created, but one of the uploaded files could not be stored: ' . $e->getMessage();
            return $redirectToLessonEditor((int) $module['course_id']);
        }

        $_SESSION['flash_success'] = 'Lesson created successfully.';
        return $redirectToLessonEditor((int) $module['course_id']);
    }],

    ['POST', '/admin/lessons/{lessonId}/save-media', function (string $lessonId) use ($learningService, $lessonRepository, $moduleRepository, $mediaStorage, $mediaConfig, $materialRepository, $materialType, $redirectToLessonEditor) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');

        $lesson = $lessonRepository->findById((int) $lessonId);
        $module = $lesson !== null ? $moduleRepository->findById((int) ($lesson['module_id'] ?? 0)) : null;
        if ($lesson === null || $module === null) return ['view' => 'errors/not_found', 'title' => 'Lesson not found'];

        $courseId = (int) ($module['course_id'] ?? 0);
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return $redirectToLessonEditor($courseId);
        }

        $existingVideoPath = trim((string) ($lesson['file_path'] ?? ''));
        $videoUploaded = !empty($_FILES['video_file']['name']);
        $externalVideo = trim((string) ($_POST['video_url'] ?? ''));
        $removeVideo = isset($_POST['remove_video']);

        $updates = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'summary' => trim((string) ($_POST['summary'] ?? '')),
            'content' => trim((string) ($_POST['content'] ?? '')),
            'video_url' => $externalVideo !== '' ? $externalVideo : null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ];

        try {
            if ($videoUploaded) {
                $upload = $mediaStorage->store($_FILES['video_file'], 'videos', $mediaConfig['video_mimes'], (int) $mediaConfig['max_upload_bytes']);
                $updates['video_url'] = null;
                $updates['video_original_name'] = $upload['original_name'];
                $updates['video_mime_type'] = $upload['mime_type'];
                $updates['video_file_size'] = $upload['file_size'];
                $updates['file_path'] = $upload['storage_path'];
            } elseif ($externalVideo !== '' || $removeVideo) {
                $updates['video_original_name'] = null;
                $updates['video_mime_type'] = null;
                $updates['video_file_size'] = null;
                $updates['file_path'] = null;
            } else {
                unset($updates['video_url']);
            }

            $result = $learningService->updateLesson((int) $lessonId, $updates);
            if (!$result['success']) {
                $_SESSION['flash_error'] = $result['message'];
                return $redirectToLessonEditor($courseId);
            }

            if (($videoUploaded || $externalVideo !== '' || $removeVideo) && $existingVideoPath !== '') {
                $mediaStorage->delete($existingVideoPath);
            }

            if (!empty($_FILES['material_file']['name'])) {
                $upload = $mediaStorage->store($_FILES['material_file'], 'materials', $mediaConfig['material_mimes'], (int) $mediaConfig['max_upload_bytes']);
                $materialRepository->create([
                    'lesson_id' => (int) $lessonId,
                    'title' => trim((string) ($_POST['material_title'] ?? '')) ?: $upload['original_name'],
                    'type' => $materialType($upload['mime_type']),
                    'original_name' => $upload['original_name'],
                    'stored_name' => $upload['stored_name'],
                    'mime_type' => $upload['mime_type'],
                    'file_size' => $upload['file_size'],
                    'storage_path' => $upload['storage_path'],
                    'download_allowed' => isset($_POST['download_allowed']) ? 1 : 0,
                    'sort_order' => (int) ($_POST['material_sort_order'] ?? 0),
                ]);
            }

            $_SESSION['flash_success'] = 'Lesson saved successfully.';
        } catch (Throwable $e) {
            error_log('Lesson media save failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'The lesson could not be fully saved: ' . $e->getMessage();
        }

        return $redirectToLessonEditor($courseId);
    }],

    ['POST', '/admin/materials/{materialId}/update', function (string $materialId) use ($materialRepository, $lessonRepository, $moduleRepository, $redirectToLessonEditor) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/admin/courses');
        }

        $material = $materialRepository->findById((int) $materialId);
        $lesson = $material !== null ? $lessonRepository->findById((int) ($material['lesson_id'] ?? 0)) : null;
        $module = $lesson !== null ? $moduleRepository->findById((int) ($lesson['module_id'] ?? 0)) : null;
        if ($material === null || $lesson === null || $module === null) return ['view' => 'errors/not_found', 'title' => 'Material not found'];

        $materialRepository->update((int) $materialId, [
            'title' => trim((string) ($_POST['title'] ?? $material['title'] ?? 'Material')),
            'download_allowed' => isset($_POST['download_allowed']),
            'sort_order' => (int) ($_POST['sort_order'] ?? $material['sort_order'] ?? 0),
        ]);

        $_SESSION['flash_success'] = 'Study material updated.';
        return $redirectToLessonEditor((int) $module['course_id']);
    }],

    ['POST', '/admin/materials/{materialId}/delete', function (string $materialId) use ($materialRepository, $lessonRepository, $moduleRepository, $mediaStorage, $redirectToLessonEditor) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        if (!Csrf::validate($_POST['_token'] ?? null)) return redirect_to('/admin/courses');

        $material = $materialRepository->findById((int) $materialId);
        $lesson = $material !== null ? $lessonRepository->findById((int) ($material['lesson_id'] ?? 0)) : null;
        $module = $lesson !== null ? $moduleRepository->findById((int) ($lesson['module_id'] ?? 0)) : null;
        if ($material === null || $lesson === null || $module === null) return ['view' => 'errors/not_found', 'title' => 'Material not found'];

        $materialRepository->delete((int) $materialId);
        if (!empty($material['storage_path'])) $mediaStorage->delete((string) $material['storage_path']);

        $_SESSION['flash_success'] = 'Study material removed.';
        return $redirectToLessonEditor((int) $module['course_id']);
    }],

    ['GET', '/lesson-materials/{materialId}/view', function (string $materialId) use ($materialRepository, $studentCanAccessLesson, $mediaStorage) {
        $material = $materialRepository->findById((int) $materialId);
        if ($material === null || $studentCanAccessLesson((int) ($material['lesson_id'] ?? 0)) === null) {
            http_response_code(404); exit('Material not found.');
        }

        $path = $mediaStorage->absolutePath((string) $material['storage_path']);
        if (!is_file($path)) { http_response_code(404); exit('Material not found.'); }

        $mime = (string) $material['mime_type'];
        if ($mime === 'text/html') {
            header('Content-Type: text/html; charset=UTF-8');
            header("Content-Security-Policy: sandbox; default-src 'none'; style-src 'unsafe-inline'; img-src data:;");
            header('X-Content-Type-Options: nosniff');
        } else {
            header('Content-Type: ' . $mime);
            header('Content-Disposition: inline; filename="' . addcslashes(basename((string) $material['original_name']), "\"") . '"');
            header('X-Content-Type-Options: nosniff');
        }
        readfile($path); exit;
    }],

    ['GET', '/lesson-materials/{materialId}/download', function (string $materialId) use ($materialRepository, $studentCanAccessLesson, $mediaStorage) {
        $material = $materialRepository->findById((int) $materialId);
        if ($material === null || $studentCanAccessLesson((int) ($material['lesson_id'] ?? 0)) === null) {
            http_response_code(404); exit('Material not found.');
        }
        if (!(bool) $material['download_allowed']) {
            http_response_code(403); exit('Download is disabled for this material.');
        }

        $path = $mediaStorage->absolutePath((string) $material['storage_path']);
        if (!is_file($path)) { http_response_code(404); exit('Material not found.'); }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addcslashes(basename((string) $material['original_name']), "\"") . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path); exit;
    }],

    ['GET', '/lessons/{lessonId}/video', function (string $lessonId) use ($lessonRepository, $studentCanAccessLesson, $mediaStorage) {
        $lesson = $studentCanAccessLesson((int) $lessonId);
        if ($lesson === null || empty($lesson['file_path'])) { http_response_code(404); exit('Video not found.'); }

        $path = $mediaStorage->absolutePath((string) $lesson['file_path']);
        if (!is_file($path)) { http_response_code(404); exit('Video not found.'); }

        $mime = (string) ($lesson['video_mime_type'] ?? 'video/mp4');
        $size = filesize($path);
        $start = 0; $end = $size - 1;
        header('Content-Type: ' . $mime);
        header('Accept-Ranges: bytes');
        header('X-Content-Type-Options: nosniff');

        if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
            if ($m[1] !== '') $start = (int) $m[1];
            if ($m[2] !== '') $end = (int) $m[2];
            if ($m[1] !== '' && $m[2] === '') $end = min($start + 2 * 1024 * 1024 - 1, $size - 1);
            if ($start > $end || $start >= $size) { http_response_code(416); header('Content-Range: bytes */' . $size); exit; }
            http_response_code(206);
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
        }

        $length = $end - $start + 1;
        header('Content-Length: ' . $length);
        $handle = fopen($path, 'rb');
        fseek($handle, $start);
        $remaining = $length;
        while ($remaining > 0 && !feof($handle)) {
            $chunk = fread($handle, min(8192, $remaining));
            if ($chunk === false || $chunk === '') break;
            echo $chunk; $remaining -= strlen($chunk);
            flush();
        }
        fclose($handle);
        exit;
    }],
];
