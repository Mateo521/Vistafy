<?php

namespace App\Http\Controllers;

use App\Models\Photographer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhotographerController extends Controller
{
    /**
     * Mostrar listado público de fotógrafos
     */
    public function index(Request $request)
    {
        $query = Photographer::with(['user'])
            ->where('is_verified', true)
            ->where('is_active', true)
            ->withCount(['events', 'photos']);

        // Búsqueda por nombre
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('business_name', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($uq) use ($request) {
                        $uq->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Filtro por región
        if ($request->has('region') && $request->region) {
            $query->where('region', $request->region);
        }

        // Ordenamiento
        $sortBy = $request->get('sort', 'recent');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('business_name', 'asc');
                break;
            case 'popular':
                $query->orderBy('photos_count', 'desc');
                break;
            case 'events':
                $query->orderBy('events_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $photographers = $query->paginate(12)->withQueryString();

        // Agregar URLs completas para las fotos
        $photographers->getCollection()->transform(function ($photographer) {
            $photographer->profile_photo_url = $photographer->profile_photo_url;
            $photographer->cover_photo_url = $photographer->cover_photo_url;
            return $photographer;
        });

        // Regiones únicas para filtro
        $regions = Photographer::where('is_verified', true)
            ->where('is_active', true)
            ->whereNotNull('region')
            ->distinct()
            ->pluck('region')
            ->sort()
            ->values();

        return Inertia::render('Photographers/Index', [
            'photographers' => $photographers,
            'regions' => $regions,
            'filters' => [
                'search' => $request->search,
                'region' => $request->region,
                'sort' => $sortBy,
            ]
        ]);
    }

    /**
     * Mostrar perfil público de un fotógrafo
     */
    public function show($slug)
    {
        $photographer = Photographer::with(['user'])
            ->where('slug', $slug)
            ->where('is_verified', true)
            ->where('is_active', true)
            ->withCount(['events', 'photos'])
            ->firstOrFail();

        // Agregar URLs de fotos
        $photographer->profile_photo_url = $photographer->profile_photo_url;
        $photographer->cover_photo_url = $photographer->cover_photo_url;

        // Eventos del fotógrafo
        $events = $photographer->events()
            ->where('is_active', true)
            ->latest('event_date')
            ->withCount('photos')
            ->take(6)
            ->get();

        // Fotos destacadas
        $featuredPhotos = $photographer->photos()
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(12)
            ->get();

        return Inertia::render('Photographers/Show', [
            'photographer' => $photographer,
            'events' => $events,
            'featuredPhotos' => $featuredPhotos,
        ]);
    }
}
