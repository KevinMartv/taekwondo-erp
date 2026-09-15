<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'pos' => [
        'url' => env('POS_URL', 'http://localhost:8003'),
        'public_url' => env('POS_PUBLIC_URL', env('POS_URL', 'http://localhost:8003')),
        'secret' => env('PAGO_SECRET', 'tkd-dev-pago-secret'),
    ],

    'portal' => [
        'url' => env('PORTAL_URL', 'http://localhost:8000'),
        'public_url' => env('PORTAL_PUBLIC_URL', env('PORTAL_URL', 'http://localhost:8000')),
        'secret' => env('PORTAL_SECRET', 'tkd-dev-portal-secret'),
    ],

    'suscripcion' => [
        'monto' => (float) env('SUSCRIPCION_MONTO', 500),
        'ciclo' => env('SUSCRIPCION_CICLO', 'mes'),
    ],

];
