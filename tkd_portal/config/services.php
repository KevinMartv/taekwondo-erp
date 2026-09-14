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

    /*
    | Módulos del ERP. "url" es la dirección pública (la que abre el navegador)
    | y "api" la que usa el portal para hablar server-to-server: dentro de
    | Docker son distintas (http://tkd_alumnos:8000 vs http://localhost:8001).
    */
    'modules' => [
        'alumnos' => [
            'url' => env('ALUMNOS_URL', 'http://localhost:8001'),
            'api' => env('ALUMNOS_INTERNAL_URL', env('ALUMNOS_URL', 'http://localhost:8001')),
        ],
        'pagos' => [
            'url' => env('PAGOS_URL', 'http://localhost:8002'),
            'api' => env('PAGOS_INTERNAL_URL', env('PAGOS_URL', 'http://localhost:8002')),
        ],
        'pos' => [
            'url' => env('POS_URL', 'http://localhost:8003'),
            'api' => env('POS_INTERNAL_URL', env('POS_URL', 'http://localhost:8003')),
        ],
    ],

    // Secreto compartido con tkd_pagos y tkd_pos para firmar las intenciones
    // que el portal envía por redirección (renovación de suscripción, tienda).
    'portal' => [
        'secret' => env('PORTAL_SECRET', 'tkd-dev-portal-secret'),
    ],

    'suscripcion' => [
        'monto' => (float) env('SUSCRIPCION_MONTO', 500),
    ],

];
