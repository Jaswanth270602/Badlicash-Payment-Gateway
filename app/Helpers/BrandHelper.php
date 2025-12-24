<?php

if (!function_exists('brand_name')) {
    /**
     * Get the brand/company name from configuration.
     * This allows easy rebranding by changing APP_NAME in .env
     *
     * @return string
     */
    function brand_name(): string
    {
        return config('app.name', 'Payment Gateway');
    }
}

