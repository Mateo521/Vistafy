<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        //  Registrar alias de middleware
        $middleware->alias([
            'photographer' => \App\Http\Middleware\EnsureUserIsPhotographer::class,
            'photographer.approved' => \App\Http\Middleware\EnsurePhotographerIsApproved::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class, //  VERIFICAR ESTO
        ]);

        // Excluir webhooks de la verificación CSRF
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
