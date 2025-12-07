<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DownloadController extends Controller
{
    /**
     * Descarga directa del archivo
     */
    public function download(string $token)
    {
        Log::info('📥 Descarga solicitada', ['token' => substr($token, 0, 20) . '...']);

        // 🔥 Buscar por order_token (no download_token)
        $purchase = Purchase::where('order_token', $token)
            ->with('items.photo')
            ->first();

        if (!$purchase) {
            Log::warning('⚠️ Token inválido');
            abort(404, 'Token de descarga inválido o expirado');
        }

        // Verificar estado (completed o approved según tu lógica)
        if (!in_array($purchase->status, ['completed', 'approved'])) {
            Log::warning('⚠️ Pago no completado', [
                'purchase_id' => $purchase->id,
                'status' => $purchase->status,
            ]);

            return Inertia::render('Download/Pending', [
                'purchase' => $purchase,
            ]);
        }

        // 🔥 Obtener la primera foto (o iterar si hay múltiples)
        $item = $purchase->items->first();
        
        if (!$item || !$item->photo) {
            Log::error('❌ No hay fotos en esta compra');
            abort(404, 'No se encontraron fotos en esta compra');
        }

        $photo = $item->photo;

        if (!Storage::disk('public')->exists($photo->path)) {
            Log::error('❌ Archivo no encontrado', ['path' => $photo->path]);
            abort(404, 'Archivo no encontrado');
        }

        Log::info('✅ Descarga iniciada', [
            'purchase_id' => $purchase->id,
            'photo_id' => $photo->id,
        ]);

        $filePath = Storage::disk('public')->path($photo->path);
        $fileName = 'vistafy-foto-' . $photo->unique_id . '.' . pathinfo($photo->path, PATHINFO_EXTENSION);

        return response()->download($filePath, $fileName);
    }

    /**
     * Página de descarga con interfaz
     */
    public function show(string $token)
    {
        Log::info('🖼️ Página de descarga solicitada', ['token' => substr($token, 0, 20) . '...']);

        $purchase = Purchase::where('order_token', $token)
            ->with('items.photo.event')
            ->first();

        if (!$purchase) {
            abort(404, 'Token de descarga inválido');
        }

        if (!in_array($purchase->status, ['completed', 'approved'])) {
            return Inertia::render('Download/Pending', [
                'purchase' => $purchase,
            ]);
        }

        return Inertia::render('Download/Show', [
            'purchase' => $purchase,
        ]);
    }
}
