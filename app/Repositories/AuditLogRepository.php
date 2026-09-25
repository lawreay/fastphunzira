<?php

namespace App\Repositories;

use PDO;

final class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $event): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
             VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent)'
        );

        $statement->execute([
            ':user_id' => isset($event['user_id']) ? (int) $event['user_id'] : null,
            ':action' => (string) ($event['action'] ?? 'system_event'),
            ':entity_type' => isset($event['entity_type']) ? (string) $event['entity_type'] : null,
            ':entity_id' => isset($event['entity_id']) ? (int) $event['entity_id'] : null,
            ':old_values' => isset($event['old_values']) ? json_encode($event['old_values']) : null,
            ':new_values' => isset($event['new_values']) ? json_encode($event['new_values']) : null,
            ':ip_address' => isset($event['ip_address']) ? (string) $event['ip_address'] : null,
            ':user_agent' => isset($event['user_agent']) ? (string) $event['user_agent'] : null,
        ]);

        $event['id'] = (int) $this->pdo->lastInsertId();

        return $event;
    }

    public function findRecent(int $limit = 20): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT :limit');
        $statement->bindValue(':limit', max(1, (int) $limit), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }
}
