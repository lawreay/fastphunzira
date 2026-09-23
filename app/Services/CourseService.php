<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\CourseRepositoryInterface;

final class CourseService
{
    public function __construct(private CourseRepositoryInterface $courseRepository)
    {
    }

    public function createCourse(array $data, int $actorId, ?bool $forceAdminCheck = null): array
    {
        $isAdmin = $forceAdminCheck ?? Auth::userCan('courses.manage');

        if (!$isAdmin) {
            return [
                'success' => false,
                'code' => 'forbidden',
                'message' => 'Only administrators can create courses.',
            ];
        }

        $title = trim((string) ($data['title'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $status = strtolower((string) ($data['status'] ?? 'draft'));

        if ($title === '' || $slug === '' || $description === '') {
            return [
                'success' => false,
                'code' => 'validation_failed',
                'message' => 'Course title, slug, and description are required.',
            ];
        }

        $course = [
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'status' => in_array($status, ['draft', 'published'], true) ? $status : 'draft',
            'created_by' => $actorId,
        ];

        return [
            'success' => true,
            'message' => 'Course created.',
            'data' => $this->courseRepository->create($course),
        ];
    }

    public function updateCourse(int $courseId, array $data, int $actorId, ?bool $forceAdminCheck = null): array
    {
        $isAdmin = $forceAdminCheck ?? Auth::userCan('courses.manage');

        if (!$isAdmin) {
            return [
                'success' => false,
                'code' => 'forbidden',
                'message' => 'Only administrators can update courses.',
            ];
        }

        $existing = $this->courseRepository->findById($courseId);
        if ($existing === null) {
            return [
                'success' => false,
                'code' => 'not_found',
                'message' => 'Course not found.',
            ];
        }

        $updates = [];

        if (isset($data['title'])) {
            $updates['title'] = trim((string) $data['title']);
        }

        if (isset($data['slug'])) {
            $updates['slug'] = trim((string) $data['slug']);
        }

        if (isset($data['description'])) {
            $updates['description'] = trim((string) $data['description']);
        }

        if (isset($data['status'])) {
            $status = strtolower((string) $data['status']);
            $updates['status'] = in_array($status, ['draft', 'published'], true) ? $status : 'draft';
        }

        if (isset($data['created_by'])) {
            $updates['created_by'] = (int) $data['created_by'];
        }

        $updated = $this->courseRepository->update($courseId, $updates);

        return [
            'success' => $updated !== null,
            'message' => $updated !== null ? 'Course updated.' : 'Course update failed.',
            'data' => $updated,
        ];
    }

    public function getCourseDetail(int $courseId): ?array
    {
        return $this->courseRepository->findById($courseId);
    }

    public function getPublishedCourses(): array
    {
        return $this->courseRepository->findPublished();
    }

    public function getAdminCourses(): array
    {
        return $this->courseRepository->findAll();
    }
}
