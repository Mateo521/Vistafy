<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhotographerIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no es fotógrafo, continuar
        if ($user->role !== 'photographer') {
            return $next($request);
        }

        // Verificar si tiene perfil de fotógrafo
        if (!$user->photographer) {
            return redirect()->route('home')->with('error', 'No tienes un perfil de fotógrafo.');
        }

        // Si está pendiente, redirigir a página de espera
        if ($user->photographer->isPending()) {
            return redirect()->route('photographer.pending');
        }

        // Si está rechazado
        if ($user->photographer->status === 'rejected') {
            return redirect()->route('photographer.rejected');
        }

        // Si está suspendido
        if ($user->photographer->status === 'suspended') {
            return redirect()->route('photographer.suspended');
        }

        // Si está aprobado, continuar
        return $next($request);
    }
}
