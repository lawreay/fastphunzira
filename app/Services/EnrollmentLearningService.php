<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\CourseRepositoryInterface;
use App\Repositories\CourseModuleRepositoryInterface;
use App\Repositories\EnrollmentRepositoryInterface;
use App\Repositories\LessonProgressRepositoryInterface;
use App\Repositories\LessonRepositoryInterface;

final class EnrollmentLearningService
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        private EnrollmentRepositoryInterface $enrollmentRepository,
        private CourseModuleRepositoryInterface $moduleRepository,
        private LessonRepositoryInterface $lessonRepository,
        private LessonProgressRepositoryInterface $progressRepository
    ) {
    }

    public function getCourseRepository(): CourseRepositoryInterface
    {
        return $this->courseRepository;
    }

    public function enrollStudentInCourse(int $studentId, int $courseId): array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return ['success' => false, 'code' => 'unauthorized', 'message' => 'You must be logged in as the student to enroll.'];
        }

        $course = $this->courseRepository->findById($courseId);
        if ($course === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Course not found.'];
        }

        if (strtolower((string) ($course['status'] ?? 'draft')) !== 'published') {
            return ['success' => false, 'code' => 'course_unavailable', 'message' => 'This course is not available for enrollment.'];
        }

        if ($this->enrollmentRepository->findByStudentAndCourse($studentId, $courseId) !== null) {
            return ['success' => false, 'code' => 'duplicate_enrollment', 'message' => 'You are already enrolled in this course.'];
        }

        $enrollment = [
            'student_id' => $studentId,
            'course_id' => $courseId,
            'enrolled_at' => date('Y-m-d H:i:s'),
        ];

        return [
            'success' => true,
            'message' => 'Enrollment created successfully.',
            'data' => $this->enrollmentRepository->create($enrollment),
        ];
    }

    public function getStudentEnrollments(int $studentId): array
    {
        return $this->enrollmentRepository->findByStudent($studentId);
    }

    public function createModule(int $courseId, array $data, ?int $actorId = null): array
    {
        if (!Auth::userCan('courses.manage')) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can manage modules.'];
        }

        $actor = $actorId ?? Auth::userId();
        if ($actor === null) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can manage modules.'];
        }

        $course = $this->courseRepository->findById($courseId);
        if ($course === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Course not found.'];
        }

        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $sortOrder = (int) ($data['sort_order'] ?? 0);

        if ($title === '' || $description === '') {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'Module title and description are required.'];
        }

        $module = [
            'course_id' => $courseId,
            'title' => $title,
            'description' => $description,
            'sort_order' => $sortOrder,
            'created_by' => $actor,
        ];

        return ['success' => true, 'message' => 'Module created.', 'data' => $this->moduleRepository->create($module)];
    }

    public function updateModule(int $moduleId, array $data): array
    {
        if (!Auth::userCan('courses.manage')) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can update modules.'];
        }

        $existing = $this->moduleRepository->findById($moduleId);
        if ($existing === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Module not found.'];
        }

        $updates = [];
        if (isset($data['title'])) {
            $updates['title'] = trim((string) $data['title']);
        }
        if (isset($data['description'])) {
            $updates['description'] = trim((string) $data['description']);
        }
        if (isset($data['sort_order'])) {
            $updates['sort_order'] = (int) $data['sort_order'];
        }

        if ($updates === []) {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'No changes supplied.'];
        }

        $updated = $this->moduleRepository->update($moduleId, $updates);

        return ['success' => $updated !== null, 'message' => $updated !== null ? 'Module updated.' : 'Module update failed.', 'data' => $updated];
    }

    public function createLesson(int $moduleId, array $data, ?int $actorId = null): array
    {
        if (!Auth::userCan('courses.manage')) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can manage lessons.'];
        }

        $actor = $actorId ?? Auth::userId();
        if ($actor === null) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'Only administrators can manage lessons.'];
        }

        $module = $this->moduleRepository->findById($moduleId);
        if ($module === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Module not found.'];
        }

        $title = trim((string) ($data['title'] ?? ''));
        $content = trim((string) ($data['content'] ?? ''));
        $sortOrder = (int) ($data['sort_order'] ?? 0);

        if ($title === '' || $content === '') {
            return ['success' => false, 'code' => 'validation_failed', 'message' => 'Lesson title and content are required.'];
        }

        $lesson = [
            'module_id' => $moduleId,
            'course_id' => (int) ($module['course_id'] ?? 0),
            'title' => $title,
            'content' => $content,
            'sort_order' => $sortOrder,
            'created_by' => $actor,
        ];

        return ['success' => true, 'message' => 'Lesson created.', 'data' => $this->lessonRepository->create($lesson)];
    }

    public function getLessonForStudent(int $studentId, int $lessonId): ?array
    {
        $lesson = $this->lessonRepository->findById($lessonId);
        if ($lesson === null) {
            return null;
        }

        $course = $this->courseRepository->findById((int) ($lesson['course_id'] ?? 0));
        if ($course === null || strtolower((string) ($course['status'] ?? 'draft')) !== 'published') {
            return null;
        }

        $enrollment = $this->enrollmentRepository->findByStudentAndCourse($studentId, (int) ($lesson['course_id'] ?? 0));
        if ($enrollment === null) {
            return null;
        }

        return $lesson;
    }

    public function markLessonComplete(int $studentId, int $lessonId): array
    {
        if (!Auth::check() || (int) Auth::userId() !== $studentId) {
            return ['success' => false, 'code' => 'unauthorized', 'message' => 'You are not allowed to update this progress record.'];
        }

        $lesson = $this->lessonRepository->findById($lessonId);
        if ($lesson === null) {
            return ['success' => false, 'code' => 'not_found', 'message' => 'Lesson not found.'];
        }

        $courseId = (int) ($lesson['course_id'] ?? 0);
        $enrollment = $this->enrollmentRepository->findByStudentAndCourse($studentId, $courseId);
        if ($enrollment === null) {
            return ['success' => false, 'code' => 'forbidden', 'message' => 'You must enroll in the course before completing lessons.'];
        }

        $progress = [
            'student_id' => $studentId,
            'course_id' => $courseId,
            'lesson_id' => $lessonId,
            'completed_at' => date('Y-m-d H:i:s'),
        ];

        return ['success' => true, 'message' => 'Lesson marked complete.', 'data' => $this->progressRepository->createOrUpdate($progress)];
    }

    public function getCourseProgress(int $studentId, int $courseId): array
    {
        $course = $this->courseRepository->findById($courseId);
        if ($course === null) {
            return ['course_id' => $courseId, 'percent' => 0.0, 'completed_lessons' => 0, 'total_lessons' => 0];
        }

        $lessons = $this->lessonRepository->findByCourse($courseId);
        $completed = 0;
        foreach ($lessons as $lesson) {
            if ($this->progressRepository->findByStudentAndLesson($studentId, (int) ($lesson['id'] ?? 0)) !== null) {
                $completed++;
            }
        }

        $total = count($lessons);
        if ($total === 0) {
            return [
                'course_id' => $courseId,
                'percent' => 0.0,
                'completed_lessons' => 0,
                'total_lessons' => 0,
            ];
        }

        $percent = round(($completed / $total) * 100, 1);

        return [
            'course_id' => $courseId,
            'percent' => $percent,
            'completed_lessons' => $completed,
            'total_lessons' => $total,
        ];
    }
}
