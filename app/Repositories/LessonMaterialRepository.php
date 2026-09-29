<?php

namespace App\Repositories;

use PDO;

final class LessonMaterialRepository implements LessonMaterialRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function create(array $material): array
    {
        $statement = $this->pdo->prepare('INSERT INTO lesson_materials (lesson_id, title, type, original_name, stored_name, mime_type, file_size, storage_path, download_allowed, sort_order) VALUES (:lesson_id, :title, :type, :original_name, :stored_name, :mime_type, :file_size, :storage_path, :download_allowed, :sort_order)');
        $statement->execute([
            ':lesson_id' => (int) ($material['lesson_id'] ?? 0), ':title' => trim((string) ($material['title'] ?? '')),
            ':type' => $material['type'] ?? 'other', ':original_name' => (string) ($material['original_name'] ?? ''),
            ':stored_name' => (string) ($material['stored_name'] ?? ''), ':mime_type' => (string) ($material['mime_type'] ?? 'application/octet-stream'),
            ':file_size' => (int) ($material['file_size'] ?? 0), ':storage_path' => (string) ($material['storage_path'] ?? ''),
            ':download_allowed' => !empty($material['download_allowed']) ? 1 : 0, ':sort_order' => (int) ($material['sort_order'] ?? 0),
        ]);
        $material['id'] = (int) $this->pdo->lastInsertId();
        return $material;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM lesson_materials WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]); $material = $statement->fetch();
        return $material === false ? null : $material;
    }

    public function findByLesson(int $lessonId): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM lesson_materials WHERE lesson_id = :lesson_id ORDER BY sort_order ASC, id ASC');
        $statement->execute([':lesson_id' => $lessonId]);
        return $statement->fetchAll() ?: [];
    }

    public function update(int $id, array $data): ?array
    {
        $allowed = ['title', 'download_allowed', 'sort_order']; $fields = []; $params = [':id' => $id];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) continue;
            $fields[] = $key . ' = :' . $key;
            $params[':' . $key] = $key === 'download_allowed' ? (!empty($value) ? 1 : 0) : $value;
        }
        if ($fields === []) return $this->findById($id);
        $statement = $this->pdo->prepare('UPDATE lesson_materials SET ' . implode(', ', $fields) . ' WHERE id = :id');
        $statement->execute($params);
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM lesson_materials WHERE id = :id');
        $statement->execute([':id' => $id]);
        return $statement->rowCount() > 0;
    }
}
