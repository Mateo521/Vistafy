<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Photo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class EventManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'photographer']);
    }

  
    public function index()
    {
        $photographer = auth()->user()->photographer;

      
        $events = Event::whereHas('photos', function ($query) use ($photographer) {
            $query->where('photographer_id', $photographer->id);
        })
            ->withCount('photos')
            ->latest()
            ->paginate(12);

        return Inertia::render('Photographer/Events/Index', [
            'events' => $events,
        ]);
    }

    
    public function create()
    {
        $photographer = auth()->user()->photographer;

      
        $availablePhotos = Photo::where('photographer_id', $photographer->id)
            ->where('is_active', true)
            ->latest()
            ->get();

        return Inertia::render('Photographer/Events/Create', [
            'availablePhotos' => $availablePhotos,
        ]);
    }

  
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'is_private' => 'boolean',
            'photo_ids' => 'array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

       
        $event = Event::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'description' => $request->description,
            'event_date' => $request->event_date,
            'location' => $request->location,
            'is_private' => $request->is_private ?? false,
            'private_token' => $request->is_private ? Str::random(32) : null,
            'is_active' => true,
        ]);

      
        if ($request->has('photo_ids') && !empty($request->photo_ids)) {
            $photographer = auth()->user()->photographer;

          
            $validPhotos = Photo::where('photographer_id', $photographer->id)
                ->whereIn('id', $request->photo_ids)
                ->pluck('id')
                ->toArray();

         
            foreach ($validPhotos as $index => $photoId) {
                $event->photos()->attach($photoId, ['order' => $index]);
            }
        }

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento creado exitosamente');
    }

   
    public function show($id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::whereHas('photos', function ($query) use ($photographer) {
            $query->where('photographer_id', $photographer->id);
        })
            ->with([
                'photos' => function ($query) use ($photographer) {
                    $query->where('photographer_id', $photographer->id)
                        ->orderBy('event_photo.order');
                }
            ])
            ->findOrFail($id);

    
        $availablePhotos = Photo::where('photographer_id', $photographer->id)
            ->where('is_active', true)
            ->whereNotIn('id', $event->photos->pluck('id'))
            ->latest()
            ->get();

        return Inertia::render('Photographer/Events/Show', [
            'event' => $event,
            'availablePhotos' => $availablePhotos,
        ]);
    }

  
    public function edit($id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::whereHas('photos', function ($query) use ($photographer) {
            $query->where('photographer_id', $photographer->id);
        })
            ->with([
                'photos' => function ($query) use ($photographer) {
                    $query->where('photographer_id', $photographer->id)
                        ->orderBy('event_photo.order');
                }
            ])
            ->findOrFail($id);

       
        $eventData = [
            'id' => $event->id,
            'name' => $event->name,
            'description' => $event->description,
            'event_date' => $event->event_date,
            'location' => $event->location,
            'is_private' => $event->is_private,
            'is_active' => $event->is_active,
            'cover_image' => $event->cover_image,
            'cover_image_url' => $event->cover_image ? asset('storage/' . $event->cover_image) : null,
            'photos' => $event->photos,
            'photos_count' => $event->photos->count(),
        ];

        
        $availablePhotos = Photo::where('photographer_id', $photographer->id)
            ->where('is_active', true)
            ->latest()
            ->get();

        return Inertia::render('Photographer/Events/Edit', [
            'event' => $eventData,
            'availablePhotos' => $availablePhotos,
        ]);
    }

   
    public function update(Request $request, $id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::whereHas('photos', function ($query) use ($photographer) {
            $query->where('photographer_id', $photographer->id);
        })->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'is_private' => 'boolean',
            'is_active' => 'boolean',
            'photo_ids' => 'array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

       
        $event->update([
            'name' => $request->name,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'location' => $request->location,
            'is_private' => $request->is_private ?? $event->is_private,
            'is_active' => $request->is_active ?? $event->is_active,
        ]);

      
        if ($request->is_private && !$event->private_token) {
            $event->update(['private_token' => Str::random(32)]);
        }

  
        if ($request->has('photo_ids')) {
       
            $validPhotos = Photo::where('photographer_id', $photographer->id)
                ->whereIn('id', $request->photo_ids)
                ->pluck('id')
                ->toArray();

           
            $syncData = [];
            foreach ($validPhotos as $index => $photoId) {
                $syncData[$photoId] = ['order' => $index];
            }

            $event->photos()->sync($syncData);
        }

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento actualizado exitosamente');
    }

    
    public function destroy($id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::whereHas('photos', function ($query) use ($photographer) {
            $query->where('photographer_id', $photographer->id);
        })->findOrFail($id);

       
        $event->photos()->wherePivot('photographer_id', $photographer->id)->detach();

       
        if ($event->photos()->count() == 0) {
            $event->delete();
        }

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento eliminado exitosamente');
    }

    
    public function addPhoto(Request $request, $id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::findOrFail($id);

        $request->validate([
            'photo_id' => 'required|exists:photos,id',
        ]);

 
        $photo = Photo::where('id', $request->photo_id)
            ->where('photographer_id', $photographer->id)
            ->firstOrFail();

       
        if (!$event->photos()->where('photo_id', $photo->id)->exists()) {
            $maxOrder = $event->photos()->max('order') ?? -1;
            $event->photos()->attach($photo->id, ['order' => $maxOrder + 1]);
        }

        return back()->with('success', 'Foto agregada al evento');
    }

     
    public function removePhoto(Request $request, $id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::findOrFail($id);

        $request->validate([
            'photo_id' => 'required|exists:photos,id',
        ]);

        
        $photo = Photo::where('id', $request->photo_id)
            ->where('photographer_id', $photographer->id)
            ->firstOrFail();

        $event->photos()->detach($photo->id);

        return back()->with('success', 'Foto removida del evento');
    }

    
    public function reorderPhotos(Request $request, $id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::findOrFail($id);

        $request->validate([
            'photo_ids' => 'required|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

    
        $validPhotos = Photo::where('photographer_id', $photographer->id)
            ->whereIn('id', $request->photo_ids)
            ->pluck('id')
            ->toArray();

       
        foreach ($validPhotos as $index => $photoId) {
            $event->photos()->updateExistingPivot($photoId, ['order' => $index]);
        }

        return back()->with('success', 'Orden actualizado');
    }
}
