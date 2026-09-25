<?php

namespace App\Repositories;

final class InMemoryLoginHistoryRepository implements LoginHistoryRepositoryInterface
{
    private array $entries = [];
    private int $nextId = 1;

    public function record(array $entry): array
    {
        $entry['id'] = $this->nextId++;
        $entry['created_at'] = $entry['created_at'] ?? date('Y-m-d H:i:s');
        $this->entries[] = $entry;

        return $entry;
    }

    public function countRecentFailures(string $email, string $ipAddress, string $since): array
    {
        $email = strtolower(trim($email));
        $emailFailures = 0;
        $ipFailures = 0;
        $sinceTimestamp = strtotime($since) ?: 0;

        foreach ($this->entries as $entry) {
            if (($entry['status'] ?? null) !== 'failed') {
                continue;
            }

            $createdAt = strtotime((string) ($entry['created_at'] ?? '')) ?: 0;
            if ($createdAt < $sinceTimestamp) {
                continue;
            }

            if (strtolower((string) ($entry['email'] ?? '')) === $email) {
                $emailFailures++;
            }

            if ((string) ($entry['ip_address'] ?? '') === $ipAddress) {
                $ipFailures++;
            }
        }

        return [
            'email_failures' => $emailFailures,
            'ip_failures' => $ipFailures,
        ];
    }
}
