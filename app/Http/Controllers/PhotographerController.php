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
    public function show(Request $request, $slug)
    {
        // Buscar fotógrafo por slug
        $photographer = Photographer::where('slug', $slug)
            ->with(['user'])
            ->firstOrFail();

        // Obtener eventos del fotógrafo
        $eventsQuery = $photographer->events()
            ->where('is_active', true)
            ->where('is_private', false)
            ->withCount('photos');

        // Filtro por búsqueda de eventos
        if ($request->filled('event_search')) {
            $eventsQuery->where('name', 'like', '%' . $request->event_search . '%');
        }

        $events = $eventsQuery->latest('event_date')->paginate(12)->withQueryString();

        // Obtener TODAS las fotos del fotógrafo (de todos sus eventos públicos)
        $photosQuery = $photographer->photos()
            ->where('is_active', true)
            ->whereHas('event', function ($query) {
                $query->where('is_active', true)
                    ->where('is_private', false);
            })
            ->with('event:id,name,slug');

        // Filtro por evento específico
        if ($request->filled('event_id')) {
            $photosQuery->where('event_id', $request->event_id);
        }

        // Tab activa (eventos o fotos)
        $activeTab = $request->get('tab', 'events');

        $photos = $photosQuery->latest()->paginate(24)->withQueryString();

        // Estadísticas
        $stats = [
            'total_events' => $photographer->events()->where('is_active', true)->count(),
            'total_photos' => $photographer->photos()->where('is_active', true)->count(),
            'public_events' => $photographer->events()->where('is_active', true)->where('is_private', false)->count(),
        ];

        return Inertia::render('Photographers/Show', [
            'photographer' => [
                'id' => $photographer->id,
                'slug' => $photographer->slug,
                'business_name' => $photographer->business_name,
                'bio' => $photographer->bio,
                'region' => $photographer->region,
                'phone' => $photographer->phone,
                'website' => $photographer->website,
                'instagram' => $photographer->instagram,
                'facebook' => $photographer->facebook,
                'profile_photo_url' => $photographer->profile_photo_url,
                'cover_photo_url' => $photographer->cover_photo_url,
                'user' => [
                    'name' => $photographer->user->name,
                    'email' => $photographer->user->email,
                ],
            ],
            'events' => $events,
            'photos' => $photos,
            'stats' => $stats,
            'activeTab' => $activeTab,
            'filters' => [
                'event_search' => $request->event_search,
                'event_id' => $request->event_id,
            ],
        ]);
    }
}
