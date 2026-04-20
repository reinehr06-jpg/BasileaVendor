<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'asaas' => [
        'api_key'       => env('ASAAS_API_KEY', ''),
        'ambiente'      => env('ASAAS_AMBIENTE', 'sandbox'), // sandbox ou production
        'webhook_token' => env('ASAAS_WEBHOOK_TOKEN', ''),
    ],

    'church' => [
        'url'    => env('CHURCH_API_URL', ''),
        'secret' => env('CHURCH_API_SECRET', ''),
    ],

    'exchangerate' => [
        'api_url'   => env('EXCHANGERATE_API_URL', 'https://api.exchangerate-api.com/v4'),
        'cache_ttl' => env('EXCHANGERATE_CACHE_TTL', 600), // 10 minutos
    ],

    'checkout' => [
        'default_currency' => env('CHECKOUT_DEFAULT_CURRENCY', 'BRL'),
        'default_language' => env('CHECKOUT_DEFAULT_LANGUAGE', 'pt-BR'),
        'supported_currencies' => ['BRL', 'USD', 'EUR'],
        'supported_languages' => ['pt-BR', 'en-US', 'es-ES'],
    ],

    'git' => [
        'deploy_secret' => env('GIT_DEPLOY_SECRET'),
    ],

    'ia_local' => [
        'endpoint' => env('IA_LOCAL_ENDPOINT', 'http://localhost:11434/api/generate'),
        'model'    => env('IA_LOCAL_MODEL', 'llama3.2'),
    ],

];
