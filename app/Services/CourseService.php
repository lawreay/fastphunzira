<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\CourseRepositoryInterface;

final class CourseService
{
    public function __construct(private CourseRepositoryInterface $courseRepository)
    {
    }

    public function createCourse(array $data, int $actorId): array
    {
        $isAdmin = Auth::userCan('courses.manage');

        if (!$isAdmin || !Auth::check() || (int) Auth::userId() !== $actorId) {
            return [
                'success' => false,
                'code' => 'forbidden',
                'message' => 'Only authenticated administrators can create courses.',
            ];
        }

        $title = trim((string) ($data['title'] ?? ''));
        $slug = $this->normalizeSlug((string) ($data['slug'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $status = strtolower((string) ($data['status'] ?? 'draft'));

        if ($title === '' || $slug === '' || $description === '') {
            return [
                'success' => false,
                'code' => 'validation_failed',
                'message' => 'Course title, slug, and description are required.',
            ];
        }

        if ($this->courseRepository->findBySlug($slug) !== null) {
            return [
                'success' => false,
                'code' => 'duplicate_slug',
                'message' => 'A course with this slug already exists.',
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

    public function updateCourse(int $courseId, array $data, int $actorId): array
    {
        $isAdmin = Auth::userCan('courses.manage');

        if (!$isAdmin || !Auth::check() || (int) Auth::userId() !== $actorId) {
            return [
                'success' => false,
                'code' => 'forbidden',
                'message' => 'Only authenticated administrators can update courses.',
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
            $updates['slug'] = $this->normalizeSlug((string) $data['slug']);
        }

        if (isset($data['description'])) {
            $updates['description'] = trim((string) $data['description']);
        }

        if (isset($data['status'])) {
            $status = strtolower((string) $data['status']);
            $updates['status'] = in_array($status, ['draft', 'published'], true) ? $status : 'draft';
        }

        foreach (['title', 'slug', 'description'] as $field) {
            if (array_key_exists($field, $updates) && trim((string) $updates[$field]) === '') {
                return [
                    'success' => false,
                    'code' => 'validation_failed',
                    'message' => 'Course title, slug, and description are required.',
                ];
            }
        }

        if (isset($updates['slug'])) {
            $duplicate = $this->courseRepository->findBySlug($updates['slug']);
            if ($duplicate !== null && (int) ($duplicate['id'] ?? 0) !== $courseId) {
                return [
                    'success' => false,
                    'code' => 'duplicate_slug',
                    'message' => 'A course with this slug already exists.',
                ];
            }
        }

        $updated = $this->courseRepository->update($courseId, $updates);

        return [
            'success' => $updated !== null,
            'message' => $updated !== null ? 'Course updated.' : 'Course update failed.',
            'data' => $updated,
        ];
    }

    public function getCourseDetail(int $courseId, bool $includeDraft = false): ?array
    {
        $course = $this->courseRepository->findById($courseId);

        if ($course === null) {
            return null;
        }

        if (!$includeDraft && strtolower((string) ($course['status'] ?? 'draft')) !== 'published') {
            return null;
        }

        return $course;
    }

    public function getPublishedCourses(): array
    {
        return $this->courseRepository->findPublished();
    }

    public function getAdminCourses(): array
    {
        return $this->courseRepository->findAll();
    }

    private function normalizeSlug(string $slug): string
    {
        $normalized = strtolower(trim($slug));
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-');

        return $normalized;
    }
}
