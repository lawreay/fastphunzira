<?php

namespace App\Repositories;

use PDO;

final class PlatformSettingsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $statement = $this->pdo->prepare(
            'SELECT setting_value FROM platform_settings WHERE setting_key = :setting_key LIMIT 1'
        );
        $statement->execute([':setting_key' => $key]);
        $row = $statement->fetch();

        return $row === false ? $default : ($row['setting_value'] ?? $default);
    }

    public function set(string $key, string $value): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO platform_settings (setting_key, setting_value)
             VALUES (:setting_key, :setting_value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $statement->execute([
            ':setting_key' => $key,
            ':setting_value' => $value,
        ]);
    }
}
