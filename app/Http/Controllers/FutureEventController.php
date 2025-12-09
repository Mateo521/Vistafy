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
     * - Público: 6 eventos
     * - Fotógrafos: Todos los eventos (paginados)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isPhotographer = $user && $user->role === 'photographer';
            $isAuthenticated = $user !== null;

            // Total de eventos disponibles CON COORDENADAS (para el mapa)
            $totalEvents = FutureEvent::upcoming()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->count();

            // Query base - SOLO EVENTOS CON COORDENADAS
            $baseQuery = FutureEvent::with('photographer.user')
                ->upcoming()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
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
            // ============================================
            $futureEvents = $baseQuery->take(6)->get()->map(function ($event) {
                return $this->mapEventData($event);
            });

            return response()->json([
                'future_events' => $futureEvents,
                'is_photographer' => false,
                'is_authenticated' => $isAuthenticated,
                'total_events' => $totalEvents,
                'showing_limited' => true,
                'current_page' => 1,
                'last_page' => 1,
                'has_more_pages' => false,
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
     *  Helper: Mapear datos de un evento (INCLUYENDO COORDENADAS)
     */
    private function mapEventData($event)
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'location' => $event->location,
            'latitude' => $event->latitude ? (float) $event->latitude : null,
            'longitude' => $event->longitude ? (float) $event->longitude : null,
            'event_date' => $event->event_date->format('Y-m-d H:i:s'),
            'formatted_date' => $event->formatted_date,
            'days_until' => $event->daysUntil(),
            'cover_image' => $event->cover_image_url,
            'status' => $event->status,
            'photographer' => [
                'id' => $event->photographer->id,
                'business_name' => $event->photographer->business_name,
                'name' => $event->photographer->user->name,
                'slug' => $event->photographer->slug,
                'profile_photo' => $event->photographer->profile_photo_url,
                'bio' => $event->photographer->bio,
                'region' => $event->photographer->region,
                'instagram' => $event->photographer->instagram,
                'facebook' => $event->photographer->facebook,
                'website' => $event->photographer->website,
            ],
        ];
    }

    /**
     *  Ver detalle de un evento futuro
     */
    public function show($id)
    {
        $event = FutureEvent::with(['photographer.user'])
            ->findOrFail($id);

        // Si ya pasó el evento, redirigir al evento convertido
        if ($event->converted_event_id) {
            $convertedEvent = \App\Models\Event::find($event->converted_event_id);
            if ($convertedEvent) {
                return redirect()->route('events.show', $convertedEvent->slug);
            }
        }

        return Inertia::render('FutureEvents/Show', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'latitude' => $event->latitude ? (float) $event->latitude : null,
                'longitude' => $event->longitude ? (float) $event->longitude : null,
                'event_date' => $event->event_date->format('Y-m-d H:i:s'),
                'formatted_date' => $event->formatted_date,
                'days_until' => $event->daysUntil(),
                'cover_image' => $event->cover_image_url,
                'status' => $event->status,
                'photographer' => [
                    'id' => $event->photographer->id,
                    'business_name' => $event->photographer->business_name,
                    'name' => $event->photographer->user->name,
                    'slug' => $event->photographer->slug,
                    'profile_photo' => $event->photographer->profile_photo_url,
                    'bio' => $event->photographer->bio,
                    'region' => $event->photographer->region,
                    'instagram' => $event->photographer->instagram,
                    'website' => $event->photographer->website,
                ],
            ],
        ]);
    }


}
