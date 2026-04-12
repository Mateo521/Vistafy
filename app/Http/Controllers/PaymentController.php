<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\User;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\MercadoPagoService;
use App\Services\CartService;
use App\Notifications\PurchaseCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    protected $mpService;
    protected $cartService;

    public function __construct(MercadoPagoService $mpService, CartService $cartService)
    {
        $this->mpService = $mpService;
        $this->cartService = $cartService;
    }

    /**
     * Iniciar compra desde el carrito (usuarios autenticados)
     */
    public function initiateCartPurchase(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para usar el carrito'
            ], 401);
        }

        $request->validate([
            'photo_ids' => 'required|array|min:1',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        try {
            $user = Auth::user();
            $photos = Photo::with('photographer')->whereIn('id', $request->photo_ids)->get();

            if ($photos->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No se encontraron fotos válidas'], 400);
            }

            DB::beginTransaction();
            
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'buyer_email' => $user->email,
                'buyer_name' => $user->name,
                'total_amount' => $photos->sum('price'),
                'currency' => 'ARS',
                'status' => 'pending',
                'order_token' => Str::random(64),
            ]);
            
            foreach ($photos as $photo) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'photo_id' => $photo->id,
                    'unit_price' => $photo->price,
                ]);
            }
            
            // Llamamos al servicio (ahora usa Marketplace)
            $preferenceResult = $this->mpService->createCartPreference($photos, $user->email, $purchase);

            if (!$preferenceResult['success']) {
                throw new \Exception('Error al crear preferencia de Mercado Pago');
            }

            $this->cartService->clear();
            DB::commit();

            return response()->json([
                'success' => true,
                'purchase_id' => $purchase->id,
                'init_point' => $preferenceResult['init_point'],
                'sandbox_init_point' => $preferenceResult['sandbox_init_point'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en compra desde carrito', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al procesar la compra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Compra individual (para invitados o usuarios autenticados)
     */
    public function initiatePurchase(Request $request, Photo $photo)
    {
        $validated = $request->validate([
            'email' => 'nullable|required_without:user_id|email',
            'create_account' => 'boolean',
        ]);

        $user = Auth::user();
        $guestEmail = $validated['email'] ?? null;

        if (!$user && $guestEmail) {
            if (User::where('email', $guestEmail)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este correo ya está registrado. Por favor inicia sesión para continuar.',
                    'email_exists' => true,
                    'login_url' => route('login'),
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $purchase = Purchase::create([
                'user_id' => $user ? $user->id : null,
                'buyer_email' => $user ? $user->email : $guestEmail,
                'buyer_name' => $user ? $user->name : null,
                'guest_email' => $guestEmail,
                'total_amount' => $photo->price,
                'currency' => 'ARS',
                'status' => 'pending',
                'order_token' => Str::random(64),
            ]);

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'photo_id' => $photo->id,
                'unit_price' => $photo->price,
            ]);

            if (!$user && ($validated['create_account'] ?? false)) {
                session([
                    'pending_account' => [
                        'email' => $guestEmail,
                        'purchase_id' => $purchase->id,
                    ]
                ]);
            }

            // Cargamos la relación del fotógrafo necesaria para el Marketplace
            $photo->load('photographer');
            $result = $this->mpService->createPhotoPreference($photo, $guestEmail ?: $user->email);

            if (!$result['success']) {
                throw new \Exception('Error al crear preferencia de pago');
            }

            DB::commit();

            return response()->json($result);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(' Error en compra individual', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear cuenta automática después del pago
     */
    public function handleAccountCreation($purchase)
    {
        $pendingAccount = session('pending_account');

        if ($pendingAccount && $pendingAccount['purchase_id'] == $purchase->id) {
            $email = $pendingAccount['email'];
            
            if (!User::where('email', $email)->exists()) {
                $temporaryPassword = Str::random(12);

                $user = User::create([
                    'name' => explode('@', $email)[0],
                    'email' => $email,
                    'password' => Hash::make($temporaryPassword),
                    'role' => 'client', // Asegurate de que el rol coincida con tu sistema
                ]);

                $purchase->update([
                    'user_id' => $user->id,
                    'buyer_name' => $user->name,
                ]);

                try {
                    \Mail::send('emails.account-created', [
                        'email' => $email,
                        'password' => $temporaryPassword,
                    ], function ($message) use ($email) {
                        $message->to($email)->subject('Tu cuenta ha sido creada - Vistafy');
                    });
                } catch (\Exception $e) {
                    Log::error(' Error enviando email de cuenta creada', ['error' => $e->getMessage()]);
                }
            }
            session()->forget('pending_account');
        }
    }

    // ... (Mantén tus métodos success, failure, pending y webhook exactamente igual) ...

    /**
     * Descargar fotos compradas (Originales desde Backblaze B2)
     */
    public function download($token)
    {
        $purchase = Purchase::with('items.photo')->where('order_token', $token)->firstOrFail();

        if ($purchase->status !== 'approved') {
            abort(403, 'El pago de esta orden aún no está aprobado.');
        }

        $items = $purchase->items;
        if ($items->isEmpty()) {
            abort(404, 'No hay fotos en esta orden.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('b2');

        // 3. CASO A: Si es una sola foto, redirigimos al link seguro de la nube
        if ($items->count() === 1) {
            $photo = $items->first()->photo;
            $filePath = $photo->original_path;

            if (!$disk->exists($filePath)) {
                abort(404, 'El archivo original no se encuentra disponible en la nube.');
            }

            // Generamos un link que dura 60 minutos para que el cliente lo descargue
            $url = $disk->temporaryUrl($filePath, now()->addMinutes(60));
            
            // Redirigimos al navegador a descargar el archivo
            return redirect()->away($url);
        }

        // 3. CASO B: Varias fotos (ZIP). Tenemos que traerlas de B2 al servidor, comprimirlas y enviarlas.
        $zipFileName = 'vistafy_orden_' . $purchase->id . '.zip';
        $tempDir = storage_path('app/public/temp');
        
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/' . $zipFileName;
        $zip = new \ZipArchive();
        
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($items as $item) {
                $photo = $item->photo;
                $cloudPath = $photo->original_path;
                
                if ($disk->exists($cloudPath)) {
                    // Descargamos el archivo temporalmente de B2 a tu servidor
                    $fileContent = $disk->get($cloudPath);
                    $fileName = $photo->original_name ?? ('foto_' . $photo->unique_id . '.jpg');
                    
                    // Lo agregamos al ZIP en memoria
                    $zip->addFromString($fileName, $fileContent);
                }
            }
            $zip->close();
        } else {
            abort(500, 'No se pudo crear el archivo ZIP con tus fotos.');
        }

        // Enviamos el ZIP al cliente y lo borramos del servidor local para no gastar espacio
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}