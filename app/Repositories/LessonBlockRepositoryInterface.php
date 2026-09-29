<?php
namespace App\Repositories;
interface LessonBlockRepositoryInterface {
    public function create(array $block): array;
    public function findById(int $id): ?array;
    public function findByLesson(int $lessonId): array;
    public function update(int $id, array $data): ?array;
    public function delete(int $id): bool;
}