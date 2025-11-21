<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mercado Pago Configuration
    |--------------------------------------------------------------------------
    */

    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
    'test_mode' => env('MERCADOPAGO_TEST_MODE', false),
    'test_buyer_email' => env('MERCADOPAGO_TEST_BUYER_EMAIL'),


 
    'test_buyer_dni' => env('MP_TEST_BUYER_DNI', '12345678'),


    // URLs de callback
    'success_url' => env('APP_URL') . '/payment/success',
    'failure_url' => env('APP_URL') . '/payment/failure',
    'pending_url' => env('APP_URL') . '/payment/pending',
    'notification_url' => env('APP_URL') . '/api/webhooks/mercadopago',
];
