<?php

namespace App\Repositories;

final class InMemoryCourseRepository implements CourseRepositoryInterface
{
    private array $courses = [];

    public function create(array $course): array
    {
        $id = count($this->courses) + 1;
        $course['id'] = $id;
        $course['status'] = $course['status'] ?? 'draft';
        $this->courses[$id] = $course;

        return $course;
    }

    public function update(int $id, array $course): ?array
    {
        if (!isset($this->courses[$id])) {
            return null;
        }

        $this->courses[$id] = array_merge($this->courses[$id], $course);

        return $this->courses[$id];
    }

    public function findById(int $id): ?array
    {
        return $this->courses[$id] ?? null;
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->courses as $course) {
            if (strtolower((string) ($course['slug'] ?? '')) === strtolower($slug)) {
                return $course;
            }
        }

        return null;
    }

    public function findAll(): array
    {
        return $this->getAll();
    }

    public function findPublished(): array
    {
        return $this->getPublished();
    }

    public function getAll(): array
    {
        return array_values($this->courses);
    }

    public function getPublished(): array
    {
        return array_values(array_filter($this->courses, static fn (array $course) => ($course['status'] ?? 'draft') === 'published'));
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->findBySlug($slug);
    }
}
