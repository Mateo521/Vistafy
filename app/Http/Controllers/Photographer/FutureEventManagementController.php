<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\FutureEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class FutureEventManagementController extends Controller
{
    /**
     *  Listar oportunidades del fotógrafo
     */
    public function index()
    {
        $photographer = Auth::user()->photographer;

        $opportunities = FutureEvent::where('photographer_id', $photographer->id)
            ->orderBy('event_date', 'asc')
            ->paginate(12)
            ->through(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'location' => $event->location,
                    'event_date' => $event->event_date,
                    'formatted_date' => $event->formatted_date,
                    'days_until' => $event->daysUntil(),
                    'cover_image' => $event->cover_image_url,
                    'status' => $event->status,
                    'created_at' => $event->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('Photographer/Opportunities/Index', [
            'opportunities' => $opportunities,
        ]);
    }

    /**
     *  Formulario para crear oportunidad
     */
    public function create()
    {
        return Inertia::render('Photographer/Opportunities/Create');
    }

    /**
     *  Guardar nueva oportunidad
     */
    public function store(Request $request)
    {
        $photographer = Auth::user()->photographer;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date|after:today',
            'event_time' => 'nullable|date_format:H:i',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB
        ]);

        // Combinar fecha + hora
        $eventDateTime = Carbon::parse($validated['event_date']);
        if (isset($validated['event_time'])) {
            $time = Carbon::parse($validated['event_time']);
            $eventDateTime->setTime($time->hour, $time->minute);
        }

        // Calcular expiry_date (7 días después del evento)
        $expiryDate = $eventDateTime->copy()->addDays(7);

        // Subir imagen
        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('future-events', 'public');
        }

        FutureEvent::create([
            'photographer_id' => $photographer->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'event_date' => $eventDateTime,
            'expiry_date' => $expiryDate,
            'cover_image' => $coverImagePath,
            'status' => 'upcoming',
        ]);

        return redirect()->route('photographer.opportunities.index')
            ->with('success', 'Oportunidad creada exitosamente');
    }

    /**
     *  Formulario para editar oportunidad
     */
    public function edit($id)
    {
        $photographer = Auth::user()->photographer;

        $opportunity = FutureEvent::where('photographer_id', $photographer->id)
            ->findOrFail($id);

        return Inertia::render('Photographer/Opportunities/Edit', [
            'opportunity' => [
                'id' => $opportunity->id,
                'title' => $opportunity->title,
                'description' => $opportunity->description,
                'location' => $opportunity->location,
                'event_date' => $opportunity->event_date->format('Y-m-d'),
                'event_time' => $opportunity->event_date->format('H:i'),
                'cover_image' => $opportunity->cover_image_url, // ← Usar el accessor
                'status' => $opportunity->status,
            ],
        ]);
    }


    /**
     *  Actualizar oportunidad
     */
    public function update(Request $request, $id)
    {
        $photographer = Auth::user()->photographer;

        $opportunity = FutureEvent::where('photographer_id', $photographer->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'nullable|date_format:H:i',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'remove_image' => 'boolean',
        ]);

        // Combinar fecha + hora
        $eventDateTime = Carbon::parse($validated['event_date']);
        if (isset($validated['event_time'])) {
            $time = Carbon::parse($validated['event_time']);
            $eventDateTime->setTime($time->hour, $time->minute);
        }

        $expiryDate = $eventDateTime->copy()->addDays(7);

        // Manejar imagen
        $coverImagePath = $opportunity->cover_image;

        if ($request->boolean('remove_image') && $coverImagePath) {
            Storage::disk('public')->delete($coverImagePath);
            $coverImagePath = null;
        }

        if ($request->hasFile('cover_image')) {
            if ($coverImagePath) {
                Storage::disk('public')->delete($coverImagePath);
            }
            $coverImagePath = $request->file('cover_image')->store('future-events', 'public');
        }

        $opportunity->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'event_date' => $eventDateTime,
            'expiry_date' => $expiryDate,
            'cover_image' => $coverImagePath,
        ]);

        return redirect()->route('photographer.opportunities.index')
            ->with('success', 'Oportunidad actualizada exitosamente');
    }

    /**
     *  Eliminar oportunidad
     */
    public function destroy($id)
    {
        $photographer = Auth::user()->photographer;

        $opportunity = FutureEvent::where('photographer_id', $photographer->id)
            ->findOrFail($id);

        // Eliminar imagen si existe
        if ($opportunity->cover_image) {
            Storage::disk('public')->delete($opportunity->cover_image);
        }

        $opportunity->delete();

        return redirect()->route('photographer.opportunities.index')
            ->with('success', 'Oportunidad eliminada exitosamente');
    }
}
