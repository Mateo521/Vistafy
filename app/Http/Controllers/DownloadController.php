<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DownloadController extends Controller
{
    public function download(string $token)
    {
        Log::info(' Descarga solicitada', ['token' => substr($token, 0, 10).'...']);

        $purchase = Purchase::where('order_token', $token)
            ->with('items.photo')
            ->first();

        if (! $purchase) {
            abort(404, 'Enlace expirado o inválido.');
        }

        if ($purchase->status !== 'approved') {

            return to_route('payment.pending', ['purchase_id' => $purchase->id]);
        }

        $item = $purchase->items->first();

        if (! $item || ! $item->photo) {
            abort(404, 'Foto no encontrada en la orden.');
        }

        $photo = $item->photo;

        $path = $photo->original_path;

        if (str_starts_with($path, 'http')) {
            Log::info(' Redirigiendo a imagen externa', ['url' => $path]);

            return redirect()->away($path);
        }

        if (Storage::disk('public')->exists($path)) {
            Log::info(' Iniciando descarga local', ['path' => $path]);

            $purchase->increment('download_count');

            return Storage::disk('public')->download(
                $path,
                "vistafy_{$photo->unique_id}.jpg"
            );
        }

        Log::error(' Archivo físico no encontrado en disco', ['path' => $path]);
        abort(404, 'El archivo no existe en el servidor.');
    }

    public function show(string $token)
    {
        Log::info(' Página de descarga solicitada', ['token' => substr($token, 0, 20).'...']);

        $purchase = Purchase::where('order_token', $token)
            ->with('items.photo.event')
            ->first();

        if (! $purchase) {
            abort(404, 'Token de descarga inválido');
        }

        if (! in_array($purchase->status, ['completed', 'approved'])) {
            return Inertia::render('Download/Pending', [
                'purchase' => $purchase,
            ]);
        }

        return Inertia::render('Download/Show', [
            'purchase' => $purchase,
        ]);
    }
}
