<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoOAuthController extends Controller
{
    
    public function redirectToProvider()
    {
        $clientId = config('services.mercadopago.client_id');
        
        if (!$clientId) {
            return redirect()->back()->with('error', 'Error de configuración: Faltan credenciales de la plataforma.');
        }

        
        $redirectUri = route('photographer.mercadopago.callback');
        
        
        $state = csrf_token();

        
        $url = "https://auth.mercadopago.com/authorization?client_id={$clientId}&response_type=code&platform_id=mp&state={$state}&redirect_uri={$redirectUri}";

        return redirect($url);
    }


    public function handleProviderCallback(Request $request)
    {
        
        if ($request->has('error') || !$request->has('code')) {
            Log::warning('Vinculación MP cancelada o fallida', $request->all());
            return redirect()->route('photographer.profile.edit')
                ->with('error', 'Se canceló la vinculación con Mercado Pago.');
        }

        $code = $request->get('code');
        $redirectUri = route('photographer.mercadopago.callback');

        try {
            
            $response = Http::post('https://api.mercadopago.com/oauth/token', [
                'client_id' => config('services.mercadopago.client_id'),
                'client_secret' => config('services.mercadopago.client_secret'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                
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

            
            Log::error('Error al intercambiar código OAuth de MP', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return redirect()->route('photographer.profile.edit')
                ->with('error', 'Hubo un problema al validar tu cuenta con Mercado Pago. Por favor, intentá de nuevo.');

        } catch (\Exception $e) {
            Log::error('Excepción crítica en callback de MP', ['error' => $e->getMessage()]);
            return redirect()->route('photographer.profile.edit')
                ->with('error', 'Ocurrió un error inesperado al conectar con Mercado Pago.');
        }
    }

    
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