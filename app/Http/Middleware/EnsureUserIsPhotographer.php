<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPhotographer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isPhotographer() && !auth()->user()->isAdmin()) {
            abort(403, 'No tienes permisos de fotógrafo');
        }

        // Verificar que tenga perfil de fotógrafo
        if (auth()->user()->isPhotographer() && !auth()->user()->photographer) {
            abort(403, 'No tienes un perfil de fotógrafo configurado');
        }

        return $next($request);
    }
}
