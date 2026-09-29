<?php

use App\Core\Auth;
use App\Support\Csrf;
use App\Services\MediaStorageInterface;

$learningService = $app['enrollmentLearningService'];
$lessonRepository = $app['lessonRepository'];
$moduleRepository = $app['moduleRepository'];
$courseRepository = $app['courseRepository'];
$enrollmentRepository = $app['enrollmentRepository'];
$studentMembershipRepository = $app['studentMembershipRepository'];
$progressRepository = $app['progressRepository'];
$materialRepository = $app['lessonMaterialRepository'];
$lessonBlockRepository = $app['lessonBlockRepository'];
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
    ['GET', '/lesson-blocks/{blockId}/view', function (string $blockId) use ($lessonBlockRepository, $studentCanAccessLesson, $mediaStorage) {
        $block=$lessonBlockRepository->findById((int)$blockId);
        if($block===null || $studentCanAccessLesson((int)($block['lesson_id']??0))===null){http_response_code(404);exit('Content not found.');}
        $path=$mediaStorage->absolutePath((string)($block['storage_path']??''));
        if(!is_file($path)){http_response_code(404);exit('Content not found.');}
        $mime=(string)($block['mime_type']??'application/octet-stream');
        header('Content-Type: '.$mime); header('X-Content-Type-Options: nosniff');
        if($mime==='text/html') header("Content-Security-Policy: sandbox; default-src 'none'; style-src 'unsafe-inline'; img-src data:;");
        else header('Content-Disposition: inline; filename="'.addcslashes(basename((string)($block['original_name']??'file')),"\\\"").'"');
        readfile($path);exit;
    }],
    ['GET', '/lesson-blocks/{blockId}/download', function (string $blockId) use ($lessonBlockRepository, $studentCanAccessLesson, $mediaStorage) {
        $block=$lessonBlockRepository->findById((int)$blockId);
        if($block===null || $studentCanAccessLesson((int)($block['lesson_id']??0))===null){http_response_code(404);exit('Content not found.');}
        if(empty($block['download_allowed'])){http_response_code(403);exit('Download is disabled for this resource.');}
        $path=$mediaStorage->absolutePath((string)($block['storage_path']??''));
        if(!is_file($path)){http_response_code(404);exit('Content not found.');}
        header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.addcslashes(basename((string)($block['original_name']??'file')),"\\\"").'"');header('Content-Length: '.filesize($path));header('X-Content-Type-Options: nosniff');readfile($path);exit;
    }],
    ['GET', '/lesson-blocks/{blockId}/video', function (string $blockId) use ($lessonBlockRepository, $studentCanAccessLesson, $mediaStorage) {
        $block=$lessonBlockRepository->findById((int)$blockId);
        if($block===null || ($block['type']??'')!=='video' || $studentCanAccessLesson((int)($block['lesson_id']??0))===null){http_response_code(404);exit('Video not found.');}
        $path=$mediaStorage->absolutePath((string)($block['storage_path']??''));
        if(!is_file($path)){http_response_code(404);exit('Video not found.');}
        $size=filesize($path);$start=0;$end=$size-1;$mime=(string)($block['mime_type']??'video/mp4');
        header('Content-Type: '.$mime);header('Accept-Ranges: bytes');header('X-Content-Type-Options: nosniff');
        if(isset($_SERVER['HTTP_RANGE'])&&preg_match('/bytes=(\d*)-(\d*)/',$_SERVER['HTTP_RANGE'],$m)){
          if($m[1]===''&&$m[2]!==''){$len=min((int)$m[2],$size);$start=$size-$len;$end=$size-1;}else{if($m[1]!=='')$start=(int)$m[1];if($m[2]!=='')$end=(int)$m[2];if($m[1]!==''&&$m[2]==='')$end=min($start+2097152-1,$size-1);}
          if($start>$end||$start>=$size){http_response_code(416);header('Content-Range: bytes */'.$size);exit;}http_response_code(206);header('Content-Range: bytes '.$start.'-'.$end.'/'.$size);
        }
        $length=$end-$start+1;header('Content-Length: '.$length);$h=fopen($path,'rb');fseek($h,$start);$remaining=$length;while($remaining>0&&!feof($h)){$chunk=fread($h,min(8192,$remaining));if($chunk===false||$chunk==='')break;echo $chunk;$remaining-=strlen($chunk);flush();}fclose($h);exit;
    }],
    ['POST', '/admin/lessons/{lessonId}/delete', function (string $lessonId) use ($learningService, $lessonRepository, $moduleRepository, $materialRepository, $mediaStorage, $redirectToLessonEditor) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        $lesson = $lessonRepository->findById((int) $lessonId);
        $module = $lesson !== null ? $moduleRepository->findById((int) ($lesson['module_id'] ?? 0)) : null;
        if ($lesson === null || $module === null) return ['view' => 'errors/not_found', 'title' => 'Lesson not found'];

        $courseId = (int) ($module['course_id'] ?? 0);
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return $redirectToLessonEditor($courseId);
        }

        foreach ($materialRepository->findByLesson((int) $lessonId) as $material) {
            if (!empty($material['storage_path'])) $mediaStorage->delete((string) $material['storage_path']);
        }
        if (!empty($lesson['file_path'])) $mediaStorage->delete((string) $lesson['file_path']);

        $result = $learningService->deleteLesson((int) $lessonId);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['message'];
        return $redirectToLessonEditor($courseId);
    }],

    ['POST', '/admin/modules/{moduleId}/delete', function (string $moduleId) use ($learningService, $moduleRepository, $lessonRepository, $materialRepository, $mediaStorage) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        $module = $moduleRepository->findById((int) $moduleId);
        if ($module === null) return ['view' => 'errors/not_found', 'title' => 'Module not found'];
        $courseId = (int) ($module['course_id'] ?? 0);

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return redirect_to('/admin/courses/' . $courseId . '/edit?tab=modules');
        }

        foreach ($lessonRepository->findByModule((int) $moduleId) as $lesson) {
            foreach ($materialRepository->findByLesson((int) ($lesson['id'] ?? 0)) as $material) {
                if (!empty($material['storage_path'])) $mediaStorage->delete((string) $material['storage_path']);
            }
            if (!empty($lesson['file_path'])) $mediaStorage->delete((string) $lesson['file_path']);
        }

        $result = $learningService->deleteModule((int) $moduleId);
        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['message'];
        return redirect_to('/admin/courses/' . $courseId . '/edit?tab=modules');
    }],

    ['GET', '/lessons/{lessonId}', function (string $lessonId) use ($studentCanAccessLesson, $progressRepository, $materialRepository, $lessonBlockRepository, $courseRepository) {
        $lesson = $studentCanAccessLesson((int) $lessonId);
        if ($lesson === null) {
            $_SESSION['flash_error'] = 'You are not enrolled for this lesson.';
            return redirect_to('/courses');
        }

        $course = $courseRepository->findById((int) ($lesson['course_id'] ?? 0));
        return [
            'view' => 'lessons/view',
            'title' => $lesson['title'],
            'lesson' => $lesson,
            'course' => $course,
            'completed' => $progressRepository->findByStudentAndLesson((int) Auth::userId(), (int) $lessonId) !== null,
            'materials' => $materialRepository->findByLesson((int) $lessonId),
            'blocks' => $lessonBlockRepository->findByLesson((int) $lessonId),
        ];
    }],
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
            'blocks' => [],
        ];
    }],
    ['GET', '/admin/lessons/{lessonId}/edit-media', function (string $lessonId) use ($lessonRepository, $moduleRepository, $materialRepository, $lessonBlockRepository) {
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
            'blocks' => $lessonBlockRepository->findByLesson((int) $lessonId),
        ];
    }],
    ['POST', '/admin/modules/{moduleId}/lessons/store-media', function (string $moduleId) use ($learningService, $moduleRepository, $lessonRepository, $mediaStorage, $mediaConfig, $materialRepository, $materialType, $lessonBlockRepository, $redirectToLessonEditor) {
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
            $blocks = is_array($_POST['blocks'] ?? null) ? $_POST['blocks'] : [];
            foreach ($blocks as $index => $block) {
                if (!is_array($block)) continue;
                $type=(string)($block['type']??'text');
                if(!in_array($type,['text','youtube','video','material'],true)) continue;
                $data=['lesson_id'=>$lessonId,'type'=>$type,'title'=>trim((string)($block['title']??'')),'content'=>trim((string)($block['content']??'')),'download_allowed'=>isset($block['download_allowed'])?1:0,'sort_order'=>(int)($block['sort_order']??$index)];
                if($type==='youtube' && $data['content']==='') throw new RuntimeException('YouTube blocks require a video URL.');
                if(in_array($type,['video','material'],true)){
                    if(empty($_FILES['block_files']['name'][$index])) throw new RuntimeException(ucfirst($type).' blocks require a file.');
                    $fileData=['name'=>$_FILES['block_files']['name'][$index],'type'=>$_FILES['block_files']['type'][$index]??'','tmp_name'=>$_FILES['block_files']['tmp_name'][$index],'error'=>$_FILES['block_files']['error'][$index]??UPLOAD_ERR_NO_FILE,'size'=>$_FILES['block_files']['size'][$index]??0];
                    $upload=$mediaStorage->store($fileData,$type==='video'?'videos':'materials',$type==='video'?$mediaConfig['video_mimes']:$mediaConfig['material_mimes'],(int)$mediaConfig['max_upload_bytes']);
                    $data['storage_path']=$upload['storage_path'];$data['original_name']=$upload['original_name'];$data['mime_type']=$upload['mime_type'];$data['file_size']=$upload['file_size'];
                }
                $lessonBlockRepository->create($data);
            }
        } catch (Throwable $e) {
            error_log('Lesson media upload failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Lesson created, but one of the uploaded files could not be stored: ' . $e->getMessage();
            return $redirectToLessonEditor((int) $module['course_id']);
        }

        $_SESSION['flash_success'] = 'Lesson created successfully.';
        return $redirectToLessonEditor((int) $module['course_id']);
    }],

    ['POST', '/admin/lessons/{lessonId}/save-media', function (string $lessonId) use ($learningService, $lessonRepository, $moduleRepository, $mediaStorage, $mediaConfig, $lessonBlockRepository, $redirectToLessonEditor) {
        if (!Auth::userCan('courses.manage')) return redirect_to('/login');
        $lesson = $lessonRepository->findById((int) $lessonId);
        $module = $lesson !== null ? $moduleRepository->findById((int) ($lesson['module_id'] ?? 0)) : null;
        if ($lesson === null || $module === null) return ['view' => 'errors/not_found', 'title' => 'Lesson not found'];

        $courseId = (int) ($module['course_id'] ?? 0);
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Invalid security token.';
            return $redirectToLessonEditor($courseId);
        }

        $updates = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'summary' => trim((string) ($_POST['summary'] ?? '')),
            'content' => trim((string) ($_POST['content'] ?? '')),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ];
        $result = $learningService->updateLesson((int) $lessonId, $updates);
        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            return $redirectToLessonEditor($courseId);
        }

        $blocks = is_array($_POST['blocks'] ?? null) ? $_POST['blocks'] : [];
        $existing = [];
        foreach ($lessonBlockRepository->findByLesson((int) $lessonId) as $block) {
            $existing[(int) $block['id']] = $block;
        }

        try {
            foreach ($blocks as $index => $block) {
                if (!is_array($block)) continue;
                $id = (int) ($block['id'] ?? 0);
                $type = (string) ($block['type'] ?? 'text');
                if (!in_array($type, ['text','youtube','video','material'], true)) continue;

                $data = [
                    'title' => trim((string) ($block['title'] ?? '')),
                    'content' => trim((string) ($block['content'] ?? '')),
                    'download_allowed' => isset($block['download_allowed']) ? 1 : 0,
                    'sort_order' => (int) ($block['sort_order'] ?? $index),
                ];

                if ($type === 'youtube' && $data['content'] === '') {
                    throw new RuntimeException('YouTube blocks require a video URL.');
                }

                if ($id > 0 && isset($existing[$id])) {
                    $lessonBlockRepository->update($id, $data);
                    unset($existing[$id]);
                    continue;
                }

                $upload = null;
                if (in_array($type, ['video','material'], true)) {
                    $file = $_FILES['block_files']['tmp_name'][$index] ?? null;
                    if ($file !== null && !empty($_FILES['block_files']['name'][$index])) {
                        $fileData = [
                            'name' => $_FILES['block_files']['name'][$index],
                            'type' => $_FILES['block_files']['type'][$index] ?? '',
                            'tmp_name' => $_FILES['block_files']['tmp_name'][$index],
                            'error' => $_FILES['block_files']['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                            'size' => $_FILES['block_files']['size'][$index] ?? 0,
                        ];
                        $allowed = $type === 'video' ? $mediaConfig['video_mimes'] : $mediaConfig['material_mimes'];
                        $upload = $mediaStorage->store($fileData, $type === 'video' ? 'videos' : 'materials', $allowed, (int) $mediaConfig['max_upload_bytes']);
                    } else {
                        throw new RuntimeException(($type === 'video' ? 'Video' : 'Learning material') . ' blocks require a file.');
                    }
                }

                if ($upload !== null) {
                    $data['storage_path'] = $upload['storage_path'];
                    $data['original_name'] = $upload['original_name'];
                    $data['mime_type'] = $upload['mime_type'];
                    $data['file_size'] = $upload['file_size'];
                }
                $data['lesson_id'] = (int) $lessonId;
                $data['type'] = $type;
                $lessonBlockRepository->create($data);
            }

            foreach ($existing as $oldBlock) {
                if (!empty($oldBlock['storage_path'])) $mediaStorage->delete((string) $oldBlock['storage_path']);
                $lessonBlockRepository->delete((int) $oldBlock['id']);
            }

            $_SESSION['flash_success'] = 'Lesson saved successfully.';
        } catch (Throwable $e) {
            error_log('Lesson content block save failed: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Lesson details were saved, but a content block failed: ' . $e->getMessage();
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
            if ($m[1] === '' && $m[2] !== '') {
                $suffixLength = min((int) $m[2], $size);
                $start = $size - $suffixLength;
                $end = $size - 1;
            } else {
                if ($m[1] !== '') $start = (int) $m[1];
                if ($m[2] !== '') $end = (int) $m[2];
                if ($m[1] !== '' && $m[2] === '') $end = min($start + 2 * 1024 * 1024 - 1, $size - 1);
            }
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
