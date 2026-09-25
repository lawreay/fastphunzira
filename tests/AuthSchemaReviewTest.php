<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class AuthSchemaReviewTest extends TestCase
{
    public function testMigrationContainsRequiredAuthSecurityConstraints(): void
    {
        $migration = file_get_contents(__DIR__ . '/../database/migrations/001_create_foundation_schema.sql');
        $seed = file_get_contents(__DIR__ . '/../database/seeders/001_seed_roles_and_permissions.sql');

        $this->assertNotFalse($migration);
        $this->assertNotFalse($seed);

        $this->assertStringContainsString('UNIQUE KEY uq_users_email', $migration);
        $this->assertStringContainsString('password_hash', $migration);
        $this->assertStringContainsString('FOREIGN KEY (role_id) REFERENCES roles', $migration);
        $this->assertStringContainsString('FOREIGN KEY (user_id) REFERENCES users', $migration);
        $this->assertStringContainsString('FOREIGN KEY (role_id) REFERENCES roles', $migration);
        $this->assertStringContainsString("('student'", $seed);
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS login_history', $migration);

        $loginIndexes = file_get_contents(__DIR__ . '/../database/migrations/007_add_login_history_indexes.sql');
        $this->assertNotFalse($loginIndexes);
        $this->assertStringContainsString('idx_login_history_email_created', $loginIndexes);
        $this->assertStringContainsString('idx_login_history_ip_created', $loginIndexes);
    }
}
