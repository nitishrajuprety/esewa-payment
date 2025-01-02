<?php

return [
    'payment_environment' => env('ESEWA_PAYMENT_ENVIRONMENT', 'production'),
    'esewa_merchant_key' => env('ESEWA_MERCHANT_KEY'),
    'esewa_secret_key_production' => env('ESEWA_SECRET_KEY_PRODUCTION'),
    'esewa_secret_key' => env('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q'),
    'esewa_epay_base_url' => env('ESEWA_EPAY_ENDPOINT', 'https://epay.esewa.com.np/api/epay/main/v2/form'),
    'esewa_epay_verify_url' => env('ESEWA_VERIFY_ENDPOINT', 'https://epay.esewa.com.np/api/epay/transaction/status/'),
];