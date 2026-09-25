<?php

namespace App\Services;

use App\Repositories\LoginHistoryRepositoryInterface;

final class LoginSecurityService
{
    public function __construct(
        private LoginHistoryRepositoryInterface $repository,
        private array $config = []
    ) {
    }

    public function isThrottled(string $email, string $ipAddress): bool
    {
        $window = $this->windowSeconds();

        $accountFailures = $this->repository->countFailuresSinceLastSuccessByEmail($email, $window);
        $ipFailures = $this->repository->countFailuresSinceLastSuccessByIp($ipAddress, $window);

        return $accountFailures >= $this->maxFailuresPerAccount()
            || ($ipAddress !== '' && $ipFailures >= $this->maxFailuresPerIp());
    }

    public function recordFailure(string $email, ?int $userId = null): void
    {
        $this->repository->record([
            'user_id' => $userId,
            'email' => strtolower(trim($email)),
            'status' => 'failed',
            'ip_address' => $this->clientIp(),
            'user_agent' => $this->userAgent(),
        ]);
    }

    public function recordSuccess(string $email, int $userId): void
    {
        $this->repository->record([
            'user_id' => $userId,
            'email' => strtolower(trim($email)),
            'status' => 'success',
            'ip_address' => $this->clientIp(),
            'user_agent' => $this->userAgent(),
        ]);
    }

    public function failureCounts(string $email, string $ipAddress): array
    {
        $window = $this->windowSeconds();

        return [
            'account' => $this->repository->countFailuresSinceLastSuccessByEmail($email, $window),
            'ip' => $ipAddress === '' ? 0 : $this->repository->countFailuresSinceLastSuccessByIp($ipAddress, $window),
        ];
    }

    private function maxFailuresPerAccount(): int
    {
        return max(1, (int) ($this->config['max_failures_per_account'] ?? 5));
    }

    private function maxFailuresPerIp(): int
    {
        return max(1, (int) ($this->config['max_failures_per_ip'] ?? 20));
    }

    private function windowSeconds(): int
    {
        return max(1, (int) ($this->config['window_seconds'] ?? 900));
    }

    private function clientIp(): ?string
    {
        $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }

    private function userAgent(): ?string
    {
        $userAgent = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

        return $userAgent === '' ? null : substr($userAgent, 0, 1000);
    }
}
