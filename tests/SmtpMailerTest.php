<?php

use App\Services\SmtpMailer;
use PHPUnit\Framework\TestCase;

final class SmtpMailerTest extends TestCase
{
    private function validConfig(): array
    {
        return [
            'enabled' => true,
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'mailer@example.com',
            'password' => 'test-only-password',
            'encryption' => 'tls',
            'from_address' => 'no-reply@example.com',
            'from_name' => 'FastPhunzira',
            'timeout' => 10,
        ];
    }

    public function testCompleteTlsConfigurationIsReady(): void
    {
        self::assertTrue((new SmtpMailer($this->validConfig()))->configured());
    }

    public function testDisabledMailConfigurationIsNotReady(): void
    {
        $config = $this->validConfig();
        $config['enabled'] = false;

        self::assertFalse((new SmtpMailer($config))->configured());
    }

    public function testPartialSmtpCredentialsAreNotReady(): void
    {
        $config = $this->validConfig();
        $config['password'] = '';

        self::assertFalse((new SmtpMailer($config))->configured());
    }

    public function testUnsupportedEncryptionIsNotReady(): void
    {
        $config = $this->validConfig();
        $config['encryption'] = 'invalid';

        self::assertFalse((new SmtpMailer($config))->configured());
    }
}