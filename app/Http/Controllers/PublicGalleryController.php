<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Event;
use App\Models\Photographer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PublicGalleryController extends Controller
{
    /**
     * Página principal con video promocional
     */
    public function index()
    {
        // Fotos recientes activas (últimas 12)
        $recentPhotos = Photo::where('is_active', true)
            ->with('photographer:id,business_name,region')
            ->latest()
            ->take(12)
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'unique_id' => $photo->unique_id,
                    'thumbnail_url' => $photo->thumbnail_url,
                    'price' => $photo->price,
                    'photographer' => $photo->photographer->business_name ?? 'Fotógrafo',
                ];
            });

        // Eventos recientes públicos (últimos 6)
        $recentEvents = Event::where('is_private', false)
            ->withCount('photos')
            ->with('photographer:id,business_name')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'event_date' => $event->event_date,
                    'photos_count' => $event->photos_count,
                    'photographer' => $event->photographer->business_name ?? null,
                ];
            });

        // Estadísticas para mostrar en el home
        $stats = [
            'total_photos' => Photo::where('is_active', true)->count(),
            'total_events' => Event::where('is_private', false)->count(),
            'total_photographers' => Photographer::where('is_active', true)->count(),
        ];

        return Inertia::render('Home', [
            'recentPhotos' => $recentPhotos,
            'recentEvents' => $recentEvents,
            'stats' => $stats,
        ]);
    }

    /**
     * Galería completa con filtros
     */
    public function gallery(Request $request)
    {
        $query = Photo::where('is_active', true)
            ->with(['photographer:id,business_name,region', 'events:id,name,slug']);

        // Filtro por búsqueda (ID o palabra clave)
        if ($request->has('search') && $request->search) {
            $search =$request->search;
            $query->where(function($q) use ($search) {
                $q->where('unique_id', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('photographer', function($q) use ($search) {
                      $q->where('business_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por región
        if ($request->has('region') && $request->region !== 'all') {
            $query->whereHas('photographer', function($q) use ($request) {
                $q->where('region', $request->region);
            });
        }

        // Filtro por evento
        if ($request->has('event') && $request->event) {
            $query->whereHas('events', function($q) use ($request) {
                $q->where('events.id', $request->event);
            });
        }

        // Ordenamiento
        $sortBy =$request->get('sort', 'recent');
        switch ($sortBy) {
            case 'recent':
                $query->latest();
                break;
            case 'popular':
                $query->orderBy('downloads', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
        }

        $photos =$query->paginate(24)->withQueryString();

        // Eventos para el filtro
        $events = Event::where('is_private', false)
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();

        // Regiones disponibles
        $regions = [
            ['value' => 'all', 'label' => 'Todas las Regiones'],
            ['value' => 'norte', 'label' => 'Norte'],
            ['value' => 'centro', 'label' => 'Centro'],
            ['value' => 'sur', 'label' => 'Sur'],
        ];

        return Inertia::render('Gallery/Index', [
            'photos' => $photos,
            'events' => $events,
            'regions' => $regions,
            'filters' => [
                'search' => $request->search,
                'region' => $request->region ?? 'all',
                'event' => $request->event,
                'sort' => $sortBy,
            ],
        ]);
    }

    /**
     * Mostrar foto individual con detalles
     */
    public function show($uniqueId)
    {
        $photo = Photo::where('unique_id', strtoupper($uniqueId))
            ->where('is_active', true)
            ->with([
                'photographer' => function($query) {
                    $query->select('id', 'user_id', 'business_name', 'region', 'bio', 'phone', 'profile_photo');
                },
                'photographer.user:id,email',
                'events' => function($query) {
                    $query->select('events.id', 'name', 'slug', 'event_date');
                }
            ])
            ->firstOrFail();

        // Fotos relacionadas (del mismo fotógrafo o evento)
        $relatedPhotos = Photo::where('is_active', true)
            ->where('id', '!=', $photo->id)
            ->where(function($q) use ($photo) {
                $q->where('photographer_id', $photo->photographer_id)
                  ->orWhereHas('events', function($q) use ($photo) {
                      $q->whereIn('events.id', $photo->events->pluck('id'));
                  });
            })
            ->with('photographer:id,business_name')
            ->take(8)
            ->get();

        return Inertia::render('Gallery/Show', [
            'photo' => [
                'id' => $photo->id,
                'unique_id' => $photo->unique_id,
                'title' => $photo->title,
                'description' => $photo->description,
                'price' => $photo->price,
                'preview_url' => $photo->preview_url,
                'thumbnail_url' => $photo->thumbnail_url,
                'downloads' => $photo->downloads,
                'photographer' => [
                    'id' => $photo->photographer->id,
                    'business_name' => $photo->photographer->business_name,
                    'region' => $photo->photographer->region,
                    'bio' => $photo->photographer->bio,
                    'phone' => $photo->photographer->phone,
                    'email' => $photo->photographer->user->email ?? null,
                    'profile_photo_url' => $photo->photographer->profile_photo_url,
                ],
                'events' => $photo->events,
                'created_at' => $photo->created_at->diffForHumans(),
            ],
            'relatedPhotos' => $relatedPhotos,
        ]);
    }

    /**
     * Búsqueda rápida por ID único
     */
    public function search(Request $request)
    {
        $request->validate([
            'id' => 'required|string|min:3',
        ]);

        $uniqueId = strtoupper(trim($request->id));

        // Buscar foto exacta
        $photo = Photo::where('unique_id', $uniqueId)
            ->where('is_active', true)
            ->first();

        if ($photo) {
            return redirect()->route('gallery.show', $photo->unique_id);
        }

        // Buscar fotos similares
        $similarPhotos = Photo::where('is_active', true)
            ->where('unique_id', 'like', "%{$uniqueId}%")
            ->take(10)
            ->get();

        if ($similarPhotos->count() === 1) {
            return redirect()->route('gallery.show', $similarPhotos->first()->unique_id);
        }

        return redirect()->route('gallery.index', ['search' => $uniqueId])
            ->with('info', "No se encontró la foto exacta '{$uniqueId}'. Mostrando resultados similares.");
    }

    /**
     * Lista de todos los eventos públicos
     */
    public function events()
    {
        $events = Event::where('is_private', false)
            ->withCount('photos')
            ->with('photographer:id,business_name')
            ->latest()
            ->paginate(12);

        // Eventos destacados (con más fotos)
        $featuredEvents = Event::where('is_private', false)
            ->withCount('photos')
            ->orderBy('photos_count', 'desc')
            ->take(3)
            ->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'featuredEvents' => $featuredEvents,
        ]);
    }

    /**
     * Mostrar evento específico con sus fotos
     */
    public function showEvent($slug)
    {
        $event = Event::where('slug', $slug)
            ->where('is_private', false)
            ->with([
                'photographer' => function($query) {
                    $query->select('id', 'business_name', 'region', 'phone', 'profile_photo');
                },
                'photographer.user:id,email'
            ])
            ->withCount('photos')
            ->firstOrFail();

        // Fotos del evento con paginación
        $photos =$event->photos()
            ->where('is_active', true)
            ->latest()
            ->paginate(24);

        return Inertia::render('Events/Show', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description,
                'event_date' => $event->event_date,
                'location' => $event->location,
                'photos_count' => $event->photos_count,
                'photographer' => [
                    'business_name' => $event->photographer->business_name,
                    'region' => $event->photographer->region,
                    'phone' => $event->photographer->phone,
                    'email' => $event->photographer->user->email ?? null,
                    'profile_photo_url' => $event->photographer->profile_photo_url,
                ],
            ],
            'photos' => $photos,
        ]);
    }

    /**
     * Lista de fotógrafos activos
     */
    public function photographers(Request $request)
    {
        $query = Photographer::where('is_active', true)
            ->with('user:id,name,email')
            ->withCount('photos');

        // Filtro por región
        if ($request->has('region') && $request->region !== 'all') {
            $query->where('region', $request->region);
        }

        // Búsqueda por nombre
        if ($request->has('search') && $request->search) {
            $search =$request->search;
            $query->where(function($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $photographers =$query->paginate(12)->withQueryString();

        return Inertia::render('Photographers/Index', [
            'photographers' => $photographers,
            'filters' => [
                'search' => $request->search,
                'region' => $request->region ?? 'all',
            ],
        ]);
    }

    /**
     * Perfil público del fotógrafo
     */
    public function showPhotographer($id)
    {
        $photographer = Photographer::where('id', $id)
            ->where('is_active', true)
            ->with('user:id,name,email')
            ->withCount('photos')
            ->firstOrFail();

        // Fotos del fotógrafo
        $photos = Photo::where('photographer_id', $photographer->id)
            ->where('is_active', true)
            ->latest()
            ->paginate(24);

        // Eventos del fotógrafo
        $events = Event::whereHas('photos', function($q) use ($photographer) {
            $q->where('photographer_id', $photographer->id);
        })
        ->where('is_private', false)
        ->withCount('photos')
        ->take(6)
        ->get();

        // Estadísticas
        $stats = [
            'total_photos' => $photographer->photos()->count(),
            'active_photos' => $photographer->photos()->where('is_active', true)->count(),
            'total_downloads' => $photographer->photos()->sum('downloads'),
            'total_events' => $events->count(),
        ];

        return Inertia::render('Photographers/Show', [
            'photographer' => [
                'id' => $photographer->id,
                'business_name' => $photographer->business_name,
                'region' => $photographer->region,
                'bio' => $photographer->bio,
                'phone' => $photographer->phone,
                'email' => $photographer->user->email ?? null,
                'profile_photo_url' => $photographer->profile_photo_url,
                'cover_photo_url' => $photographer->cover_photo_url,
            ],
            'photos' => $photos,
            'events' => $events,
            'stats' => $stats,
        ]);
    }

    /**
     * Descargar foto (después del pago)
     * Esta función debería estar protegida por el sistema de pagos
     */
    public function download($uniqueId)
    {
        $photo = Photo::where('unique_id', strtoupper($uniqueId))
            ->where('is_active', true)
            ->firstOrFail();

        // TODO: Verificar que el usuario haya pagado por esta foto
        // Esto se implementará con el sistema de pagos

        // Incrementar contador de descargas
        $photo->increment('downloads');

        // Generar URL de descarga temporal
        $filePath = storage_path('app/' .$photo->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download(
            $filePath,
            $photo->unique_id . '.' . pathinfo($photo->file_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Verificar disponibilidad de una foto
     */
    public function checkAvailability($uniqueId)
    {
        $photo = Photo::where('unique_id', strtoupper($uniqueId))
            ->where('is_active', true)
            ->first();

        return response()->json([
            'available' => $photo !== null,
            'photo' => $photo ? [
                'unique_id' => $photo->unique_id,
                'title' => $photo->title,
                'price' => $photo->price,
                'preview_url' => $photo->preview_url,
            ] : null,
        ]);
    }
}
