<?php

namespace App\Repositories;

final class InMemoryLessonMaterialRepository implements LessonMaterialRepositoryInterface
{
    private array $materials = [];
    public function create(array $material): array { $id = count($this->materials) + 1; $material['id'] = $id; $this->materials[$id] = $material; return $material; }
    public function findById(int $id): ?array { return $this->materials[$id] ?? null; }
    public function findByLesson(int $lessonId): array { return array_values(array_filter($this->materials, static fn(array $m): bool => (int) ($m['lesson_id'] ?? 0) === $lessonId)); }
    public function update(int $id, array $data): ?array { if (!isset($this->materials[$id])) return null; $this->materials[$id] = array_merge($this->materials[$id], $data); return $this->materials[$id]; }
    public function delete(int $id): bool { if (!isset($this->materials[$id])) return false; unset($this->materials[$id]); return true; }
}
