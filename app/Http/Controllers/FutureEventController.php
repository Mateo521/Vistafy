<?php

namespace App\Http\Controllers;

use App\Models\FutureEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class FutureEventController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isPhotographer = $user && $user->role === 'photographer';
            $isAuthenticated = $user !== null;

            $mode = $request->query('mode', 'default');

            
            $baseQuery = FutureEvent::with('photographer.user')
                ->upcoming()
                ->whereHas('photographer', function ($q) {
                    $q->where('status', 'approved');  
                })
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('event_date', 'asc');

            $totalEvents = $baseQuery->count();

            if ($mode === 'map') {
                $events = $baseQuery->get()->map(function ($event) {
                    return $this->mapEventData($event);
                });

                return response()->json([
                    'future_events' => $events,
                    'is_photographer' => $isPhotographer,
                    'is_authenticated' => $isAuthenticated,
                    'total_events' => $totalEvents,
                    'showing_limited' => false,
                    'current_page' => 1,
                    'last_page' => 1,
                    'has_more_pages' => false,
                    'per_page' => $totalEvents,
                ]);
            }

            if ($isPhotographer) {
                $paginatedEvents = $baseQuery->paginate(perPage: 12);

                $futureEvents = $paginatedEvents->map(fn($event) => $this->mapEventData($event));

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

            $futureEvents = $baseQuery->take(6)->get()->map(fn($event) => $this->mapEventData($event));

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


    public function acceptInvitation(\App\Models\Event $event)
    {
        $photographerId = auth()->user()->photographer->id;

        $pivot = $event->collaborators()->where('photographer_id', $photographerId)->first();

        if (!$pivot || !in_array($pivot->pivot->status, ['pending', 'invited'])) {
            return redirect()->back()->with('error', 'No tenés invitaciones pendientes para este evento.');
        }

        $event->collaborators()->updateExistingPivot($photographerId, ['status' => 'approved']);

        return redirect()->back()->with('success', '¡Invitación aceptada! Ya puedes subir fotos a este evento.');
    }

    public function rejectInvitation(\App\Models\Event $event)
    {
        $photographerId = auth()->user()->photographer->id;


        $event->collaborators()->detach($photographerId);

        return redirect()->back()->with('success', 'Invitación rechazada.');
    }


    public function page()
    {
        return Inertia::render('FutureEvents/Index');
    }

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

   
    
public function show($id)
    {
        $event = FutureEvent::with(['photographer.user'])
            ->whereHas('photographer', function ($q) {
                $q->where('status', 'approved');  
            })
            ->findOrFail($id);

        if ($event->converted_event_id) {
            $convertedEvent = \App\Models\Event::find($event->converted_event_id);
            if ($convertedEvent) {
                return redirect()->route('events.show', $convertedEvent->slug);
            }
        }

       
        $userApplicationStatus = 'none';

        if (auth()->check() && auth()->user()->photographer) {
            
            $pivot = $event->collaborators()->where('photographer_id', auth()->user()->photographer->id)->first();
            if ($pivot) {
                $userApplicationStatus = $pivot->pivot->status; // 'requested', 'approved', etc.
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
                
                'formatted_time' => $event->event_date->format('H:i'), 
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
            
            'isPhotographer' => auth()->check() && auth()->user()->photographer !== null,
            'isAuthenticated' => auth()->check(),
            'userApplicationStatus' => $userApplicationStatus,
        ]);
    }

   
    public function apply(\App\Models\FutureEvent $event)
    {
        $applicant = auth()->user()->photographer;

        
        if ($event->photographer_id === $applicant->id) {
            return redirect()->back()->with('error', 'Ya eres el organizador de este evento.');
        }

        
        if ($event->collaborators()->where('photographer_id', $applicant->id)->exists()) {
            return redirect()->back()->with('error', 'Ya tienes una solicitud en curso o eres parte de este evento.');
        }

        
        $event->collaborators()->attach($applicant->id, ['status' => 'requested']);

        
        try {
             
            $event->load('photographer.user');

            \Illuminate\Support\Facades\Mail::to($event->photographer->user->email)
                ->send(new \App\Mail\PhotographerApplicationMail($event, $applicant));
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando postulación: ' . $e->getMessage());
            
         
            return redirect()->back()->with('success', 'Postulación guardada correctamente, aunque hubo un problema enviando el correo de aviso al organizador.');
        }

        return redirect()->back()->with('success', '¡Objetivo fijado! Tu postulación ha sido enviada al organizador.');
    }

}