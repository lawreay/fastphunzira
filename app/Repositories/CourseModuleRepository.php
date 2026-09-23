<?php

namespace App\Repositories;

final class CourseModuleRepository implements CourseModuleRepositoryInterface
{
    private array $modules = [];

    public function create(array $module): array
    {
        $id = count($this->modules) + 1;
        $module['id'] = $id;
        $this->modules[$id] = $module;

        return $module;
    }

    public function update(int $id, array $data): ?array
    {
        if (!isset($this->modules[$id])) {
            return null;
        }

        $this->modules[$id] = array_merge($this->modules[$id], $data);

        return $this->modules[$id];
    }

    public function findById(int $id): ?array
    {
        return $this->modules[$id] ?? null;
    }

    public function findByCourse(int $courseId): array
    {
        return array_values(array_filter($this->modules, static fn (array $module) => (int) ($module['course_id'] ?? 0) === $courseId));
    }
}
