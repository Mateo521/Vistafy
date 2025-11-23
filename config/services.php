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

    'mercadopago' => [
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),

        //  Modo Sandbox / Test
        'test_mode' => env('MP_TEST_MODE', false), // << usa este SIEMPRE
        'sandbox_mode' => env('MP_TEST_MODE', false), // << opcional, pero útil si algo lo referencia

        //  Datos del comprador de prueba
        'test_buyer_email' => env('MP_TEST_BUYER_EMAIL'),
        'test_buyer_dni' => env('MP_TEST_BUYER_DNI', '12345678'),

        // URLs de retorno
        'success_url' => env('APP_URL') . '/payment/success',
        'failure_url' => env('APP_URL') . '/payment/failure',
        'pending_url' => env('APP_URL') . '/payment/pending',

        // Webhook
        'notification_url' => env('APP_URL') . '/api/webhooks/mercadopago',
    ],


    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
