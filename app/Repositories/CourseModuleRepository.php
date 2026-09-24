<?php

namespace App\Repositories;

use PDO;

final class CourseModuleRepository implements CourseModuleRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $module): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO course_modules (course_id, title, description, sort_order)
            VALUES (:course_id, :title, :description, :sort_order)'
        );

        $statement->execute([
            ':course_id' => (int) ($module['course_id'] ?? 0),
            ':title' => trim((string) ($module['title'] ?? '')),
            ':description' => trim((string) ($module['description'] ?? '')),
            ':sort_order' => (int) ($module['sort_order'] ?? 0),
        ]);

        $module['id'] = (int) $this->pdo->lastInsertId();

        return $module;
    }

    public function update(int $id, array $data): ?array
    {
        $existing = $this->findById($id);
        if ($existing === null) {
            return null;
        }

        $allowedColumns = ['course_id', 'title', 'description', 'sort_order'];
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedColumns, true)) {
                continue;
            }

            $fields[] = sprintf('%s = :%s', $key, $key);
            $params[':' . $key] = $value;
        }

        if ($fields === []) {
            return $existing;
        }

        $statement = $this->pdo->prepare('UPDATE course_modules SET ' . implode(', ', $fields) . ' WHERE id = :id');
        $statement->execute($params);

        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM course_modules WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $module = $statement->fetch();

        return $module === false ? null : $module;
    }

    public function findByCourse(int $courseId): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM course_modules WHERE course_id = :course_id ORDER BY sort_order ASC, id ASC');
        $statement->execute([':course_id' => $courseId]);

        return $statement->fetchAll() ?: [];
    }
}
