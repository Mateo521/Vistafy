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
        Log::info('📥 Descarga directa solicitada', [
            'token' => substr($token, 0, 20) . '...',
        ]);

        $purchase = Purchase::where('download_token', $token)
            ->with('photo')
            ->first();

        if (!$purchase) {
            Log::warning(' Token inválido');
            abort(404, 'Token de descarga inválido');
        }

        //  CAMBIO: Verificar 'approved' en lugar de 'completed'
        if ($purchase->status !== 'approved') {
            Log::warning(' Pago no aprobado', [
                'purchase_id' => $purchase->id,
                'status' => $purchase->status,
            ]);

            return Inertia::render('Download/Pending', [
                'purchase' => $purchase,
            ]);
        }

        $photo = $purchase->photo;

        if (!$photo || !Storage::disk('public')->exists($photo->path)) {
            Log::error(' Archivo no encontrado', [
                'photo_path' => $photo ? $photo->path : 'N/A',
            ]);
            abort(404, 'Archivo no encontrado');
        }

        // Incrementar contador
        $purchase->increment('download_count');

        Log::info(' Descarga iniciada', [
            'purchase_id' => $purchase->id,
            'photo_id' => $photo->id,
            'download_count' => $purchase->download_count,
        ]);

        $filePath = Storage::disk('public')->path($photo->path);
        $fileName = 'foto-' . $photo->id . '.' . pathinfo($photo->path, PATHINFO_EXTENSION);

        return response()->download($filePath, $fileName);
    }

    /**
     * Página de descarga con Inertia
     */
    public function show(string $token)
    {
        Log::info(' Página de descarga solicitada', [
            'token' => substr($token, 0, 20) . '...',
        ]);

        $purchase = Purchase::where('download_token', $token)
            ->with('photo')
            ->first();

        if (!$purchase) {
            Log::warning(' Token inválido');
            abort(404, 'Token de descarga inválido');
        }

        Log::info(' Compra encontrada', [
            'purchase_id' => $purchase->id,
            'status' => $purchase->status,
            'has_photo' => $purchase->photo ? 'SÍ' : 'NO',
        ]);

        //  CAMBIO: Verificar 'approved' en lugar de 'completed'
        if ($purchase->status !== 'approved') {
            return Inertia::render('Download/Pending', [
                'purchase' => $purchase,
            ]);
        }

        return Inertia::render('Download/Show', [
            'purchase' => $purchase,
            'photo' => $purchase->photo,
        ]);
    }
}
