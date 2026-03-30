<?php

return [
    'base_url' => env('CHECKOUT_API_URL', 'http://localhost:8001'),
    'api_key' => env('CHECKOUT_API_KEY', ''),
    'webhook_secret' => env('CHECKOUT_WEBHOOK_SECRET', ''),
    'timeout' => env('CHECKOUT_TIMEOUT', 30),
    'webhook_path' => '/webhook/checkout',

    'default_payment_data' => [
        'currency' => 'BRL',
        'metadata' => [
            'source' => 'basileia-vendas',
        ],
    ],
];
