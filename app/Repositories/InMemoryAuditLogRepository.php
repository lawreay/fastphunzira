<?php

namespace App\Repositories;

final class InMemoryAuditLogRepository implements AuditLogRepositoryInterface
{
    private array $entries = [];
    private int $nextId = 1;

    public function create(array $event): array
    {
        $event['id'] = $this->nextId++;
        $event['created_at'] = $event['created_at'] ?? date('Y-m-d H:i:s');
        $this->entries[] = $event;

        return $event;
    }

    public function findRecent(int $limit = 20): array
    {
        $items = array_reverse($this->entries);

        return array_slice($items, 0, max(1, (int) $limit));
    }
}
