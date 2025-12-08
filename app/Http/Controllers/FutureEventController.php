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
     *  Listar eventos futuros para el Home
     * - Público: 3 eventos
     * - Fotógrafos: Todos los eventos
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isPhotographer = $user && $user->role === 'photographer';
            $isAuthenticated = $user !== null;

            // Total de eventos disponibles
            $totalEvents = FutureEvent::upcoming()->count();

            // Query base
            $baseQuery = FutureEvent::with('photographer.user')
                ->upcoming()
                ->orderBy('event_date', 'asc');

            // ============================================
            // CASO 1: FOTÓGRAFO → Paginación completa
            // ============================================
            if ($isPhotographer) {
                $paginatedEvents = $baseQuery->paginate(12);

                $futureEvents = $paginatedEvents->map(function ($event) {
                    return $this->mapEventData($event);
                });

                return response()->json([
                    'future_events' => $futureEvents,
                    'is_photographer' => true,
                    'is_authenticated' => true,
                    'total_events' => $totalEvents,
                    'showing_limited' => false,
                    'current_page' => $paginatedEvents->currentPage(),
                    'last_page' => $paginatedEvents->lastPage(),
                    'has_more_pages' => $paginatedEvents->hasMorePages(),
                    'per_page' => $paginatedEvents->perPage(),
                ]);
            }

            // ============================================
            // CASO 2 y 3: NO FOTÓGRAFO → Solo 6 eventos
            // (autenticado o no, da igual)
            // ============================================
            $futureEvents = $baseQuery->take(6)->get()->map(function ($event) {
                return $this->mapEventData($event);
            });

            return response()->json([
                'future_events' => $futureEvents,
                'is_photographer' => false,
                'is_authenticated' => $isAuthenticated,
                'total_events' => $totalEvents,
                'showing_limited' => true, //  Siempre limitado si no es fotógrafo
                'current_page' => 1,
                'last_page' => 1,
                'has_more_pages' => false, //  NUNCA hay más páginas para no-fotógrafos
                'per_page' => 6,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en FutureEventController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'future_events' => [],
                'is_photographer' => false,
                'is_authenticated' => false,
                'total_events' => 0,
                'showing_limited' => false,
                'current_page' => 1,
                'has_more_pages' => false,
                'error' => 'Error al cargar eventos futuros',
            ], 500);
        }
    }

    /**
     * Helper: Mapear datos de un evento
     */
    private function mapEventData($event)
    {
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
    }

    /**
     *  Ver detalle de un evento futuro
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $isPhotographer = $user && $user->role === 'photographer';
            $isAuthenticated = $user !== null;

            // Buscar el evento con el fotógrafo organizador
            $event = FutureEvent::with('photographer.user')
                ->upcoming()
                ->findOrFail($id);

            // Mapear datos para la vista
            $eventData = [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'event_date' => $event->event_date,
                'formatted_date' => $event->formatted_date,
                'formatted_time' => $event->event_date->format('H:i'),
                'days_until' => $event->daysUntil(),
                'cover_image' => $event->cover_image_url,
                'status' => $event->status,
                'photographer' => [
                    'id' => $event->photographer->id,
                    'business_name' => $event->photographer->business_name,
                    'name' => $event->photographer->user->name,
                    'email' => $event->photographer->user->email,
                ],
            ];

            return Inertia::render('FutureEvents/Show', [
                'event' => $eventData,
                'isPhotographer' => $isPhotographer,
                'isAuthenticated' => $isAuthenticated,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al mostrar evento futuro', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('home')
                ->with('error', 'Evento no encontrado');
        }
    }
}
