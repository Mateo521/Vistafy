<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Debug: ver si llega acá
        \Log::info('EnsureUserIsAdmin middleware ejecutado', [
            'user_id' => auth()->id(),
            'is_admin' => auth()->user()?->is_admin,
        ]);

        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión.');
        }

        if (!auth()->user()->isAdmin()) {
            \Log::warning('Usuario sin permisos de admin intentó acceder', [
                'user_id' => auth()->id(),
                'email' => auth()->user()->email,
                'is_admin' => auth()->user()->is_admin,
            ]);
            
            abort(403, 'No tenés permisos de administrador.');
        }

        return $next($request);
    }
}
