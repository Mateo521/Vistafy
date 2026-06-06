<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use App\Models\Photographer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class PublicGalleryController extends Controller
{
    public function index()
    {

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

        $recentPhotos = Photo::with(['event', 'photographer.user'])
            ->where('is_active', true)
            ->where(function ($query) {

                $query->whereNull('event_id')

                    ->orWhereHas('event', function ($q) {
                        $q->where('is_active', true)
                            ->where('is_private', false);
                    });
            })
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'unique_id' => $photo->unique_id,
                    'event_id' => $photo->event_id,

                    'original_url' => $photo->original_url,
                    'thumbnail_url' => $photo->thumbnail_url,
                    'watermarked_url' => $photo->watermarked_url,

                    'downloads' => $photo->downloads ?? 0,
                    'created_at' => $photo->created_at->toISOString(),
                    'event_name' => optional($photo->event)->name,
                    'event_slug' => optional($photo->event)->slug,
                    'event_is_private' => optional($photo->event)->is_private,
                    'photographer_name' => optional($photo->photographer)->business_name
                        ?? optional(optional($photo->photographer)->user)->name
                        ?? null,
                ];
            });

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

                return $event['latitude'] !== null && $event['longitude'] !== null;
            })
            ->values();

        $stats = [
            'total_photos' => Photo::where('is_active', true)->count(),
            'total_events' => Event::where('is_active', true)->count()
                + \App\Models\FutureEvent::upcoming()->count(),
            'total_photographers' => Photographer::whereHas('user', function ($query) {
                $query->where('is_active', true);
            })->count(),
        ];

        $videoFiles = collect(File::files(public_path('videos')))
            ->filter(fn ($file) => preg_match('/\.(mp4|mov|webm|m4v)$/i', $file->getFilename()))
            ->map(fn ($file) => asset('videos/'.$file->getFilename()))
            ->values();

        return Inertia::render('Home', [
            'recentEvents' => $recentEvents,
            'recentPhotos' => $recentPhotos,
            'futureEvents' => $futureEvents,  //
            'stats' => $stats,
            'videoList' => $videoFiles,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function gallery(Request $request)
    {

        $query = Photo::with(['photographer', 'event'])
            ->where('is_active', true)
            ->where(function ($q) {

                $q->whereNull('event_id')

                    ->orWhereHas('event', function ($eventQuery) {
                        $eventQuery->where('is_active', true)
                            ->where('is_private', false);
                    });
            });

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('unique_id', 'like', '%'.$request->search.'%')
                    ->orWhere('title', 'like', '%'.$request->search.'%')
                    ->orWhereHas('photographer', function ($q2) use ($request) {
                        $q2->where('business_name', 'like', '%'.$request->search.'%');
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

        $photos = $query->paginate(perPage: 25)->withQueryString();

        $photos->getCollection()->transform(function ($photo) {
            return [
                'id' => $photo->id,
                'unique_id' => $photo->unique_id,
                'title' => $photo->title,
                'price' => number_format($photo->price, 2),
                'thumbnail_url' => $photo->thumbnail_url,
                'watermarked_url' => $photo->watermarked_url,
                'photographer' => $photo->photographer->business_name,

                //  AGREGAR DATOS DE IA
                'has_faces' => $photo->has_faces,
                'bib_numbers' => $photo->bib_numbers
                    ? (is_string($photo->bib_numbers)
                        ? json_decode($photo->bib_numbers, true)
                        : $photo->bib_numbers
                    )
                    : null,
            ];
        });

        $regions = \App\Models\Photographer::whereNotNull('region')
            ->distinct()
            ->pluck('region')
            ->sort()
            ->values()
            ->toArray();

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

    public function bibSearch(Request $request)
    {
        $request->validate([
            'bib_number' => 'required|string|max:10',
        ]);

        $bibNumber = trim($request->bib_number);

        \Log::info(' Búsqueda global por dorsal', [
            'bib_number' => $bibNumber,
        ]);

        $photos = Photo::where('is_active', true)
            ->where('bib_processed', true)
            ->whereNotNull('bib_numbers')
            ->whereRaw('JSON_CONTAINS(bib_numbers, ?)', [json_encode($bibNumber)])
            ->with(['photographer.user', 'event'])
            ->get()
            ->map(function ($photo) {

                return [
                    'id' => $photo->id,
                    'unique_id' => $photo->unique_id,
                    'title' => $photo->title,
                    'description' => $photo->description,
                    'thumbnail_url' => $photo->thumbnail_url,
                    'watermarked_url' => $photo->watermarked_url,
                    'original_url' => $photo->original_url,
                    'price' => $photo->price,
                    'width' => $photo->width,
                    'height' => $photo->height,
                    'file_size' => $photo->file_size,
                    'mime_type' => $photo->mime_type,
                    'downloads' => $photo->downloads,
                    'views' => $photo->views,
                    'is_active' => $photo->is_active,
                    'created_at' => $photo->created_at,
                    'photographer' => $photo->photographer->business_name ?? $photo->photographer->user->name,
                    'photographer_id' => $photo->photographer_id,
                    'event_id' => $photo->event_id,
                    'event_name' => $photo->event?->name,
                    'bib_numbers' => $photo->bib_numbers,
                ];
            });

        \Log::info(' Resultados de búsqueda global por dorsal', [
            'bib_number' => $bibNumber,
            'total_found' => $photos->count(),
            'photo_ids' => $photos->pluck('id')->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'count' => $photos->count(),
            'results' => $photos,
        ]);
    }

    public function faceSearch(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|array|size:128',
            'face_descriptor.*' => 'required|numeric',
            'threshold' => 'nullable|numeric|min:0|max:1',
        ]);

        $searchDescriptor = $request->face_descriptor;
        $threshold = $request->threshold ?? 0.6;

        $photos = Photo::where('has_faces', true)
            ->whereNotNull('face_encodings')
            ->where('is_active', true)
            ->with(['photographer:id,slug,business_name', 'event:id,name,slug'])
            ->get();

        $results = [];

        foreach ($photos as $photo) {
            if (! $photo->face_encodings || ! is_array($photo->face_encodings)) {
                continue;
            }

            foreach ($photo->face_encodings as $photoDescriptor) {
                if (! is_array($photoDescriptor) || count($photoDescriptor) !== 128) {
                    continue;
                }

                $distance = $this->euclideanDistance($searchDescriptor, $photoDescriptor);

                if ($distance < $threshold) {
                    $results[] = [
                        'id' => $photo->id,
                        'unique_id' => $photo->unique_id,
                        'original_name' => $photo->original_name,
                        'thumbnail_url' => $photo->thumbnail_url,
                        'watermarked_url' => $photo->watermarked_url,
                        'price' => $photo->price,
                        'photographer' => $photo->photographer->business_name ?? 'Desconocido',
                        'event_name' => $photo->event->name ?? 'Sin evento',
                        'event_slug' => $photo->event->slug ?? null,
                        'distance' => round($distance, 4),
                        'similarity' => round(max(0, 1 - $distance), 4),
                    ];
                    break;
                }
            }
        }

        usort($results, fn ($a, $b) => $a['distance'] <=> $b['distance']);

        return response()->json([
            'success' => true,
            'count' => count($results),
            'results' => $results,
            'threshold_used' => $threshold,
        ]);
    }

    /**
     * Calcular distancia euclidiana entre dos vectores de 128 dimensiones
     *
     * Esta función compara dos "huellas dactilares faciales" y devuelve
     * qué tan diferentes son. Mientras más bajo el número, más parecidos son los rostros.
     *
     * @param  array  $vec1  Descriptor facial 1 (128 números)
     * @param  array  $vec2  Descriptor facial 2 (128 números)
     * @return float Distancia (0 = idénticos, >0.6 = diferentes)
     */
    private function euclideanDistance(array $vec1, array $vec2): float
    {
        if (count($vec1) !== count($vec2)) {
            throw new \InvalidArgumentException('Los vectores deben tener la misma longitud');
        }

        $sum = 0.0;
        for ($i = 0; $i < count($vec1); $i++) {
            $sum += pow($vec1[$i] - $vec2[$i], 2);
        }

        return sqrt($sum);
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
                'event:id,name,slug,event_date,location',
            ])
            ->firstOrFail();

        \Log::info('Photo URLs Debug', [
            'unique_id' => $photo->unique_id,
            'watermarked_path' => $photo->watermarked_path,
            'thumbnail_path' => $photo->thumbnail_path,
            'view_url' => $photo->view_url,
            'thumbnail_view_url' => $photo->thumbnail_view_url,
            'watermarked_url (original)' => $photo->watermarked_url,
            'thumbnail_url (original)' => $photo->thumbnail_url,
        ]);

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
                    'price' => $p->price,
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

    public function search(Request $request)
    {
        $request->validate([
            'id' => 'required|string|min:3',
        ]);

        $uniqueId = strtoupper(trim($request->id));

        $photo = Photo::where('unique_id', $uniqueId)
            ->where('is_active', true)
            ->first();

        if ($photo) {
            return redirect()->route('gallery.show', $photo->unique_id);
        }

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
    public function showEvent(Request $request, $slug)
    {

        $event = Event::where('slug', $slug)
            ->with([
                'photographer' => function ($query) {
                    $query->select('id', 'business_name', 'region', 'phone', 'profile_photo');
                },
                'photographer.user:id,email',
            ])
            ->withCount('photos')
            ->firstOrFail();

        if (! $event->is_active) {
            abort(404, 'Este evento no está disponible públicamente');
        }

        if ($event->is_private) {
            $token = $request->query('token');

            if (! $token || $token !== $event->private_token) {
                abort(403, 'No tenés permiso para ver este evento privado. Solicita el enlace correcto al fotógrafo.');
            }
        }

        $photosQuery = $event->photos()
            ->where('is_active', true)
            ->with([
                'photographer' => function ($query) {
                    $query->select('id', 'business_name', 'region');
                },
            ]);

        if ($request->filled('photographer_id')) {
            $photosQuery->where('photographer_id', $request->photographer_id);
        }

        $photos = $photosQuery->latest()->paginate(30)->withQueryString();

        $photographers = Photographer::select('photographers.id', 'photographers.business_name')
            ->join('photos', 'photographers.id', '=', 'photos.photographer_id')
            ->where('photos.event_id', $event->id) //  Corregido: usar event_id directo
            ->where('photos.is_active', true)
            ->with('user:id,name')
            ->selectRaw('COUNT(photos.id) as photos_count')
            ->groupBy('photographers.id', 'photographers.business_name')
            ->get();

        $photos->getCollection()->transform(function ($photo) {
            return [
                'id' => $photo->id,
                'unique_id' => $photo->unique_id,
                'title' => $photo->title,
                'price' => $photo->price,
                'thumbnail_url' => $photo->getThumbnailUrlAttribute(), 
                'watermarked_url' => $photo->getWatermarkedUrlAttribute(),
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

    public function photographers(Request $request)
    {
        $query = Photographer::where('is_active', true)
            ->with('user:id,name,email')
            ->withCount('photos');

        if ($request->has('region') && $request->region !== 'all') {
            $query->where('region', $request->region);
        }

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

    public function showPhotographer($id)
    {
        $photographer = Photographer::where('id', $id)
            ->where('is_active', true)
            ->with('user:id,name,email')
            ->withCount('photos')
            ->firstOrFail();

        $photos = Photo::where('photographer_id', $photographer->id)
            ->where('is_active', true)
            ->latest()
            ->paginate(24);

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
                $query->with('user:id,name');
            },
        ])
            ->where('is_active', true)
            ->where('is_private', false)
            ->withCount('photos');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%')
                    ->orWhere('location', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->date);
        }

        if ($request->filled('photographer_id')) {
            $query->where('photographer_id', $request->photographer_id);
        }

        $events = $query->latest('event_date')
            ->paginate(12)
            ->withQueryString();

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
            'photographers' => $photographers,
            'filters' => $request->only(['search', 'date', 'photographer_id']),
        ]);
    }
}
