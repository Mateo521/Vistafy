<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        try {
            
            Mail::send('emails.customer-welcome', ['user' => $user], function ($msg) use ($user) {
                $msg->to($user->email)->subject('[F33 STUDIO] Acceso Concedido');
            });
        } catch (\Exception $e) {
            \Log::error('Error enviando correo a cliente nuevo: '.$e->getMessage());
        }

        event(new Registered($user));

        Auth::login($user);

        if ($user->isPhotographer()) {
            return redirect()->route('photographer.dashboard');
        }

        return redirect()->route('home');
    }
}
