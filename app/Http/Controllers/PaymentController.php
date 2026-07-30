<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\User;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
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

   
  public function initiateCartPurchase(Request $request)
    {
        $request->validate([
            'photo_ids' => 'required|array|min:1',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $user = Auth::user();

        try {
            $photoIds = collect($request->photo_ids)->unique()->values();
            $photos = Photo::with('photographer', 'event')->whereIn('id', $photoIds)->get();

            if ($photos->isEmpty() || $photos->count() !== $photoIds->count()) {
                return response()->json(['success' => false, 'message' => 'No se encontraron fotos válidas'], 400);
            }

            $photosByPhotographer = $photos->groupBy('photographer_id');

            $unavailablePhotographers = collect();

            foreach ($photosByPhotographer as $groupPhotos) {
                $photographer = $groupPhotos->first()->photographer;
                if (!$photographer || !$photographer->has_mercadopago_account) {
                    $unavailablePhotographers->push([
                        'id' => $photographer->id ?? null,
                        'name' => $photographer->business_name ?? 'Fotógrafo',
                        'photo_ids' => $groupPhotos->pluck('id')->values(),
                        'photo_unique_ids' => $groupPhotos->pluck('unique_id')->values(),
                    ]);
                }
            }

            if ($unavailablePhotographers->isNotEmpty()) {
                $names = $unavailablePhotographers->pluck('name')->filter()->join(', ');

                return response()->json([
                    'success' => false,
                    'message' => $names
                        ? "No se puede completar la compra porque {$names} no tiene Mercado Pago habilitado para recibir pagos."
                        : 'Uno de los fotógrafos no puede recibir pagos en este momento.',
                    'unavailable_photographers' => $unavailablePhotographers->values(),
                ], 422);
            }

            DB::beginTransaction();
            
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'buyer_email' => $user->email,
                'buyer_name' => $user->name,
                'guest_email' => null, // Ya no hay invitados
                'total_amount' => $photos->sum('price'),
                'currency' => 'ARS',
                'status' => 'pending',
                'order_token' => Str::random(64),
            ]);

            $isMultiPhotographer = $photosByPhotographer->count() > 1;
            $paymentResults = [];

            foreach ($photosByPhotographer as $photographerId => $groupPhotos) {
                $groupPhotos = $groupPhotos->values();
                $groupTotal = (float) $groupPhotos->sum('price');

                $payment = PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'photographer_id' => $photographerId,
                    'amount' => $groupTotal,
                    'platform_fee' => $this->mpService->calculatePlatformFee($groupTotal),
                    'currency' => 'ARS',
                    'status' => 'pending',
                ]);

                foreach ($groupPhotos as $photo) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'purchase_payment_id' => $payment->id,
                        'photo_id' => $photo->id,
                        'unit_price' => $photo->price,
                    ]);
                }

                $preferenceResult = $this->mpService->createPaymentPreference(
                    $groupPhotos,
                    $user->email,
                    $purchase,
                    $payment,
                    $isMultiPhotographer
                );

                if (!$preferenceResult['success']) {
                    throw new \Exception('Error al crear preferencia de Mercado Pago');
                }

                $payment->refresh();
                $paymentResults[] = [
                    'id' => $payment->id,
                    'photographer_id' => $payment->photographer_id,
                    'photographer_name' => $payment->photographer->business_name ?? 'Fotógrafo',
                    'amount' => (float) $payment->amount,
                    'status' => $payment->status,
                    'init_point' => $payment->init_point,
                    'sandbox_init_point' => $payment->sandbox_init_point,
                ];
            }

            DB::commit();
            if ($isMultiPhotographer) {
                return response()->json([
                    'success' => true,
                    'requires_multiple_payments' => true,
                    'purchase_id' => $purchase->id,
                    'redirect_url' => route('payment.split.show', ['purchase' => $purchase->id]),
                    'payments' => $paymentResults,
                ]);
            }

            return response()->json($preferenceResult);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en compra desde carrito', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null,
                'photo_ids' => isset($photoIds) ? $photoIds->values()->all() : null,
            ]);
            return response()->json(['success' => false, 'message' => 'Error al procesar la compra.'], 500);
        }
    }

    
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

          
            $photo->load('photographer');
            $result = $this->mpService->createPhotoPreference($photo, $guestEmail ?: $user->email, $purchase);

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

  

     public function success(Request $request)
    {
        $purchaseId = $request->query('purchase_id');

        if (!$purchaseId) {
            return redirect()->route('home')
                ->with('error', 'ID de compra inválido');
        }

        $purchase = Purchase::with('items.photo.event', 'items.photo.photographer')
            ->findOrFail($purchaseId);

        return Inertia::render('Payment/Success', [
            'purchase' => $purchase,
        ]);
    }
 
    public function failure(Request $request)
    {
        $purchaseId = $request->query('purchase_id');
        $purchase = $purchaseId
            ? Purchase::with('items.photo.event', 'items.photo.photographer', 'payments.photographer')->find($purchaseId)
            : null;

        return Inertia::render('Payment/Failure', [
            'purchase' => $purchase,
            'message' => 'El pago fue rechazado o cancelado',
        ]);
    }
 
    public function pending(Request $request)
    {
        $purchaseId = $request->query('purchase_id');
        $purchase = Purchase::with('items.photo.event', 'items.photo.photographer', 'payments.photographer')
            ->findOrFail($purchaseId);

        return Inertia::render('Payment/Pending', [
            'purchase' => $purchase,
            'message' => 'Tu pago está siendo procesado',
        ]);
    }

    public function splitCheckout(Purchase $purchase)
    {
        abort_unless($purchase->user_id === Auth::id(), 403);

        $purchase->load('items.photo.event', 'items.photo.photographer', 'payments.photographer');

        return Inertia::render('Payment/SplitCheckout', [
            'purchase' => $purchase,
        ]);
    }

    public function splitStatus(Purchase $purchase)
    {
        abort_unless($purchase->user_id === Auth::id(), 403);

        $purchase->load('payments.photographer');

        return response()->json([
            'purchase' => [
                'id' => $purchase->id,
                'status' => $purchase->status,
                'total_amount' => (float) $purchase->total_amount,
            ],
            'payments' => $purchase->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'photographer_id' => $payment->photographer_id,
                    'photographer_name' => $payment->photographer->business_name ?? 'Fotógrafo',
                    'amount' => (float) $payment->amount,
                    'status' => $payment->status,
                    'init_point' => $payment->init_point,
                    'sandbox_init_point' => $payment->sandbox_init_point,
                ];
            })->values(),
        ]);
    }

    public function retrySplitPayment(Purchase $purchase, PurchasePayment $payment)
    {
        abort_unless($purchase->user_id === Auth::id(), 403);
        abort_unless($payment->purchase_id === $purchase->id, 404);
        abort_if($payment->status === 'approved', 422, 'Este pago ya fue aprobado.');

        $payment->load('items.photo.photographer', 'items.photo.event', 'photographer');
        $photos = $payment->items->pluck('photo')->filter()->values();

        if ($photos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay fotos asociadas a este pago.',
            ], 422);
        }

        $payment->update(['status' => 'pending']);

        $result = $this->mpService->createPaymentPreference(
            $photos,
            $purchase->buyer_email,
            $purchase,
            $payment,
            true
        );

        $payment->refresh();

        return response()->json([
            'success' => true,
            'payment' => [
                'id' => $payment->id,
                'photographer_id' => $payment->photographer_id,
                'photographer_name' => $payment->photographer->business_name ?? 'Fotógrafo',
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
                'init_point' => $payment->init_point,
                'sandbox_init_point' => $payment->sandbox_init_point,
            ],
            'init_point' => $result['init_point'] ?? $payment->init_point,
            'sandbox_init_point' => $result['sandbox_init_point'] ?? $payment->sandbox_init_point,
        ]);
    }


    

   
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

        if ($items->count() === 1) {
            $photo = $items->first()->photo;
            $filePath = $photo->original_path;

            if (!$disk->exists($filePath)) {
                abort(404, 'El archivo original no se encuentra disponible en la nube.');
            }

            $fileName = $photo->original_name ?? ('foto_' . $photo->unique_id . '.jpg');

            
            $url = $disk->temporaryUrl($filePath, now()->addMinutes(60), [
                'ResponseContentDisposition' => 'attachment; filename="' . $fileName . '"'
            ]);
            
            return redirect()->away($url);
        }

        $zipFileName = 'F33_orden_' . $purchase->id . '.zip';
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
                   
                    $fileContent = $disk->get($cloudPath);
                    $fileName = $photo->original_name ?? ('foto_' . $photo->unique_id . '.jpg');
                    
                  
                    $zip->addFromString($fileName, $fileContent);
                }
            }
            $zip->close();
        } else {
            abort(500, 'No se pudo crear el archivo ZIP con tus fotos.');
        }

     
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}