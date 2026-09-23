<?php

namespace App\Repositories;

use PDO;

final class CourseRepository implements CourseRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $course): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO courses (title, slug, description, status, created_by, created_at)
            VALUES (:title, :slug, :description, :status, :created_by, NOW())'
        );

        $statement->execute([
            ':title' => $course['title'],
            ':slug' => $course['slug'],
            ':description' => $course['description'],
            ':status' => $course['status'] ?? 'draft',
            ':created_by' => (int) ($course['created_by'] ?? 0),
        ]);

        $course['id'] = (int) $this->pdo->lastInsertId();

        return $course;
    }

    public function update(int $id, array $course): ?array
    {
        $fields = [];
        $params = [':id' => $id];

        foreach ($course as $key => $value) {
            $fields[] = sprintf('%s = :%s', $key, $key);
            $params[':' . $key] = $value;
        }

        if ($fields === []) {
            return $this->findById($id);
        }

        $sql = 'UPDATE courses SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.*, u.full_name AS created_by_name
             FROM courses c
             LEFT JOIN users u ON u.id = c.created_by
             WHERE c.id = :id
             LIMIT 1'
        );

        $statement->execute([':id' => $id]);
        $course = $statement->fetch();

        return $course === false ? null : $course;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.*, u.full_name AS created_by_name
             FROM courses c
             LEFT JOIN users u ON u.id = c.created_by
             WHERE c.slug = :slug
             LIMIT 1'
        );

        $statement->execute([':slug' => $slug]);
        $course = $statement->fetch();

        return $course === false ? null : $course;
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
        $statement = $this->pdo->query(
            'SELECT c.*, u.full_name AS created_by_name
             FROM courses c
             LEFT JOIN users u ON u.id = c.created_by
             ORDER BY c.created_at DESC'
        );

        return $statement->fetchAll() ?: [];
    }

    public function getPublished(): array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.*, u.full_name AS created_by_name
             FROM courses c
             LEFT JOIN users u ON u.id = c.created_by
             WHERE c.status = :status
             ORDER BY c.created_at DESC'
        );

        $statement->execute([':status' => 'published']);

        return $statement->fetchAll() ?: [];
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->findBySlug($slug);
    }
}
