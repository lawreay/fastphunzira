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
        return $this->countFailures(
            static fn (array $entry): bool => strtolower((string) ($entry['email'] ?? '')) === strtolower(trim($email)),
            $windowSeconds,
            strtolower(trim($email))
        );
    }

    public function countFailuresSinceLastSuccessByIp(string $ipAddress, int $windowSeconds): int
    {
        if ($ipAddress === '') {
            return 0;
        }

        return $this->countFailures(
            static fn (array $entry): bool => (string) ($entry['ip_address'] ?? '') === $ipAddress,
            $windowSeconds,
            $ipAddress
        );
    }

    private function countFailures(callable $matches, int $windowSeconds, string $successKey): int
    {
        $cutoff = time() - max(1, $windowSeconds);
        $lastSuccessAt = 0;

        foreach ($this->entries as $entry) {
            $createdAt = strtotime((string) ($entry['created_at'] ?? '')) ?: 0;

            if ($createdAt < $cutoff || !$matches($entry)) {
                continue;
            }

            if (($entry['status'] ?? '') === 'success') {
                $lastSuccessAt = max($lastSuccessAt, $createdAt);
            }
        }

        $count = 0;

        foreach ($this->entries as $entry) {
            $createdAt = strtotime((string) ($entry['created_at'] ?? '')) ?: 0;

            if ($createdAt < $cutoff || $createdAt <= $lastSuccessAt || !$matches($entry)) {
                continue;
            }

            if (($entry['status'] ?? '') === 'failed') {
                $count++;
            }
        }

        return $count;
    }
}
