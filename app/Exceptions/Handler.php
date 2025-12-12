<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * AGREGAR ESTE MÉTODO
     */
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);
        $status = $response->status();

        // Solo para requests web (no API)
        if (!$request->inertia() && in_array($status, [404, 403, 500, 503])) {
            return $response;
        }

        // Para requests de Inertia
        if ($request->inertia() || $request->header('X-Inertia')) {
            if ($status === 404) {
                return Inertia::render('Errors/404')
                    ->toResponse($request)
                    ->setStatusCode(404);
            }

            if (in_array($status, [403, 500, 503])) {
                return Inertia::render('Errors/Error', [
                    'status' => $status
                ])
                ->toResponse($request)
                ->setStatusCode($status);
            }
        }

        return $response;
    }
}
