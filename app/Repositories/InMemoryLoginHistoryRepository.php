<?php

namespace App\Repositories;

final class InMemoryLoginHistoryRepository implements LoginHistoryRepositoryInterface
{
    private array $entries = [];
    private int $nextId = 1;

    public function record(array $attempt): array
    {
        $attempt['id'] = $this->nextId++;
        $attempt['email'] = strtolower(trim((string) ($attempt['email'] ?? '')));
        $attempt['created_at'] = $attempt['created_at'] ?? date('Y-m-d H:i:s');
        $this->entries[] = $attempt;

        return $attempt;
    }

    public function countFailuresSinceLastSuccessByEmail(string $email, int $windowSeconds): int
    {
        $email = strtolower(trim($email));
        $cutoff = time() - max(1, $windowSeconds);
        $lastSuccessAt = 0;

        foreach ($this->entries as $entry) {
            $createdAt = strtotime((string) ($entry['created_at'] ?? '')) ?: 0;

            if ($createdAt < $cutoff || strtolower((string) ($entry['email'] ?? '')) !== $email) {
                continue;
            }

            if (($entry['status'] ?? '') === 'success') {
                $lastSuccessAt = max($lastSuccessAt, $createdAt);
            }
        }

        $count = 0;

        foreach ($this->entries as $entry) {
            $createdAt = strtotime((string) ($entry['created_at'] ?? '')) ?: 0;

            if (
                $createdAt < $cutoff
                || $createdAt <= $lastSuccessAt
                || strtolower((string) ($entry['email'] ?? '')) !== $email
                || ($entry['status'] ?? '') !== 'failed'
            ) {
                continue;
            }

            $count++;
        }

        return $count;
    }

    public function countFailuresByIp(string $ipAddress, int $windowSeconds): int
    {
        if ($ipAddress === '') {
            return 0;
        }

        $cutoff = time() - max(1, $windowSeconds);
        $count = 0;

        foreach ($this->entries as $entry) {
            $createdAt = strtotime((string) ($entry['created_at'] ?? '')) ?: 0;

            if (
                $createdAt >= $cutoff
                && (string) ($entry['ip_address'] ?? '') === $ipAddress
                && ($entry['status'] ?? '') === 'failed'
            ) {
                $count++;
            }
        }

        return $count;
    }
}
