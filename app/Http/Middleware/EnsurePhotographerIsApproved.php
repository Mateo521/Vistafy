<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhotographerIsApproved
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el fotógrafo esté aprobado antes de acceder a rutas protegidas
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión.');
        }

        $user = auth()->user();

        // Verificar que sea fotógrafo
        if (!$user->isPhotographer()) {
            abort(403, 'Esta área es solo para fotógrafos.');
        }

        // Obtener el perfil de fotógrafo
        $photographer = $user->photographer;

        if (!$photographer) {
            abort(403, 'No tenés un perfil de fotógrafo asociado.');
        }

        //  Verificar el estado del fotógrafo
        switch ($photographer->status) {
            case 'pending':
                return redirect()->route('photographer.pending');
            
            case 'rejected':
                return redirect()->route('photographer.rejected');
            
            case 'suspended':
                return redirect()->route('photographer.suspended');
            
            case 'approved':
                //  Fotógrafo aprobado, puede continuar
                return $next($request);
            
            default:
                abort(403, 'Estado de cuenta no válido.');
        }
    }
}
