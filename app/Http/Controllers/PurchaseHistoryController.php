<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function download($purchaseId, $photoId)
    {
        $purchase = Auth::user()->purchases()
            ->where('id', $purchaseId)
            ->where('status', 'approved')
            ->first();

        if (! $purchase) {
            dd('ERROR 1: La compra ID '.$purchaseId.' no le pertenece al usuario logueado ('.Auth::user()->email.') o no está aprobada.');
        }

        $item = $purchase->items()->where('photo_id', $photoId)->first();

        if (! $item) {
            dd('ERROR 2: La compra es tuya, pero la foto ID '.$photoId.' no está registrada dentro de esta orden.');
        }

        $photo = $item->photo;

        if (! Storage::disk('b2')->exists($photo->original_path)) {
            dd('ERROR 3: Todo en la base de datos está perfecto, pero el archivo de la foto original NO existe en el bucket de Backblaze B2. Ruta buscada: '.$photo->original_path);
        }

        $photo->increment('downloads');
        $item->increment('download_count');

        $fileName = ($photo->title ?: 'foto-'.$photo->unique_id).'.jpg';

        return response()->streamDownload(function () use ($photo) {
            echo Storage::disk('b2')->get($photo->original_path);
        }, $fileName, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
