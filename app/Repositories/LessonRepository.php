<?php

namespace App\Repositories;

use PDO;

final class LessonRepository implements LessonRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $lesson): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO lessons (module_id, title, summary, content, video_url, file_path, sort_order)
            VALUES (:module_id, :title, :summary, :content, :video_url, :file_path, :sort_order)'
        );

        $statement->execute([
            ':module_id' => (int) ($lesson['module_id'] ?? 0),
            ':title' => trim((string) ($lesson['title'] ?? '')),
            ':summary' => trim((string) ($lesson['summary'] ?? '')),
            ':content' => (string) ($lesson['content'] ?? ''),
            ':video_url' => $lesson['video_url'] ?? null,
            ':file_path' => $lesson['file_path'] ?? null,
            ':sort_order' => (int) ($lesson['sort_order'] ?? 0),
        ]);

        $lesson['id'] = (int) $this->pdo->lastInsertId();

        $module = $this->pdo->prepare('SELECT course_id FROM course_modules WHERE id = :module_id LIMIT 1');
        $module->execute([':module_id' => (int) ($lesson['module_id'] ?? 0)]);
        $moduleData = $module->fetch();
        $lesson['course_id'] = $moduleData['course_id'] ?? 0;

        return $lesson;
    }

    public function update(int $id, array $data): ?array
    {
        $existing = $this->findById($id);
        if ($existing === null) {
            return null;
        }

        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = sprintf('%s = :%s', $key, $key);
            $params[':' . $key] = $value;
        }

        if ($fields === []) {
            return $existing;
        }

        $statement = $this->pdo->prepare('UPDATE lessons SET ' . implode(', ', $fields) . ' WHERE id = :id');
        $statement->execute($params);

        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT l.*, cm.course_id
             FROM lessons l
             INNER JOIN course_modules cm ON cm.id = l.module_id
             WHERE l.id = :id
             LIMIT 1'
        );
        $statement->execute([':id' => $id]);
        $lesson = $statement->fetch();

        return $lesson === false ? null : $lesson;
    }

    public function findByModule(int $moduleId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT l.*, cm.course_id
             FROM lessons l
             INNER JOIN course_modules cm ON cm.id = l.module_id
             WHERE l.module_id = :module_id
             ORDER BY l.sort_order ASC, l.id ASC'
        );
        $statement->execute([':module_id' => $moduleId]);

        return $statement->fetchAll() ?: [];
    }

    public function findByCourse(int $courseId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT l.*, cm.course_id
             FROM lessons l
             INNER JOIN course_modules cm ON cm.id = l.module_id
             WHERE cm.course_id = :course_id
             ORDER BY l.sort_order ASC, l.id ASC'
        );
        $statement->execute([':course_id' => $courseId]);

        return $statement->fetchAll() ?: [];
    }
}
