<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\AuditLogRepositoryInterface;

final class AuditLogService
{
    public function __construct(private AuditLogRepositoryInterface $repository)
    {
    }

    public function record(array $event): array
    {
        $payload = [
            'user_id' => isset($event['user_id']) ? (int) $event['user_id'] : Auth::userId(),
            'action' => trim((string) ($event['action'] ?? 'system_event')),
            'entity_type' => isset($event['entity_type']) ? trim((string) $event['entity_type']) : null,
            'entity_id' => isset($event['entity_id']) ? (int) $event['entity_id'] : null,
            'old_values' => $event['old_values'] ?? null,
            'new_values' => $event['new_values'] ?? ($event['details'] ?? null),
            'ip_address' => $event['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent' => $event['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null),
        ];

        if ($payload['action'] === '') {
            $payload['action'] = 'system_event';
        }

        return $this->repository->create($payload);
    }

    public function getRecentForAdmin(): array
    {
        if (!Auth::userCan('courses.manage')) {
            return [];
        }

        return $this->repository->findRecent(20);
    }
}
