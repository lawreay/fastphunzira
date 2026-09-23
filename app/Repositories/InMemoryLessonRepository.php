<?php

namespace App\Repositories;

final class InMemoryLessonRepository implements LessonRepositoryInterface
{
    private array $lessons = [];

    public function create(array $lesson): array
    {
        $id = count($this->lessons) + 1;
        $lesson['id'] = $id;
        $this->lessons[$id] = $lesson;

        return $lesson;
    }

    public function update(int $id, array $data): ?array
    {
        if (!isset($this->lessons[$id])) {
            return null;
        }

        $this->lessons[$id] = array_merge($this->lessons[$id], $data);

        return $this->lessons[$id];
    }

    public function findById(int $id): ?array
    {
        return $this->lessons[$id] ?? null;
    }

    public function findByModule(int $moduleId): array
    {
        return array_values(array_filter($this->lessons, static fn (array $lesson) => (int) ($lesson['module_id'] ?? 0) === $moduleId));
    }

    public function findByCourse(int $courseId): array
    {
        return array_values(array_filter($this->lessons, static fn (array $lesson) => (int) ($lesson['course_id'] ?? 0) === $courseId));
    }
}
