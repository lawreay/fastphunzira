<?php

namespace App\Services;

use App\Repositories\LoginHistoryRepositoryInterface;

final class LoginSecurityService
{
    public function __construct(
        private LoginHistoryRepositoryInterface $historyRepository,
        private AuditLogService $auditLogService,
        private int $windowSeconds = 900,
        private int $maxFailuresPerEmail = 5,
        private int $maxFailuresPerIp = 20
    ) {
    }

    public function isRateLimited(string $email, string $ipAddress): bool
    {
        if ($this->windowSeconds <= 0) {
            return false;
        }

        $since = date('Y-m-d H:i:s', time() - $this->windowSeconds);
        $counts = $this->historyRepository->countRecentFailures($email, $ipAddress, $since);

        return ($this->maxFailuresPerEmail > 0 && $counts['email_failures'] >= $this->maxFailuresPerEmail)
            || ($this->maxFailuresPerIp > 0 && $counts['ip_failures'] >= $this->maxFailuresPerIp);
    }

    public function recordFailure(string $email, ?int $userId = null): void
    {
        $this->historyRepository->record([
            'user_id' => $userId,
            'email' => $email,
            'status' => 'failed',
            'ip_address' => $this->ipAddress(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        $this->auditLogService->record([
            'user_id' => $userId,
            'action' => 'login_failed',
            'entity_type' => $userId !== null ? 'user' : null,
            'entity_id' => $userId,
            'details' => ['reason' => 'invalid_or_inactive_credentials'],
        ]);
    }

    public function recordSuccess(int $userId, string $email): void
    {
        $this->historyRepository->record([
            'user_id' => $userId,
            'email' => $email,
            'status' => 'success',
            'ip_address' => $this->ipAddress(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    private function ipAddress(): ?string
    {
        $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

        return $ip !== '' ? $ip : null;
    }
}
