<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{

    public function enable(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();


        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }


        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );


        $qrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrCodeUrl);

        return Inertia::render('Auth/EnableTwoFactor', [
            'qrCode' => $qrImage,
            'secret' => $user->two_factor_secret
        ]);
    }


    public function challenge(Request $request)
    {
        if (!$request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }
    public function verifyChallenge(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);
        
        $userId = $request->session()->get('2fa:user:id');
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            \Illuminate\Support\Facades\Auth::login($user);
            $request->session()->regenerate();
            $request->session()->forget('2fa:user:id');

            return app(\App\Http\Controllers\Auth\AuthenticatedSessionController::class)->redirectBasedOnRole();
        }

        return back()->withErrors(['code' => 'Código incorrecto. Inténtalo de nuevo.']);
    }


    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);
        
        $user = $request->user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $user->two_factor_enabled = true;
            $user->save();


            $request->session()->put('2fa_passed', true);

            return redirect()->route('photographer.profile.edit')->with('success', 'Autenticación de 2 factores activada.');
        }

        return back()->withErrors(['code' => 'El código Ingresádo es incorrecto. Intentalo de nuevo.']);
    }


    public function disable(Request $request)
    {
        $user = $request->user();
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->save();

        return back()->with('success', 'Autenticación de 2 factores desactivada.');
    }
}