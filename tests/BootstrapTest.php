<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BootstrapTest extends TestCase
{
    public function testAppConfigLoads(): void
    {
        $config = require __DIR__ . '/../config/app.php';

        $this->assertSame('FastPhunzira', $config['app_name']);
        $this->assertSame('local', $config['app_env']);
    }

    public function testSecurityConfigHasSessionLifetime(): void
    {
        $config = require __DIR__ . '/../config/security.php';

        $this->assertArrayHasKey('session_lifetime', $config);
        $this->assertGreaterThan(0, $config['session_lifetime']);
    }
}
