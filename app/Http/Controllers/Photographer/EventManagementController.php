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

    /**
     * Lista de eventos del fotógrafo
     */
    public function index()
    {
        $photographer = auth()->user()->photographer;

        // Eventos donde el fotógrafo tiene al menos una foto
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

    /**
     * Formulario para crear evento
     */
    public function create()
    {
        $photographer = auth()->user()->photographer;

        // Obtener fotos del fotógrafo disponibles
        $availablePhotos = Photo::where('photographer_id', $photographer->id)
            ->where('is_active', true)
            ->latest()
            ->get();

        return Inertia::render('Photographer/Events/Create', [
            'availablePhotos' => $availablePhotos,
        ]);
    }

    /**
     * Guardar nuevo evento
     */
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

        // Crear evento
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

        // Asociar fotos si se proporcionaron
        if ($request->has('photo_ids') && !empty($request->photo_ids)) {
            $photographer = auth()->user()->photographer;

            // Verificar que todas las fotos pertenezcan al fotógrafo
            $validPhotos = Photo::where('photographer_id', $photographer->id)
                ->whereIn('id', $request->photo_ids)
                ->pluck('id')
                ->toArray();

            // Adjuntar fotos con orden
            foreach ($validPhotos as $index => $photoId) {
                $event->photos()->attach($photoId, ['order' => $index]);
            }
        }

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento creado exitosamente');
    }

    /**
     * Ver detalles del evento con gestión de fotos
     */
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

        // Fotos disponibles para agregar al evento
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

    /**
     * Formulario para editar evento
     */
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

        //  Formatear datos para el formulario
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

        // Fotos disponibles
        $availablePhotos = Photo::where('photographer_id', $photographer->id)
            ->where('is_active', true)
            ->latest()
            ->get();

        return Inertia::render('Photographer/Events/Edit', [
            'event' => $eventData,
            'availablePhotos' => $availablePhotos,
        ]);
    }

    /**
     * Actualizar evento
     */
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

        // Actualizar datos del evento
        $event->update([
            'name' => $request->name,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'location' => $request->location,
            'is_private' => $request->is_private ?? $event->is_private,
            'is_active' => $request->is_active ?? $event->is_active,
        ]);

        // Si cambió a privado y no tiene token, generar uno
        if ($request->is_private && !$event->private_token) {
            $event->update(['private_token' => Str::random(32)]);
        }

        // Actualizar fotos si se enviaron
        if ($request->has('photo_ids')) {
            // Verificar que todas las fotos pertenezcan al fotógrafo
            $validPhotos = Photo::where('photographer_id', $photographer->id)
                ->whereIn('id', $request->photo_ids)
                ->pluck('id')
                ->toArray();

            // Sincronizar fotos con orden
            $syncData = [];
            foreach ($validPhotos as $index => $photoId) {
                $syncData[$photoId] = ['order' => $index];
            }

            $event->photos()->sync($syncData);
        }

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento actualizado exitosamente');
    }

    /**
     * Eliminar evento
     */
    public function destroy($id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::whereHas('photos', function ($query) use ($photographer) {
            $query->where('photographer_id', $photographer->id);
        })->findOrFail($id);

        // Solo desasociar las fotos del fotógrafo, no eliminar el evento si tiene fotos de otros
        $event->photos()->wherePivot('photographer_id', $photographer->id)->detach();

        // Si el evento ya no tiene fotos, eliminarlo
        if ($event->photos()->count() == 0) {
            $event->delete();
        }

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento eliminado exitosamente');
    }

    /**
     * Agregar foto a evento existente
     */
    public function addPhoto(Request $request, $id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::findOrFail($id);

        $request->validate([
            'photo_id' => 'required|exists:photos,id',
        ]);

        // Verificar que la foto pertenezca al fotógrafo
        $photo = Photo::where('id', $request->photo_id)
            ->where('photographer_id', $photographer->id)
            ->firstOrFail();

        // Verificar que no esté ya en el evento
        if (!$event->photos()->where('photo_id', $photo->id)->exists()) {
            $maxOrder = $event->photos()->max('order') ?? -1;
            $event->photos()->attach($photo->id, ['order' => $maxOrder + 1]);
        }

        return back()->with('success', 'Foto agregada al evento');
    }

    /**
     * Remover foto de evento
     */
    public function removePhoto(Request $request, $id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::findOrFail($id);

        $request->validate([
            'photo_id' => 'required|exists:photos,id',
        ]);

        // Verificar que la foto pertenezca al fotógrafo
        $photo = Photo::where('id', $request->photo_id)
            ->where('photographer_id', $photographer->id)
            ->firstOrFail();

        $event->photos()->detach($photo->id);

        return back()->with('success', 'Foto removida del evento');
    }

    /**
     * Reordenar fotos en evento
     */
    public function reorderPhotos(Request $request, $id)
    {
        $photographer = auth()->user()->photographer;

        $event = Event::findOrFail($id);

        $request->validate([
            'photo_ids' => 'required|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        // Verificar que todas las fotos pertenezcan al fotógrafo
        $validPhotos = Photo::where('photographer_id', $photographer->id)
            ->whereIn('id', $request->photo_ids)
            ->pluck('id')
            ->toArray();

        // Actualizar el orden
        foreach ($validPhotos as $index => $photoId) {
            $event->photos()->updateExistingPivot($photoId, ['order' => $index]);
        }

        return back()->with('success', 'Orden actualizado');
    }
}
