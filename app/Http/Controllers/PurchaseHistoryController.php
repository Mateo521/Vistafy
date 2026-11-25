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
            ->with(['items.photo.event', 'items.photo.photographer', 'items.payment'])
            ->where(function ($query) {
                $query->where('status', 'approved')
                    ->orWhereHas('payments', fn ($q) => $q->where('status', 'approved'));
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($purchase) {
                $paidItems = $purchase->items->filter(function ($item) use ($purchase) {
                    return $purchase->status === 'approved'
                        || ($item->payment && $item->payment->status === 'approved');
                })->values();

                return [
                    'id' => $purchase->id,
                    'order_token' => $purchase->order_token,
                    'total_amount' => $purchase->status === 'approved'
                        ? (float) $purchase->total_amount
                        : (float) $paidItems->sum('unit_price'),
                    'currency' => $purchase->currency,
                    'status' => $paidItems->isNotEmpty() ? 'approved' : $purchase->status,
                    'created_at' => $purchase->created_at->format('d/m/Y H:i'),
                    'created_at_human' => $purchase->created_at->diffForHumans(),
                    'item_count' => $paidItems->count(),
                    'items' => $paidItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'photo_id' => $item->photo_id,
                            'unit_price' => (float) $item->unit_price,
                            'download_count' => (int) ($item->download_count ?? 0),
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
            })
            ->filter(fn ($purchase) => $purchase['item_count'] > 0)
            ->values();

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
        ]);
    }

    public function download($purchaseId, $photoId)
    {
        $purchase = Auth::user()->purchases()
            ->where('id', $purchaseId)
            ->first();

        if (! $purchase) {
            abort(404, 'La compra no existe o no pertenece a tu cuenta.');
        }

        $item = $purchase->items()
            ->with(['photo', 'payment'])
            ->where('photo_id', $photoId)
            ->first();

        if (! $item) {
            abort(404, 'La fotografía no pertenece a esta orden.');
        }

        $isPaid = $purchase->status === 'approved'
            || ($item->payment && $item->payment->status === 'approved');

        if (! $isPaid) {
            abort(403, 'El pago de esta fotografía aún no está aprobado.');
        }

        $photo = $item->photo;

        if (! Storage::disk('b2_private')->exists($photo->original_path)) {
            abort(404, 'El archivo original no se encuentra disponible.');
        }

        $photo->increment('downloads');
        $item->increment('download_count');

        $fileName = ($photo->title ?: 'foto-'.$photo->unique_id).'.jpg';

        return response()->streamDownload(function () use ($photo) {
            echo Storage::disk('b2_private')->get($photo->original_path);
        }, $fileName, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
