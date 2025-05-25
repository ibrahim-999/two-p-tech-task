<?php

return [
    'clickpay' => [
        'profile_id' => env('CLICKPAY_PROFILE_ID' ),
        'server_key' => env('CLICKPAY_SERVER_KEY'),
        'client_key' => env('CLICKPAY_CLIENT_KEY',),
        'is_live' => env('CLICKPAY_IS_LIVE', false),
        'base_url' => env('CLICKPAY_BASE_URL', 'https://secure-egypt.clickpay.com.sa'),
    ],

    'default_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'clickpay'),
    'currency' => env('DEFAULT_CURRENCY', 'EGP'),

    'callback_url' => env('APP_URL') . '/api/v1/payments/callback',
    'return_url' => env('APP_URL') . '/payment/success',
];
