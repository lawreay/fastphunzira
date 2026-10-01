<?php

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

final class SmtpMailer
{
    public function __construct(private array $config)
    {
    }

    public function configured(): bool
    {
        $encryption = (string) ($this->config['encryption'] ?? '');
        $username = trim((string) ($this->config['username'] ?? ''));
        $password = (string) ($this->config['password'] ?? '');

        return !empty($this->config['enabled'])
            && trim((string) ($this->config['host'] ?? '')) !== ''
            && (int) ($this->config['port'] ?? 0) > 0
            && filter_var((string) ($this->config['from_address'] ?? ''), FILTER_VALIDATE_EMAIL) !== false
            && in_array($encryption, ['', 'none', 'tls', 'ssl'], true)
            && (($username === '' && $password === '') || ($username !== '' && $password !== ''));
    }

    public function send(string $recipient, string $subject, string $htmlBody, string $textBody): void
    {
        if (!$this->configured()) {
            throw new RuntimeException('SMTP email is not configured.');
        }

        if (filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('A valid recipient email address is required.');
        }

        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = (string) $this->config['host'];
        $mailer->Port = (int) $this->config['port'];
        $mailer->SMTPAuth = trim((string) ($this->config['username'] ?? '')) !== '';
        $mailer->Username = (string) ($this->config['username'] ?? '');
        $mailer->Password = (string) ($this->config['password'] ?? '');
        $mailer->Timeout = (int) ($this->config['timeout'] ?? 15);
        $mailer->SMTPDebug = 0;
        $mailer->CharSet = PHPMailer::CHARSET_UTF8;

        $encryption = (string) ($this->config['encryption'] ?? 'tls');
        if ($encryption === 'tls' || $encryption === 'ssl') {
            $mailer->SMTPSecure = $encryption === 'tls'
                ? PHPMailer::ENCRYPTION_STARTTLS
                : PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mailer->SMTPAutoTLS = false;
        }

        $mailer->setFrom(
            (string) $this->config['from_address'],
            (string) ($this->config['from_name'] ?? 'FastPhunzira')
        );
        $mailer->addAddress($recipient);
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody;

        try {
            $mailer->send();
        } catch (Exception $exception) {
            throw new RuntimeException('SMTP delivery failed.', 0, $exception);
        }
    }
}