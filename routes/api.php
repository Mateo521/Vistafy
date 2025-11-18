<?php
use App\Http\Controllers\WebhookController;

// Webhook de Mercado Pago (sin autenticación)
Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadoPago']);
