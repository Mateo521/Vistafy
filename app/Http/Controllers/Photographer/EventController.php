<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Mostrar lista de eventos del fotógrafo
     */
    public function index()
    {
        $photographer = auth()->user()->photographer;

        $events = Event::where('photographer_id', $photographer->id)
            ->withCount('photos')
            ->latest()
            ->paginate(10);

        return Inertia::render('Photographer/Events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return Inertia::render('Photographer/Events/Create');
    }

    /**
     * Guardar nuevo evento
     */
    public function store(Request $request)
    {
        $photographer = auth()->user()->photographer;

        $validated =$request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string|max:2000',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'is_private' => 'boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB
        ]);

        // Generar slug único
        $slug = Str::slug($validated['name']) . '-' . Str::random(6);

        // Subir imagen de portada si existe
        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath =$request->file('cover_image')->store('events/covers', 'public');
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

    /**
     * Mostrar evento específico
     */
    public function show(Event $event)
    {
        // Verificar que el evento pertenece al fotógrafo
        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tienes permiso para ver este evento');
        }

        $event->load(['photos' => function($query) {
            $query->latest()->take(12);
        }]);

        $stats = [
            'total_photos' => $event->photos()->count(),
            'active_photos' => $event->photos()->where('is_active', true)->count(),
            'total_downloads' => $event->photos()->sum('downloads'),
        ];

        return Inertia::render('Photographer/Events/Show', [
            'event' => $event,
            'stats' => $stats,
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Event $event)
    {
        // Verificar que el evento pertenece al fotógrafo
        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tienes permiso para editar este evento');
        }

        return Inertia::render('Photographer/Events/Edit', [
            'event' => $event,
        ]);
    }

    /**
     * Actualizar evento
     */
    public function update(Request $request, Event$event)
    {
        // Verificar que el evento pertenece al fotógrafo
        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tienes permiso para actualizar este evento');
        }

        $validated =$request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string|max:2000',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'is_private' => 'boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // Actualizar slug si cambió el nombre
        if ($validated['name'] !== $event->name) {
            $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);
        }

        // Subir nueva imagen de portada si existe
        if ($request->hasFile('cover_image')) {
            // Eliminar imagen anterior
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('events/covers', 'public');
        }

        $event->update($validated);

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento actualizado exitosamente');
    }

    /**
     * Eliminar evento
     */
    public function destroy(Event $event)
    {
        // Verificar que el evento pertenece al fotógrafo
        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tienes permiso para eliminar este evento');
        }

        // Eliminar imagen de portada
        if ($event->cover_image) {
            Storage::disk('public')->delete($event->cover_image);
        }

        // Eliminar relaciones con fotos (no elimina las fotos, solo la relación)
        $event->photos()->detach();

        $event->delete();

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento eliminado exitosamente');
    }

    /**
     * Actualizar solo la imagen de portada
     */
    public function updateCoverImage(Request $request, Event$event)
    {
        // Verificar que el evento pertenece al fotógrafo
        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tienes permiso para actualizar este evento');
        }

        $request->validate([
            'cover_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            // Eliminar imagen anterior
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }

            // Guardar nueva imagen
            $path =$request->file('cover_image')->store('events/covers', 'public');
            
            $event->update([
                'cover_image' => $path,
            ]);

            return back()->with('success', 'Imagen de portada actualizada correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al subir la imagen: ' . $e->getMessage());
        }
    }
}
