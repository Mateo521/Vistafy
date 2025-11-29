<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
class EventController extends Controller
{
    /**
     * Mostrar lista de eventos del fotógrafo
     */
    public function index(Request $request)
    {
        //  Filtrar SOLO eventos del fotógrafo autenticado
        $query = Event::where('photographer_id', auth()->user()->photographer->id)
            ->withCount('photos');

        // Filtros opcionales
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('event_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_date', '<=', $request->date_to);
        }

        // Ordenar
        $sortBy = $request->get('sort', 'event_date');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginar
        $events = $query->paginate(12)->withQueryString();

        // Estadísticas del fotógrafo
        $stats = [
            'total_events' => Event::where('photographer_id', auth()->user()->photographer->id)->count(),
            'active_events' => Event::where('photographer_id', auth()->user()->photographer->id)
                ->where('is_active', true)->count(),
            'total_photos' => Photo::whereHas('event', function ($q) {
                $q->where('photographer_id', auth()->user()->photographer->id);
            })->count(),
            'total_sales' => 0, // Implementar cuando tengas ventas
        ];

        return Inertia::render('Photographer/Events/Index', [
            'events' => $events,
            'stats' => $stats,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'sort', 'order']),
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

        $validated = $request->validate([
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
            $coverImagePath = $request->file('cover_image')->store('events/covers', 'public');
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

        // ✅ Cargar fotos del evento con paginación
        $photos = $event->photos()
            ->latest()
            ->paginate(24)
            ->withQueryString();

        // Estadísticas del evento
        $stats = [
            'total_photos' => $event->photos()->count(),
            'active_photos' => $event->photos()->where('is_active', true)->count(),
            'total_downloads' => $event->photos()->sum('downloads'),
        ];

        // ✅ SOLUCIÓN: Serializar manualmente para asegurar que todos los campos lleguen
        return Inertia::render('Photographer/Events/Show', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
                'description' => $event->description,
                'long_description' => $event->long_description,
                'event_date' => $event->event_date?->format('Y-m-d'),
                'location' => $event->location,
                'cover_image' => $event->cover_image,
                'cover_image_url' => $event->cover_image_url,
                'is_private' => (bool) $event->is_private,
                'is_active' => (bool) $event->is_active,
                'private_token' => $event->private_token,
                'photographer_id' => $event->photographer_id,
            ],
            'photos' => $photos,
            'stats' => $stats,
        ]);
    }



    /**
     * Mostrar formulario de edición
     */
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
            'cover_image_url' => $event->cover_image_url,  //  Usa el accessor
        ];

        return Inertia::render('Photographer/Events/Edit', [
            'event' => $eventData,
        ]);
    }




    /**
     * Actualizar evento
     */
    public function update(Request $request, Event $event)
    {
        // Verificar permisos
        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tienes permiso para actualizar este evento');
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

        // Subir nueva imagen si existe
        if ($request->hasFile('cover_image')) {
            if ($event->cover_image) {
                \Storage::disk('public')->delete($event->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('events/covers', 'public');
        }

        // Actualizar evento (el modelo se encarga del token automáticamente)
        $event->update($validated);

        return redirect()->route('photographer.events.index')
            ->with('success', 'Evento actualizado exitosamente');
    }


    /**
     * Eliminar evento
     */
    public function destroy(Event $event)
    {
        // Verificar que el evento pertenece al fotógrafo autenticado
        if ($event->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No tienes permiso para eliminar este evento.');
        }

        DB::beginTransaction();

        try {
            // 1. Eliminar la imagen de portada del evento
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }

            // 2. Eliminar todas las fotos asociadas al evento
            foreach ($event->photos as $photo) {
                // Eliminar archivos físicos
                if ($photo->original_path) {
                    Storage::disk('public')->delete($photo->original_path);
                }
                if ($photo->watermarked_path) {
                    Storage::disk('public')->delete($photo->watermarked_path);
                }
                if ($photo->thumbnail_path) {
                    Storage::disk('public')->delete($photo->thumbnail_path);
                }

                // Eliminar registro de la base de datos
                $photo->delete();
            }

            // 3. Eliminar el evento
            $event->delete();

            DB::commit();

            return redirect()->route('photographer.events.index')
                ->with('success', 'Evento y todas sus fotos eliminadas exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar evento: ' . $e->getMessage());

            return back()->with('error', 'Error al eliminar el evento: ' . $e->getMessage());
        }
    }


    /**
     * Actualizar solo la imagen de portada
     */
    public function updateCoverImage(Request $request, Event $event)
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
            $path = $request->file('cover_image')->store('events/covers', 'public');

            $event->update([
                'cover_image' => $path,
            ]);

            return back()->with('success', 'Imagen de portada actualizada correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al subir la imagen: ' . $e->getMessage());
        }
    }
}
