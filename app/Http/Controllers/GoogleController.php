<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

   
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

          
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
             
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            } else {
             
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'role' => 'client',  
                    'password' => null,   
                ]);
            }

   
            Auth::login($user);
 
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->isPhotographer()) {
                return redirect()->route('photographer.dashboard');
            }

            return redirect()->route('home');  

        } catch (\Exception $e) {
             
            return redirect()->route('login')->withErrors([
                'email' => 'Error en la autenticación con Google. Inténtalo de nuevo.'
            ]);
        }
    }
}