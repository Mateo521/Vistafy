<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EventController extends Controller
{

public function inviteColleague(Request $request, Event $event)
{
    if ($event->photographer_id !== auth()->user()->photographer->id) {
        return redirect()->back()->withErrors(['email' => 'Solo el creador puede invitar colaboradores.']);
    }

    $request->validate([
        'email' => 'required|email'
    ]);

    $invitedUser = User::where('email', $request->email)
        ->whereHas('photographer', function($q) {
            $q->where('status', 'approved');
        })->first();

    if (!$invitedUser) {
        return redirect()->back()->withErrors(['email' => 'No se encontró un fotógrafo aprobado con este correo en f33.']);
    }

    $invitedPhotographer = $invitedUser->photographer;

    if ($invitedPhotographer->id === $event->photographer_id) {
        return redirect()->back()->withErrors(['email' => 'Ya sos el creador de este evento.']);
    }

    if ($event->collaborators()->where('photographer_id', $invitedPhotographer->id)->exists()) {
        return redirect()->back()->withErrors(['email' => 'Este fotógrafo ya forma parte del evento o fue invitado.']);
    }


    $event->collaborators()->attach($invitedPhotographer->id, ['status' => 'invited']);

    $inviter = auth()->user()->photographer;


    try {
        Mail::to($invitedUser->email)->send(new \App\Mail\EventInvitationMail($event, $inviter));
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error enviando invitación por Brevo: ' . $e->getMessage());
        return redirect()->back()->with('success', 'El fotógrafo fue invitado, pero el correo de aviso no se pudo enviar.');
    }

    return redirect()->back()->with('success', 'Invitación enviada por correo.');
}


    public function index(Request $request)
    {
        $photographer = $request->user()->photographer;

        $myEventsQuery = $photographer->events()
            ->withCount('photos');

        $this->applyFilters($myEventsQuery, $request);

        $myEvents = $myEventsQuery
            ->orderBy($request->get('sort', 'event_date'), $request->get('order', 'desc'))
            ->paginate(9, ['*'], 'page')
            ->withQueryString();

        $collaborationsQuery = $photographer->guestEvents()
            ->withCount('photos')
            ->with('photographer');

        $this->applyFilters($collaborationsQuery, $request);

        $collaborations = $collaborationsQuery
            ->orderBy($request->get('sort', 'event_date'), $request->get('order', 'desc'))
            ->paginate(9, ['*'], 'collab_page')
            ->withQueryString();

        $stats = [
            'total_events' => $photographer->events()->count(),
            'active_events' => $photographer->events()->where('is_active', true)->count(),
            'total_photos' => \App\Models\Photo::where('photographer_id', $photographer->id)->count(),
            'total_sales' => 0,
        ];

        return Inertia::render('Photographer/Events/Index', [
            'events' => $myEvents,
            'collaborations' => $collaborations,
            'stats' => $stats,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'sort', 'order']),
        ]);
    }

    private function applyFilters($query, $request)
    {
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%')
                    ->orWhere('location', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('event_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_date', '<=', $request->date_to);
        }
    }

    public function create()
    {
        return Inertia::render('Photographer/Events/Create');
    }

    public function store(Request $request)
    {
        $photographer = auth()->user()->photographer;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string|max:2000',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'is_private' => 'boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB
        ]);

        $slug = Str::slug($validated['name']).'-'.Str::random(6);

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('events/covers', 'b2');
        }

        $event = Event::create([
            'photographer_id' => $photographer->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'long_description' => $validated['long_description'] ?? null,
            'event_date' => $validated['event_date'],
            'location' => $validated['location'] ?? null,
            'is_private' => $validated['is_private'] ?? false,
            'cover_image' => $coverImagePath,
        ]);

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento creado exitosamente');
    }

   public function show($id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::with(['photographer', 'collaborators'])
            ->findOrFail($id);

        $isOwner = $event->photographer_id === $photographer->id;
        
    
        $collaboratorRecord = $event->collaborators->firstWhere('id', $photographer->id);
        $isCollaborator = $collaboratorRecord && $collaboratorRecord->pivot->status === 'approved';

    
        if (! $isOwner && ! $isCollaborator) {
            abort(403, 'No tenés permiso para gestionar este evento.');
        }

        $photos = $event->photos()
            ->with('photographer:id,business_name')  
            ->latest()
            ->paginate(100) 
            ->withQueryString();

        $stats = [
            'total_photos' => $event->photos()->count(),
            'active_photos' => $event->photos()->where('is_active', true)->count(),
            'total_downloads' => $event->photos()->sum('downloads'),
        ];

    
        $unassignedPhotos = Photo::where('photographer_id', $photographer->id)
            ->whereNull('event_id')
            ->latest()
            ->get(['id', 'unique_id', 'thumbnail_path', 'watermarked_path', 'original_name'])
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'unique_id' => $photo->unique_id,
                    'thumbnail_url' => $photo->thumbnail_url,
                    'original_name' => $photo->original_name,
                ];
            });

        return Inertia::render('Photographer/Events/Show', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description,
                'long_description' => $event->long_description,
                'event_date' => $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') : null,
                'location' => $event->location,
                'cover_image' => $event->cover_image,
                'cover_image_url' => $event->cover_image_url,
                'is_private' => (bool) $event->is_private,
                'is_active' => (bool) $event->is_active,
                'private_token' => $event->private_token,
                'photographer_id' => $event->photographer_id,
                'is_owner' => $isOwner,
                'photographer' => [
                    'id' => $event->photographer->id,
                    'business_name' => $event->photographer->business_name,
                    'profile_photo_url' => $event->photographer->profile_photo_url,
                ],
                'collaborators' => $event->collaborators->map(function ($collab) {
                    return [
                        'id' => $collab->id,
                        'business_name' => $collab->business_name,
                        'profile_photo_url' => $collab->profile_photo_url,
                        'pivot' => [
                            'status' => $collab->pivot->status ?? 'pending',
                        ],
                    ];
                }),
            ],
        
            'permissions' => [
                'is_creator' => $isOwner,
                'can_edit_event' => $isOwner,
                'can_delete_event' => $isOwner,
            ],
            'current_photographer_id' => $photographer->id,  
            'photos' => $photos,
            'stats' => $stats,
            'unassignedPhotos' => $unassignedPhotos,  
        ]);
    }

    
    public function edit(Event $event)
    {
        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403);
        }

        $eventData = [
            'id' => $event->id,
            'name' => $event->name,
            'description' => $event->description ?? '',
            'long_description' => $event->long_description ?? '',
            'event_date' => $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('Y-m-d') : null,
            'location' => $event->location ?? '',
            'is_private' => (bool) $event->is_private,
            'is_active' => (bool) $event->is_active,
            'cover_image' => $event->cover_image,
            'cover_image_url' => $event->cover_image_url,   
        ];

        return Inertia::render('Photographer/Events/Edit', [
            'event' => $eventData,
        ]);
    }

    public function update(Request $request, Event $event)
    {

        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tenés permiso para actualizar este evento');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string|max:2000',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'is_private' => 'boolean',
            'is_active' => 'boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image) {
                \Storage::disk('b2')->delete($event->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('events/covers', 'b2');
        }

        $event->update($validated);

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento actualizado exitosamente');
    }

    public function destroy(Event $event)
    {

        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tenés permiso para eliminar este evento.');
        }

        DB::beginTransaction();

        try {

            if ($event->cover_image) {
                Storage::disk('b2')->delete($event->cover_image);
            }

            foreach ($event->photos as $photo) {

                if ($photo->original_path) {
                    Storage::disk('b2')->delete($photo->original_path);
                }
                if ($photo->watermarked_path) {
                    Storage::disk('b2')->delete($photo->watermarked_path);
                }
                if ($photo->thumbnail_path) {
                    Storage::disk('b2')->delete($photo->thumbnail_path);
                }

                $photo->delete();
            }

            $event->delete();

            DB::commit();

            return redirect()->route('photographer.events.index')
                ->with('success', 'Evento y todas sus fotos eliminadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar evento: '.$e->getMessage());

            return back()->with('error', 'Error al eliminar el evento: '.$e->getMessage());
        }
    }

    
public function updateCoverImage(Request $request, Event $event)
    {

        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tenés permiso para actualizar este evento');
        }


        $request->validate([
            'photo_id' => 'required|exists:photos,id',
        ]);


        $photo = \App\Models\Photo::findOrFail($request->photo_id);


        if ($photo->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No autorizado para usar esta fotografía');
        }

        try {

            $event->update([
                'cover_image' => $photo->thumbnail_path, 
            ]);

            return back()->with('success', 'Portada actualizada correctamente');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar portada: ' . $e->getMessage());
            return back()->with('error', 'Error al establecer la portada.');
        }
    }



    public function bibSearch(Event $event)
    {
        return inertia('Events/BibSearch', [
            'event' => $event->load('photographer'),
        ]);
    }

    public function searchByBib(Request $request, Event $event)
    {
        $request->validate([
            'bib_number' => 'required|string|max:10',
        ]);

        $bibNumber = trim($request->bib_number);

        \Log::info(' Búsqueda por dorsal', [
            'event_id' => $event->id,
            'bib_number' => $bibNumber,
        ]);

        $photos = Photo::where('event_id', $event->id)
            ->where('is_active', true)
            ->where('bib_processed', true)
            ->whereNotNull('bib_numbers')
            ->whereRaw('JSON_CONTAINS(bib_numbers, ?)', [json_encode($bibNumber)])
            ->with(['photographer.user'])
            ->paginate(20)
            ->through(fn ($photo) => [
                'id' => $photo->id,
                'unique_id' => $photo->unique_id,
                'thumbnail_url' => $photo->thumbnail_url,
                'watermarked_url' => $photo->watermarked_url,
                'photographer_name' => $photo->photographer->business_name ?? $photo->photographer->user->name,
                'bib_numbers' => $photo->bib_numbers,
            ]);

        \Log::info(' Resultados de Búsqueda por dorsal', [
            'bib_number' => $bibNumber,
            'total_found' => $photos->total(),
        ]);

        return inertia('Events/BibSearch', [
            'event' => $event->load('photographer'),
            'photos' => $photos,
            'searchedBib' => $bibNumber,
        ]);
    }
}
