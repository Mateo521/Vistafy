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
                    'latitude' => $event->latitude,          // 
                    'longitude' => $event->longitude,        // 
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
     *  Formulario para crear oportunidad (CON DATOS DEL FOTÓGRAFO)
     */
    public function create()
    {
        $photographer = Auth::user()->photographer;

        //  Verificar que el fotógrafo existe
        if (!$photographer) {
            return redirect()->route('photographer.dashboard')
                ->with('error', 'No se encontró el perfil de fotógrafo');
        }

        return Inertia::render('Photographer/Opportunities/Create', [
            'photographer' => [
                'id' => $photographer->id,
                'latitude' => $photographer->latitude ?? -38.4161,
                'longitude' => $photographer->longitude ?? -63.6167,
                'region' => $photographer->region ?? 'Argentina',
            ]
        ]);
    }






    public function store(Request $request)
    {
        $photographer = Auth::user()->photographer;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'event_date' => 'required|date|after:today',
            'event_time' => 'nullable|date_format:H:i',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
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
            $coverImagePath = $request->file('cover_image')->store('eventos-futuros', 'public');
        }

        //  CONVERSIÓN EXPLÍCITA A FLOAT
        FutureEvent::create([
            'photographer_id' => $photographer->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'latitude' => (float) $validated['latitude'],      //  FORZAR FLOAT
            'longitude' => (float) $validated['longitude'],    //  FORZAR FLOAT
            'event_date' => $eventDateTime,
            'expiry_date' => $expiryDate,
            'cover_image' => $coverImagePath,
            'status' => 'upcoming',
        ]);

        return redirect()->route('photographer.opportunities.index')
            ->with('success', 'Oportunidad creada exitosamente');
    }





    /**
     *  Formulario para editar oportunidad (CON COORDENADAS)
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
                'latitude' => $opportunity->latitude,            // 
                'longitude' => $opportunity->longitude,          // 
                'event_date' => $opportunity->event_date->format('Y-m-d'),
                'event_time' => $opportunity->event_date->format('H:i'),
                'cover_image' => $opportunity->cover_image_url,
                'status' => $opportunity->status,
            ],
        ]);
    }

    /**
     *  Actualizar oportunidad (CON COORDENADAS)
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
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'event_date' => 'required|date',
            'event_time' => 'nullable|date_format:H:i',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'remove_image' => 'boolean',
        ]);

        $eventDateTime = Carbon::parse($validated['event_date']);
        if (isset($validated['event_time'])) {
            $time = Carbon::parse($validated['event_time']);
            $eventDateTime->setTime($time->hour, $time->minute);
        }

        $expiryDate = $eventDateTime->copy()->addDays(7);

        $coverImagePath = $opportunity->cover_image;

        if ($request->boolean('remove_image') && $coverImagePath) {
            Storage::disk('public')->delete($coverImagePath);
            $coverImagePath = null;
        }

        if ($request->hasFile('cover_image')) {
            if ($coverImagePath) {
                Storage::disk('public')->delete($coverImagePath);
            }
            $coverImagePath = $request->file('cover_image')->store('eventos-futuros', 'public');
        }

        //  CONVERSIÓN EXPLÍCITA A FLOAT
        $opportunity->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'latitude' => (float) $validated['latitude'],      //  FORZAR FLOAT
            'longitude' => (float) $validated['longitude'],    //  FORZAR FLOAT
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
