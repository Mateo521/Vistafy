<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as$guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // Redirigir según el rol del usuario
                if ($user->isPhotographer()) {
                    return redirect()->route('photographer.dashboard');
                } elseif ($user->isAdmin()) {
                    return redirect('/admin/panel'); // Si lo implementas después
                }
                
                // Cliente normal va a la galería
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}
