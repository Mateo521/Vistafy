<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mercado Pago Configuration
    |--------------------------------------------------------------------------
    */

    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
    
    // URLs de callback
    'success_url' => env('APP_URL') . '/payment/success',
    'failure_url' => env('APP_URL') . '/payment/failure',
    'pending_url' => env('APP_URL') . '/payment/pending',
    'notification_url' => env('APP_URL') . '/api/webhooks/mercadopago',
];
