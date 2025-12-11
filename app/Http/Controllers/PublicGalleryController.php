<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Event;
use App\Models\Photographer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;    // ← Para Route::has()
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PublicGalleryController extends Controller
{
    /**
     * Página principal con video promocional
     */
    public function index()
    {
        // Obtener eventos recientes PÚBLICOS
        $recentEvents = Event::with(['photographer.user'])
            ->where('is_active', true)
            ->where('is_private', false)
            ->orderBy('event_date', 'desc')
            ->take(15)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'description' => $event->description,
                    'location' => $event->location,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'cover_image_url' => $event->cover_image_url,
                    'photos_count' => $event->photos()->where('is_active', true)->count(),
                    'is_private' => $event->is_private,
                    'photographer' => optional($event->photographer)->business_name
                        ?? optional($event->photographer->user)->name
                        ?? 'Anónimo',
                ];
            });

        // OBTENER ÚLTIMAS FOTOS - Solo de eventos públicos
        $recentPhotos = Photo::with(['event', 'photographer.user'])
            ->where('is_active', true)
            ->whereHas('event', function ($query) {
                $query->where('is_active', true)
                    ->where('is_private', false);
            })
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'unique_id' => $photo->unique_id,
                    'original_url' => $photo->original_path
                        ? Storage::url($photo->original_path)
                        : null,
                    'thumbnail_url' => $photo->thumbnail_path
                        ? Storage::url($photo->thumbnail_path)
                        : null,
                    'watermarked_url' => $photo->watermarked_path
                        ? Storage::url($photo->watermarked_path)
                        : null,
                    'downloads' => $photo->downloads ?? 0,
                    'created_at' => $photo->created_at->toISOString(),
                    'event_name' => optional($photo->event)->name,
                    'event_slug' => optional($photo->event)->slug,
                    'event_is_private' => optional($photo->event)->is_private,
                    'photographer_name' => optional($photo->photographer)->business_name
                        ?? optional($photo->photographer->user)->name
                        ?? null,
                ];
            });

        //  EVENTOS FUTUROS CON COORDENADAS PARA EL MAPA
        $futureEvents = \App\Models\FutureEvent::with('photographer.user')
            ->upcoming()
            ->orderBy('event_date', 'asc')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'location' => $event->location,
                    'latitude' => $event->latitude ? (float) $event->latitude : null,
                    'longitude' => $event->longitude ? (float) $event->longitude : null,
                    'event_date' => $event->event_date,
                    'formatted_date' => $event->formatted_date,
                    'days_until' => $event->daysUntil(),
                    'cover_image' => $event->cover_image_url,
                    'status' => $event->status,
                    'photographer' => [
                        'id' => $event->photographer->id,
                        'business_name' => $event->photographer->business_name,
                        'name' => $event->photographer->user->name,
                        'slug' => $event->photographer->slug,
                    ],
                ];
            })
            ->filter(function ($event) {
                //  Filtrar solo eventos con coordenadas válidas
                return $event['latitude'] !== null && $event['longitude'] !== null;
            })
            ->values(); // Reiniciar índices del array

        // Estadísticas mejoradas (incluye eventos futuros para mostrar actividad)
        $stats = [
            'total_photos' => Photo::where('is_active', true)->count(),
            'total_events' => Event::where('is_active', true)->count()
                + \App\Models\FutureEvent::upcoming()->count(),
            'total_photographers' => Photographer::whereHas('user', function ($query) {
                $query->where('is_active', true);
            })->count(),
        ];

        $videoFiles = collect(File::files(public_path('videos')))
            ->filter(fn($file) => preg_match('/\.(mp4|mov|webm|m4v)$/i', $file->getFilename()))
            ->map(fn($file) => asset('videos/' . $file->getFilename()))
            ->values();

        // 🔍 DEBUG: Ver qué se envía (puedes quitar esto después)
        \Log::info('Home - Future Events:', [
            'count' => $futureEvents->count(),
            'events' => $futureEvents->toArray(),
        ]);

        return Inertia::render('Home', [
            'recentEvents' => $recentEvents,
            'recentPhotos' => $recentPhotos,
            'futureEvents' => $futureEvents,  //  AGREGAR ESTO
            'stats' => $stats,
            'videoList' => $videoFiles,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }






    /**
     * Galería completa con filtros
     */
    public function gallery(Request $request)
    {
        $query = Photo::with(['photographer', 'event'])
            ->where('is_active', true)
            ->whereHas('event', function ($q) {
                $q->where('is_active', true)
                    ->where('is_private', false);
            });

        // Filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('unique_id', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%')
                    ->orWhereHas('photographer', function ($q2) use ($request) {
                        $q2->where('business_name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('region') && $request->region !== 'all') {
            $query->whereHas('photographer', function ($q) use ($request) {
                $q->where('region', $request->region);
            });
        }

        if ($request->filled('event')) {
            $query->where('event_id', $request->event);
        }

        // Ordenamiento
        switch ($request->get('sort', 'recent')) {
            case 'popular':
                $query->orderByDesc('downloads');
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

        $photos = $query->paginate(24)->withQueryString();

        //  CORRECCIÓN: Transformar las fotos correctamente
        $photos->getCollection()->transform(function ($photo) {
            return [
                'id' => $photo->id,
                'unique_id' => $photo->unique_id,
                'title' => $photo->title,
                'price' => number_format($photo->price, 2), //  Formato correcto
                'thumbnail_url' => $photo->thumbnail_url,
                'watermarked_url' => $photo->watermarked_url,
                'photographer' => $photo->photographer->business_name, //  SOLO el nombre
            ];
        });

        // Obtener regiones únicas
        $regions = [
            ['value' => 'all', 'label' => 'Todas las regiones'],
            ['value' => 'norte', 'label' => 'Norte'],
            ['value' => 'centro', 'label' => 'Centro'],
            ['value' => 'sur', 'label' => 'Sur'],
            ['value' => 'oriente', 'label' => 'Oriente'],
            ['value' => 'poniente', 'label' => 'Poniente'],
        ];

        // Obtener eventos activos
        $events = Event::where('is_active', true)
            ->where('is_private', false)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Gallery/Index', [
            'photos' => $photos,
            'events' => $events,
            'regions' => $regions,
            'filters' => [
                'search' => $request->search,
                'region' => $request->region ?? 'all',
                'event' => $request->event,
                'sort' => $request->sort ?? 'recent',
            ],
        ]);
    }


    public function show($uniqueId)
    {
        $photo = Photo::where('unique_id', strtoupper($uniqueId))
            ->where('is_active', true)
            ->with([
                'photographer' => function ($query) {
                    $query->select('id', 'user_id', 'business_name', 'slug', 'region', 'bio', 'phone', 'profile_photo');
                },
                'photographer.user:id,email',
                'event:id,name,slug,event_date,location'
            ])
            ->firstOrFail();

        // 🔍 DEBUG: Ver todas las URLs
        \Log::info('Photo URLs Debug', [
            'unique_id' => $photo->unique_id,
            'watermarked_path' => $photo->watermarked_path,
            'thumbnail_path' => $photo->thumbnail_path,
            'view_url' => $photo->view_url,
            'thumbnail_view_url' => $photo->thumbnail_view_url,
            'watermarked_url (original)' => $photo->watermarked_url,
            'thumbnail_url (original)' => $photo->thumbnail_url,
        ]);

        // Fotos relacionadas...
        $relatedPhotos = Photo::where('is_active', true)
            ->where('id', '!=', $photo->id)
            ->where(function ($q) use ($photo) {
                $q->where('photographer_id', $photo->photographer_id)
                    ->when($photo->event_id, function ($q) use ($photo) {
                        $q->orWhere('event_id', $photo->event_id);
                    });
            })
            ->with('photographer:id,business_name')
            ->take(8)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'unique_id' => $p->unique_id,
                    'thumbnail_url' => $p->thumbnail_view_url ?? $p->thumbnail_url,
                    'price' => $p->price
                ];
            });

        return Inertia::render('Gallery/Show', [
            'photo' => [
                'id' => $photo->id,
                'unique_id' => $photo->unique_id,
                'title' => $photo->title,
                'description' => $photo->description,
                'price' => $photo->price,

                'watermarked_url' => $photo->view_url ?? $photo->watermarked_url,
                'thumbnail_url' => $photo->thumbnail_view_url ?? $photo->thumbnail_url,

                'downloads' => $photo->downloads,
                'width' => $photo->width,
                'height' => $photo->height,

                'photographer' => [
                    'id' => $photo->photographer->id,
                    'business_name' => $photo->photographer->business_name,
                    'slug' => $photo->photographer->slug,
                    'region' => $photo->photographer->region,
                    'bio' => $photo->photographer->bio,
                    'phone' => $photo->photographer->phone,
                    'email' => $photo->photographer->user->email ?? null,
                    'profile_photo_url' => $photo->photographer->profile_photo_url,
                    'website' => $photo->photographer->website ?? null,
                    'instagram' => $photo->photographer->instagram ?? null,
                ],
                'event' => $photo->event ? [
                    'id' => $photo->event->id,
                    'name' => $photo->event->name,
                    'slug' => $photo->event->slug,
                    'location' => $photo->event->location,
                ] : null,
                'created_at' => $photo->created_at->format('Y-m-d'),
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
    /**
     * Lista de todos los eventos públicos
     */
    public function showEvent(Request $request, $slug)
    {
        //  Buscar evento por slug (sin filtrar por is_private aún)
        $event = Event::where('slug', $slug)
            ->with([
                'photographer' => function ($query) {
                    $query->select('id', 'business_name', 'region', 'phone', 'profile_photo');
                },
                'photographer.user:id,email'
            ])
            ->withCount('photos')
            ->firstOrFail();

        //  VALIDACIÓN 1: Evento OCULTO (is_active = false)
        if (!$event->is_active) {
            abort(404, 'Este evento no está disponible públicamente');
        }

        //  VALIDACIÓN 2: Evento PRIVADO (requiere token)
        if ($event->is_private) {
            $token = $request->query('token');

            // Validar que el token sea correcto
            if (!$token || $token !== $event->private_token) {
                abort(403, 'No tenés permiso para ver este evento privado. Solicita el enlace correcto al fotógrafo.');
            }
        }

        //  VALIDACIÓN 3: Evento PÚBLICO (sin restricciones adicionales)

        // Query de fotos del evento
        $photosQuery = $event->photos()
            ->where('is_active', true)
            ->with([
                'photographer' => function ($query) {
                    $query->select('id', 'business_name', 'region');
                }
            ]);

        // Filtro por fotógrafo específico
        if ($request->filled('photographer_id')) {
            $photosQuery->where('photographer_id', $request->photographer_id);
        }

        // Fotos del evento con paginación
        $photos = $photosQuery->latest()->paginate(30)->withQueryString();

        // Obtener todos los fotógrafos únicos del evento con conteo de fotos
        $photographers = Photographer::select('photographers.id', 'photographers.business_name')
            ->join('photos', 'photographers.id', '=', 'photos.photographer_id')
            ->where('photos.event_id', $event->id) //  Corregido: usar event_id directo
            ->where('photos.is_active', true)
            ->with('user:id,name')
            ->selectRaw('COUNT(photos.id) as photos_count')
            ->groupBy('photographers.id', 'photographers.business_name')
            ->get();

        // Formatear datos de fotos
        $photos->getCollection()->transform(function ($photo) {
            return [
                'id' => $photo->id,
                'unique_id' => $photo->unique_id,
                'title' => $photo->title,
                'price' => $photo->price,
                'thumbnail_url' => $photo->thumbnail_url,
                'watermarked_url' => $photo->watermarked_url,
                'photographer' => [
                    'id' => $photo->photographer->id,
                    'business_name' => $photo->photographer->business_name,
                ],
            ];
        });

        return Inertia::render('Events/Show', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description,
                'long_description' => $event->long_description,
                'event_date' => $event->event_date->format('Y-m-d'),
                'location' => $event->location,
                'photos_count' => $event->photos_count,
                'cover_image_url' => $event->cover_image_url,
                'downloads' => $event->photos()->sum('downloads'),
                'is_private' => $event->is_private, //  Agregar para mostrar badge
                'photographer' => [
                    'business_name' => $event->photographer->business_name,
                    'region' => $event->photographer->region,
                    'phone' => $event->photographer->phone,
                    'email' => $event->photographer->user->email ?? null,
                    'profile_photo_url' => $event->photographer->profile_photo_url,
                ],
            ],
            'photos' => $photos,
            'photographers' => $photographers,
            'filters' => [
                'photographer_id' => $request->photographer_id,
            ],
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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $photographers = $query->paginate(12)->withQueryString();

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
        $events = Event::whereHas('photos', function ($q) use ($photographer) {
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
                'website' => $photographer->website,
                'instagram' => $photographer->instagram,
                'facebook' => $photographer->facebook,
                'email' => $photographer->user->email, // Email viene del usuario
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
        $filePath = storage_path('app/' . $photo->file_path);

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


    public function events(Request $request)
    {
        $query = Event::with([
            'photographer' => function ($query) {
                $query->select('id', 'business_name', 'region', 'profile_photo', 'user_id');
                $query->with('user:id,name'); // Cargar usuario por si no tiene business_name
            }
        ])
            ->where('is_active', true)
            ->where('is_private', false)
            ->withCount('photos');

        // --- FILTROS ---

        // 1. Búsqueda General
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        // 2. Filtro por Fecha
        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->date);
        }

        // 3. NUEVO: Filtro por Fotógrafo
        if ($request->filled('photographer_id')) {
            $query->where('photographer_id', $request->photographer_id);
        }

        // --- OBTENER RESULTADOS ---

        $events = $query->latest('event_date')
            ->paginate(12)
            ->withQueryString();

        // Formatear eventos para la vista
        $events->getCollection()->transform(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description,
                'event_date' => $event->event_date->format('Y-m-d'),
                'location' => $event->location,
                'cover_image_url' => $event->cover_image_url,
                'photos_count' => $event->photos_count,
                'photographer' => [
                    'id' => $event->photographer->id,
                    'business_name' => $event->photographer->business_name ?? $event->photographer->user->name,
                    'region' => $event->photographer->region,
                    'profile_photo_url' => $event->photographer->profile_photo_url,
                ],
            ];
        });

        // --- OBTENER LISTA DE FOTÓGRAFOS (Para el select del filtro) ---
        // Solo fotógrafos que tengan al menos un evento público activo
        $photographers = \App\Models\Photographer::whereHas('events', function ($q) {
            $q->where('is_active', true)->where('is_private', false);
        })
            ->select('id', 'business_name', 'user_id')
            ->with('user:id,name')
            ->orderBy('business_name')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'business_name' => $p->business_name ?? $p->user->name,
                ];
            });

        return Inertia::render('Events/Index', [
            'events' => $events,
            'photographers' => $photographers, // Enviamos la lista a la vista
            'filters' => $request->only(['search', 'date', 'photographer_id']),
        ]);
    }


}
