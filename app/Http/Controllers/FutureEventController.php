<?php

namespace App\Http\Controllers;

use App\Models\FutureEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class FutureEventController extends Controller
{
    /**
     * 🏠 Listar eventos futuros para el Home
     * - Público: 3 eventos
     * - Fotógrafos: Todos los eventos
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isPhotographer = $user && $user->role === 'photographer';

            // Determinar items por página
            $perPage = $isPhotographer ? 6 : 3;

            // Query base
            $query = FutureEvent::with('photographer.user')
                ->upcoming()
                ->orderBy('event_date', 'asc');

            // Paginar
            $paginatedEvents = $query->paginate($perPage);

            // Mapear datos
            $futureEvents = $paginatedEvents->map(function ($event) {
                $photographer = $event->photographer;

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'location' => $event->location,
                    'event_date' => $event->event_date->format('Y-m-d H:i:s'),
                    'formatted_date' => $event->formatted_date,
                    'days_until' => $event->daysUntil(),
                    'cover_image' => $event->cover_image_url,
                    'photographer' => [
                        'id' => $photographer?->id ?? 0,
                        'business_name' => $photographer?->business_name ?? 'Fotógrafo no disponible',
                        'name' => $photographer?->user?->name ?? 'N/A',
                    ],
                ];
            });

            return response()->json([
                'future_events' => $futureEvents,
                'is_photographer' => $isPhotographer,
                'total_events' => FutureEvent::upcoming()->count(),
                'current_page' => $paginatedEvents->currentPage(),
                'last_page' => $paginatedEvents->lastPage(),
                'has_more_pages' => $paginatedEvents->hasMorePages(),
                'per_page' => $paginatedEvents->perPage(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FutureEventController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'future_events' => [],
                'is_photographer' => false,
                'total_events' => 0,
                'current_page' => 1,
                'has_more_pages' => false,
                'error' => 'Error al cargar eventos futuros',
            ], 500);
        }
    }

    /**
     * 📄 Ver detalle de un evento futuro
     */
    public function show(FutureEvent $futureEvent)
    {
        $user = Auth::user();
        $isPhotographer = $user && $user->role === 'photographer';

        // Solo fotógrafos pueden ver detalles completos
        if (!$isPhotographer) {
            return response()->json([
                'message' => 'Debes ser fotógrafo registrado para ver los detalles completos'
            ], 403);
        }

        return Inertia::render('FutureEvents/Show', [
            'event' => [
                'id' => $futureEvent->id,
                'title' => $futureEvent->title,
                'description' => $futureEvent->description,
                'location' => $futureEvent->location,
                'event_date' => $futureEvent->event_date->format('Y-m-d H:i:s'),
                'formatted_date' => $futureEvent->formatted_date,
                'days_until' => $futureEvent->daysUntil(),
                'image' => $futureEvent->image,
            ],
        ]);
    }
}
