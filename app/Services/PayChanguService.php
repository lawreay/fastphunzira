<?php

namespace App\Services;

use App\Repositories\PaymentTransactionRepository;
use App\Repositories\PlatformSettingsRepository;

final class PayChanguService
{
    public function __construct(
        private array $config,
        private PaymentTransactionRepository $transactions,
        private PlatformSettingsRepository $settings
    ) {
    }

    public function enabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false)
            && trim((string) ($this->config['secret_key'] ?? '')) !== '';
    }

    public function premiumPrice(): float
    {
        return max(0, (float) ($this->settings->get('premium_price', '0') ?? '0'));
    }

    public function premiumCurrency(): string
    {
        return strtoupper((string) ($this->settings->get(
            'premium_currency',
            $this->config['currency'] ?? 'MWK'
        ) ?? 'MWK'));
    }

    public function premiumDurationDays(): int
    {
        return max(1, (int) ($this->settings->get('premium_duration_days', '30') ?? '30'));
    }

    public function initiatePremiumCheckout(array $user, string $callbackUrl, string $returnUrl): array
    {
        if (!$this->enabled()) {
            return ['success' => false, 'code' => 'disabled', 'message' => 'PayChangu is not configured.'];
        }

        $amount = $this->premiumPrice();
        if ($amount <= 0) {
            return ['success' => false, 'code' => 'price_not_configured', 'message' => 'The premium plan price has not been configured.'];
        }

        $txRef = 'FP-' . date('YmdHis') . '-' . bin2hex(random_bytes(6));

        $payload = [
            'amount' => (string) $amount,
            'currency' => $this->premiumCurrency(),
            'email' => (string) ($user['email'] ?? ''),
            'first_name' => trim(explode(' ', (string) ($user['full_name'] ?? 'Student'), 2)[0]),
            'last_name' => trim(explode(' ', (string) ($user['full_name'] ?? ''), 2)[1] ?? ''),
            'callback_url' => $callbackUrl,
            'return_url' => $returnUrl,
            'tx_ref' => $txRef,
            'customization' => [
                'title' => 'FastPhunzira Premium',
                'description' => 'Premium student membership',
            ],
            'meta' => [
                'purpose' => 'premium_membership',
                'user_id' => (int) ($user['id'] ?? 0),
            ],
        ];

        $response = $this->request('POST', '/payment', $payload);

        if (!$response['success']) {
            return $response;
        }

        $checkoutUrl = $response['data']['data']['checkout_url'] ?? null;
        if (!is_string($checkoutUrl) || $checkoutUrl === '') {
            return ['success' => false, 'code' => 'provider_error', 'message' => 'PayChangu did not return a checkout URL.'];
        }

        $this->transactions->create([
            'user_id' => (int) ($user['id'] ?? 0),
            'provider' => 'paychangu',
            'tx_ref' => $txRef,
            'purpose' => 'premium_membership',
            'amount' => $amount,
            'currency' => $this->premiumCurrency(),
            'status' => 'pending',
            'metadata' => ['mode' => $this->config['mode'] ?? 'test'],
        ]);

        return [
            'success' => true,
            'tx_ref' => $txRef,
            'checkout_url' => $checkoutUrl,
        ];
    }

    public function verify(string $txRef): array
    {
        if (!$this->enabled()) {
            return ['success' => false, 'code' => 'disabled', 'message' => 'PayChangu is not configured.'];
        }

        return $this->request('GET', '/verify-payment/' . rawurlencode($txRef));
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'code' => 'curl_missing', 'message' => 'The PHP cURL extension is required for PayChangu.'];
        }

        $ch = curl_init(rtrim((string) $this->config['api_base_url'], '/') . $path);
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . (string) $this->config['secret_key'],
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));
        }

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $error !== '') {
            return ['success' => false, 'code' => 'network_error', 'message' => 'Unable to reach PayChangu.'];
        }

        $decoded = json_decode((string) $body, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'code' => 'invalid_response', 'message' => 'PayChangu returned an invalid response.'];
        }

        if ($status < 200 || $status >= 300 || ($decoded['status'] ?? '') !== 'success') {
            return ['success' => false, 'code' => 'provider_error', 'message' => (string) ($decoded['message'] ?? 'PayChangu rejected the request.'), 'data' => $decoded];
        }

        return ['success' => true, 'data' => $decoded];
    }
}
