<?php

return [
    'clickpay' => [
        'profile_id' => env('CLICKPAY_PROFILE_ID', '46314'),
        'server_key' => env('CLICKPAY_SERVER_KEY', 'SDJNM66MRT-JKZNJBZLNL-GHMK6LLH96'),
        'client_key' => env('CLICKPAY_CLIENT_KEY', 'CQKMT7-9PP96K-9Q629B-QH7GNP'),
        'is_live' => env('CLICKPAY_IS_LIVE', false),
        'base_url' => env('CLICKPAY_BASE_URL', 'https://secure.clickpay.com.sa'),
    ],

    'default_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'clickpay'),
    'currency' => env('DEFAULT_CURRENCY', 'EGP'),

    'callback_url' => env('APP_URL') . '/api/v1/payments/callback',
    'return_url' => env('APP_URL') . '/payment/success',
];
