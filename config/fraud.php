<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fraud Detection Enabled
    |--------------------------------------------------------------------------
    */
    'enabled' => env('FRAUD_DETECTION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Global Decision Thresholds
    |--------------------------------------------------------------------------
    | risk_score <= allow  => allow
    | allow < risk_score < block => review
    | risk_score >= block => block
    */
    'thresholds' => [
        'allow' => (float) env('FRAUD_THRESHOLD_ALLOW', 20.0),
        'block' => (float) env('FRAUD_THRESHOLD_BLOCK', 70.0),

        /*
        | Per-merchant overrides (key = merchant_id).
        | Use when a merchant needs stricter or looser thresholds.
        */
        'merchant_overrides' => [
            // Example: 1 => ['allow' => 25.0, 'block' => 80.0],
        ],

        /*
        | Per-payment-method overrides (key = payment_method e.g. 'card', 'upi').
        */
        'payment_method_overrides' => [
            // Example: 'card' => ['allow' => 15.0, 'block' => 65.0],
        ],

        /*
        | Combined merchant + payment_method (merchant_id => [payment_method => thresholds]).
        | Most specific; applied after merchant and payment_method overrides.
        */
        'merchant_payment_overrides' => [
            // Example: 1 => ['card' => ['allow' => 10.0, 'block' => 60.0]],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule Configuration
    |--------------------------------------------------------------------------
    | Weights and parameters for each rule. Used when registering rules in the
    | FraudEngine (e.g. via a service provider). No magic numbers in rules.
    */
    'rules' => [
        'ip_velocity' => [
            'enabled' => true,
            'window_seconds' => (int) env('FRAUD_IP_VELOCITY_WINDOW', 3600),
            'max_transactions_in_window' => (int) env('FRAUD_IP_VELOCITY_MAX', 10),
            'weight' => (float) env('FRAUD_IP_VELOCITY_WEIGHT', 25.0),
        ],
        'card_velocity' => [
            'enabled' => true,
            'window_seconds' => (int) env('FRAUD_CARD_VELOCITY_WINDOW', 3600),
            'max_transactions_in_window' => (int) env('FRAUD_CARD_VELOCITY_MAX', 5),
            'weight' => (float) env('FRAUD_CARD_VELOCITY_WEIGHT', 30.0),
        ],
        'amount_anomaly' => [
            'enabled' => true,
            'high_amount_threshold' => (float) env('FRAUD_AMOUNT_HIGH', 5000.00),
            'very_high_amount_threshold' => (float) env('FRAUD_AMOUNT_VERY_HIGH', 25000.00),
            'weight_high' => (float) env('FRAUD_AMOUNT_WEIGHT_HIGH', 15.0),
            'weight_very_high' => (float) env('FRAUD_AMOUNT_WEIGHT_VERY_HIGH', 35.0),
        ],
        'geo_mismatch' => [
            'enabled' => true,
            'weight_mismatch' => (float) env('FRAUD_GEO_WEIGHT_MISMATCH', 20.0),
            'weight_high_risk' => (float) env('FRAUD_GEO_WEIGHT_HIGH_RISK', 15.0),
            'high_risk_countries' => array_filter(explode(',', env('FRAUD_GEO_HIGH_RISK_COUNTRIES', ''))),
        ],
        'device_change' => [
            'enabled' => true,
            'weight_new_device' => (float) env('FRAUD_DEVICE_WEIGHT_NEW', 10.0),
            'weight_device_change' => (float) env('FRAUD_DEVICE_WEIGHT_CHANGE', 25.0),
            'ttl_seconds' => (int) env('FRAUD_DEVICE_TTL', 86400),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis
    |--------------------------------------------------------------------------
    | Connection name for velocity and device rules. Null = default connection.
    */
    'redis_connection' => env('FRAUD_REDIS_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Fail-open on Redis errors
    |--------------------------------------------------------------------------
    | If true, when Redis is unavailable rules that depend on it are skipped
    | and the incident is logged. If false, Redis errors may surface and block.
    */
    'fail_open_on_redis' => env('FRAUD_FAIL_OPEN_REDIS', true),
];
