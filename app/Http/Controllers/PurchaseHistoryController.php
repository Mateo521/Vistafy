<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class PurchaseHistoryController extends Controller
{
    /**
     *  Mostrar historial de compras
     */
    public function index()
    {
        $purchases = Auth::user()->purchases()
            ->with(['items.photo.event', 'items.photo.photographer'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'order_token' => $purchase->order_token,
                    'total_amount' => (float) $purchase->total_amount, //  Convertir a float
                    'currency' => $purchase->currency,
                    'status' => $purchase->status,
                    'created_at' => $purchase->created_at->format('d/m/Y H:i'),
                    'created_at_human' => $purchase->created_at->diffForHumans(),
                    'item_count' => $purchase->items->count(), //  Contar items correctamente
                    'items' => $purchase->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'photo_id' => $item->photo_id,
                            'unit_price' => (float) $item->unit_price, //  Convertir a float
                            'download_count' => (int) ($item->download_count ?? 0), //  Convertir a int
                            'photo' => [
                                'id' => $item->photo->id,
                                'unique_id' => $item->photo->unique_id,
                                'title' => $item->photo->title,
                                'thumbnail_url' => $item->photo->thumbnail_url,
                                'width' => $item->photo->width,
                                'height' => $item->photo->height,
                                'event' => $item->photo->event ? [
                                    'name' => $item->photo->event->name,
                                ] : null,
                                'photographer' => $item->photo->photographer ? [
                                    'name' => $item->photo->photographer->name,
                                ] : null,
                            ],
                        ];
                    }),
                ];
            });

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
        ]);
    }

    /**
     *  Descargar foto original
     */
    public function download($purchaseId, $photoId)
    {
        // Verificar que la compra pertenece al usuario
        $purchase = Auth::user()->purchases()
            ->where('id', $purchaseId)
            ->where('status', 'approved')
            ->firstOrFail();

        // Verificar que la foto está en la compra
        $item = $purchase->items()->where('photo_id', $photoId)->firstOrFail();
        $photo = $item->photo;

        // Incrementar contador de descargas
        $item->increment('download_count');

        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($photo->original_path)) {
            abort(404, 'Archivo no encontrado');
        }

        $filePath = storage_path('app/public/' . $photo->original_path);
        $fileName = ($photo->title ?: 'foto-' . $photo->unique_id) . '.jpg';

        return Response::download($filePath, $fileName, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     *  Descargar todas las fotos de una compra como ZIP
     */
    public function downloadAll($purchaseId)
    {
        $purchase = Auth::user()->purchases()
            ->where('id', $purchaseId)
            ->where('status', 'approved')
            ->with('items.photo')
            ->firstOrFail();

        $zip = new \ZipArchive();
        $zipFileName = 'compra-' . $purchase->id . '-' . time() . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Crear directorio temporal si no existe
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($purchase->items as $item) {
                $photo = $item->photo;
                $filePath = storage_path('app/public/' . $photo->original_path);

                if (file_exists($filePath)) {
                    $fileName = ($photo->title ?: 'foto-' . $photo->unique_id) . '.jpg';
                    $zip->addFile($filePath, $fileName);
                    
                    // Incrementar contador
                    $item->increment('download_count');
                }
            }

            $zip->close();

            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        abort(500, 'Error al crear archivo ZIP');
    }
}
