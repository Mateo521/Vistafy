<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoOAuthController extends Controller
{
    /**
     * Paso 1: Redirigir al fotógrafo a la pantalla de login de Mercado Pago
     */
    public function redirectToProvider()
    {
        $clientId = config('services.mercadopago.client_id');
        
        if (!$clientId) {
            return redirect()->back()->with('error', 'Error de configuración: Faltan credenciales de la plataforma.');
        }

        
        $redirectUri = route('photographer.mercadopago.callback');
        
        // El state es una medida de seguridad extra recomendada
        $state = csrf_token();

        // Armamos la URL oficial de autorización de MP
        $url = "https://auth.mercadopago.com/authorization?client_id={$clientId}&response_type=code&platform_id=mp&state={$state}&redirect_uri={$redirectUri}";

        return redirect($url);
    }

    /**
     * Paso 2: Recibir la respuesta de Mercado Pago e intercambiar el código por el Token Real
     */
    public function handleProviderCallback(Request $request)
    {
        // 1. Verificar si el usuario canceló o hubo un error
        if ($request->has('error') || !$request->has('code')) {
            Log::warning('Vinculación MP cancelada o fallida', $request->all());
            return redirect()->route('photographer.profile.edit')
                ->with('error', 'Se canceló la vinculación con Mercado Pago.');
        }

        $code = $request->get('code');
        $redirectUri = route('photographer.mercadopago.callback');

        try {
            // 2. Pedirle a la API de MP que nos cambie el "código" por los Tokens de acceso
            $response = Http::post('https://api.mercadopago.com/oauth/token', [
                'client_id' => config('services.mercadopago.client_id'),
                'client_secret' => config('services.mercadopago.client_secret'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // 3. Guardar los datos en el perfil del fotógrafo
                $photographer = auth()->user()->photographer;
                $photographer->update([
                    'mp_access_token' => $data['access_token'],
                    'mp_refresh_token' => $data['refresh_token'],
                    'mp_public_key' => $data['public_key'],
                    'mp_user_id' => $data['user_id'],
                ]);

                Log::info('Fotógrafo vinculado a MP exitosamente', ['photographer_id' => $photographer->id]);

                return redirect()->route('photographer.profile.edit')
                    ->with('success', '¡Excelente! Tu cuenta de Mercado Pago fue vinculada exitosamente. Ya podés recibir pagos.');
            }

            // Si MP responde con error (ej: el código ya se usó o expiró)
            Log::error('Error al intercambiar código OAuth de MP', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return redirect()->route('photographer.profile.edit')
                ->with('error', 'Hubo un problema al validar tu cuenta con Mercado Pago. Por favor, intenta de nuevo.');

        } catch (\Exception $e) {
            Log::error('Excepción crítica en callback de MP', ['error' => $e->getMessage()]);
            return redirect()->route('photographer.profile.edit')
                ->with('error', 'Ocurrió un error inesperado al conectar con Mercado Pago.');
        }
    }

    /**
     * Paso 3: Desvincular la cuenta
     */
    public function unlinkAccount()
    {
        $photographer = auth()->user()->photographer;
        
        $photographer->update([
            'mp_access_token' => null,
            'mp_refresh_token' => null,
            'mp_public_key' => null,
            'mp_user_id' => null,
        ]);

        Log::info('Fotógrafo desvinculó su cuenta de MP', ['photographer_id' => $photographer->id]);

        return redirect()->route('photographer.profile.edit')
            ->with('success', 'Tu cuenta de Mercado Pago ha sido desvinculada. Recuerda que no podrás recibir pagos hasta volver a conectarla.');
    }
}