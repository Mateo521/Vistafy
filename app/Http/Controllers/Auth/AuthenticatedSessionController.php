<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        
        return $this->redirectBasedOnRole();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    
    protected function redirectBasedOnRole(): RedirectResponse
    {
        $user = auth()->user();

       
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

         
        if ($user->role === 'photographer') {
            $photographer = $user->photographer;

            if (!$photographer) {
                
                return redirect()->route('home')
                    ->with('error', 'No se encontró tu perfil de fotógrafo. Contacta a soporte.');
            }

           
            switch ($photographer->status) {
                case 'pending':
                    return redirect()->route('photographer.pending')
                        ->with('info', 'Tu cuenta está pendiente de aprobación.');

                case 'rejected':
                    return redirect()->route('photographer.rejected')
                        ->with('error', 'Tu cuenta fue rechazada.');

                case 'suspended':
                    return redirect()->route('photographer.suspended')
                        ->with('error', 'Tu cuenta está suspendida.');

                case 'approved':
                    return redirect()->route('photographer.dashboard');

                default:
                    return redirect()->route('photographer.pending');
            }
        }

        
        if ($user->role === 'client') {
            return redirect()->route('home');
        }

        
        return redirect()->route('home');
    }
}
