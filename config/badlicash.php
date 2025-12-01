<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BadliCash Mode
    |--------------------------------------------------------------------------
    |
    | This value determines the mode of operation for BadliCash.
    | Options: 'test' or 'live'
    |
    */
    'mode' => env('BADLICASH_MODE', 'test'),

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | The current API version for BadliCash.
    |
    */
    'api_version' => env('BADLICASH_API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Fee Configuration
    |--------------------------------------------------------------------------
    |
    | Default fee structure for transactions.
    | percentage: Percentage fee (e.g., 2.5 for 2.5%)
    | flat: Flat fee per transaction
    |
    */
    'fee' => [
        'percentage' => env('BADLICASH_FEE_PERCENTAGE', 2.5),
        'flat' => env('BADLICASH_FEE_FLAT', 0.30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for transactions.
    |
    */
    'default_currency' => env('BADLICASH_DEFAULT_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Bank Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for bank provider endpoints and credentials.
    |
    */
    'bank_provider' => [
        'sandbox_url' => env('BANK_PROVIDER_SANDBOX_URL', 'http://localhost/api/sandbox/bank'),
        'live_url' => env('BANK_PROVIDER_LIVE_URL', 'https://api.bank-provider.com'),
        'api_key' => env('BANK_PROVIDER_API_KEY'),
        'api_secret' => env('BANK_PROVIDER_API_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for webhook delivery and retry logic.
    |
    */
    'webhook' => [
        'max_retry_attempts' => env('WEBHOOK_MAX_RETRY_ATTEMPTS', 5),
        'retry_delay_seconds' => env('WEBHOOK_RETRY_DELAY_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | API rate limiting configuration per API key.
    |
    */
    'rate_limit' => [
        'per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
        'per_hour' => env('API_RATE_LIMIT_PER_HOUR', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default and maximum pagination limits.
    |
    */
    'pagination' => [
        'default_per_page' => env('DEFAULT_PER_PAGE', 10),
        'max_per_page' => env('MAX_PER_PAGE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Link Expiry
    |--------------------------------------------------------------------------
    |
    | Default expiry time for payment links in hours.
    |
    */
    'payment_link_expiry_hours' => 24,

    /*
    |--------------------------------------------------------------------------
    | Settlement Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for settlement processing.
    |
    */
    'settlement' => [
        'batch_size' => 100,
        'min_amount' => 10.00,
    ],
];

