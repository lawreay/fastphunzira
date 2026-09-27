<?php

return [
    'enabled' => filter_var(getenv('PAYCHANGU_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'mode' => strtolower(getenv('PAYCHANGU_MODE') ?: 'test'),
    'currency' => strtoupper(getenv('PAYCHANGU_CURRENCY') ?: 'MWK'),
    'public_key' => getenv('PAYCHANGU_PUBLIC_KEY') ?: '',
    'secret_key' => getenv('PAYCHANGU_SECRET_KEY') ?: '',
    'webhook_secret' => getenv('PAYCHANGU_WEBHOOK_SECRET') ?: '',
    'api_base_url' => getenv('PAYCHANGU_API_BASE_URL') ?: 'https://api.paychangu.com',
];
