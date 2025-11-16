<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Event;
use Inertia\Inertia;

class PublicGalleryController extends Controller
{
    /**
     * Página principal - todas las fotos disponibles
     */
    public function index()
    {
        $photos = Photo::with('photographer.user')
            ->where('is_active', true)
            ->latest()
            ->paginate(24);

        return Inertia::render('Gallery/Index', [
            'photos' => $photos,
        ]);
    }

    /**
     * Detalle de una foto
     */
    public function show($uniqueId)
    {
        $photo = Photo::with(['photographer.user', 'events'])
            ->where('unique_id', $uniqueId)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Gallery/Show', [
            'photo' => $photo,
        ]);
    }

    /**
     * Buscar fotos
     */
    public function search()
    {
        $query = request('q');
        
        $photos = Photo::with('photographer.user')
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('unique_id', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate(24);

        return Inertia::render('Gallery/Search', [
            'photos' => $photos,
            'query' => $query,
        ]);
    }
}
