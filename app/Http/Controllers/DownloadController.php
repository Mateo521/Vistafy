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
        Log::info(' Descarga solicitada', ['token' => substr($token, 0, 10) . '...']);

        // 1. Buscar la compra
        $purchase = Purchase::where('order_token', $token)
            ->with('items.photo')
            ->first();

        if (!$purchase) {
            abort(404, 'Enlace expirado o inválido.');
        }

        // 2. Verificar estado del pago
        if ($purchase->status !== 'approved') {
            // Si intentan descargar algo no pagado, redirigir al checkout o estado
            return to_route('payment.pending', ['purchase_id' => $purchase->id]);
        }

        // 3. Obtener la foto
        $item = $purchase->items->first();

        if (!$item || !$item->photo) {
            abort(404, 'Foto no encontrada en la orden.');
        }

        $photo = $item->photo;

        // CORRECCIÓN 1: Usar la columna correcta 'original_path'
        $path = $photo->original_path;

        // CORRECCIÓN 2: Manejar imágenes de prueba (Picsum/Externas)
        if (str_starts_with($path, 'http')) {
            Log::info(' Redirigiendo a imagen externa', ['url' => $path]);
            return redirect()->away($path);
        }

        // CORRECCIÓN 3: Manejar imágenes locales reales
        // Asumiendo que usaste 'public' disk al subir
        if (Storage::disk('public')->exists($path)) {
            Log::info(' Iniciando descarga local', ['path' => $path]);

            // Incrementar contador si quieres
            // $purchase->increment('download_count');

            return Storage::disk('public')->download(
                $path,
                "vistafy_{$photo->unique_id}.jpg"
            );
        }

        Log::error(' Archivo físico no encontrado en disco', ['path' => $path]);
        abort(404, 'El archivo no existe en el servidor.');
    }

    /**
     * Página de descarga con interfaz
     */
    public function show(string $token)
    {
        Log::info(' Página de descarga solicitada', ['token' => substr($token, 0, 20) . '...']);

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
