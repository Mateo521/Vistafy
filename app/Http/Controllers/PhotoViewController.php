<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PhotoViewController extends Controller
{
    public function show($photographer, $year, $month, $day, $type, $filename)
    {
        // Construir la ruta del archivo
        $path = "photos/{$photographer}/{$year}/{$month}/{$day}/{$type}/{$filename}";

        // Buscar la foto en la base de datos
        $photo = Photo::where('photographer_id', $photographer)
            ->where('is_active', true)
            ->where(function ($query) use ($type, $filename) {
                if ($type === 'watermarked') {
                    $query->where('watermarked_path', 'like', "%/{$filename}");
                } elseif ($type === 'thumbnails') {
                    $query->where('thumbnail_path', 'like', "%/{$filename}");
                }
            })
            ->with(['event', 'photographer.user'])
            ->first();

        // Si no existe en BD
        if (!$photo) {
            abort(404, 'Foto no encontrada');
        }

        //  DETECCIÓN MEJORADA
        $acceptHeader = request()->header('Accept', '');
        $referer = request()->header('Referer', '');

        // Es petición de imagen si:
        // 1. Accept NO contiene "text/html" (navegadores siempre lo piden)
        // 2. O tiene un Referer (viene desde otra página, es un <img>)
        $isImageRequest = !str_contains($acceptHeader, 'text/html') || !empty($referer);

        //  Si es petición de <img>, servir archivo
        if ($isImageRequest) {
            if (Storage::disk('public')->exists($path)) {
                return response()->file(storage_path('app/public/' . $path), [
                    'Content-Type' => Storage::disk('public')->mimeType($path),
                    'Cache-Control' => 'public, max-age=31536000',
                ]);
            }
            abort(404, 'Imagen no encontrada');
        }

        //  Si es navegador directo, mostrar página Vue
        return Inertia::render('Photos/View', [
            'photo' => [
                'id' => $photo->id,
                'unique_id' => $photo->unique_id,
                'title' => $photo->title ?? $photo->original_name,
                'description' => $photo->description,
                'watermarked_url' => $photo->watermarked_url, // URL de storage directa
                'thumbnail_url' => $photo->thumbnail_url,
                'width' => $photo->width,
                'height' => $photo->height,
                'price' => $photo->price ?? 2500,
                'downloads' => $photo->downloads,
                'event' => $photo->event ? [
                    'id' => $photo->event->id,
                    'name' => $photo->event->name,
                    'slug' => $photo->event->slug,
                ] : null,
                'photographer' => [
                    'id' => $photo->photographer->id,
                    'business_name' => $photo->photographer->business_name,
                    'slug' => $photo->photographer->slug ?? $photo->photographer->id,
                    'profile_photo_url' => $photo->photographer->profile_photo_url ?? null,
                ],
            ],
        ]);
    }
}
