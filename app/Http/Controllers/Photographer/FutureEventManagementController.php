<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\FutureEvent;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FutureEventManagementController extends Controller
{

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


    public function convertToEvent($id)
    {
        $photographer = Auth::user()->photographer;


        $futureEvent = FutureEvent::where('photographer_id', $photographer->id)
            ->with(['collaborators' => function ($q) {
                $q->where('future_event_photographer.status', 'approved');
            }])
            ->findOrFail($id);


        if ($futureEvent->converted_event_id) {
            return redirect()->route('photographer.photos.create', ['event_id' => $futureEvent->converted_event_id])
                ->with('info', 'Este evento ya había sido convertido.');
        }

        DB::beginTransaction();

        try {

            $slug = Str::slug($futureEvent->title) . '-' . Str::random(6);

            $event = Event::create([
                'photographer_id' => $photographer->id,
                'name' => $futureEvent->title,
                'slug' => $slug,
                'description' => Str::limit($futureEvent->description, 500), // 
                'long_description' => $futureEvent->description,
                'event_date' => $futureEvent->event_date,
                'location' => $futureEvent->location,
                'is_private' => false, // Por defecto público
                'cover_image' => clone $futureEvent->cover_image, 
            ]);


            $approvedPhotographerIds = $futureEvent->collaborators->pluck('id')->toArray();
            if (!empty($approvedPhotographerIds)) {
                
                $syncData = array_fill_keys($approvedPhotographerIds, ['status' => 'approved']);
                $event->collaborators()->sync($syncData);
            }


            $futureEvent->update([
                'converted_event_id' => $event->id,
                'status' => 'converted'
            ]);

            DB::commit();


            return redirect()->route('photographer.photos.create', ['event_id' => $event->id])
                ->with('success', 'El evento fue creado y los colaboradores fueron notificados.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error convirtiendo evento futuro: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al convertir el evento.');
        }
    }


    public function create()
    {
        $photographer = Auth::user()->photographer;


        if (! $photographer) {
            return redirect()->route('photographer.dashboard')
                ->with('error', 'No se encontró el perfil de fotógrafo');
        }

        return Inertia::render('Photographer/Opportunities/Create', [
            'photographer' => [
                'id' => $photographer->id,
                'latitude' => $photographer->latitude ?? -38.4161,
                'longitude' => $photographer->longitude ?? -63.6167,
                'region' => $photographer->region ?? 'Argentina',
            ],
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
        ], [

            'event_date.after' => 'La fecha del evento debe ser una fecha posterior a hoy.',
        ]);


        $eventDateTime = Carbon::parse($validated['event_date']);
        if (isset($validated['event_time'])) {
            $time = Carbon::parse($validated['event_time']);
            $eventDateTime->setTime($time->hour, $time->minute);
        }


        $expiryDate = $eventDateTime->copy()->addDays(7);


        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = 'eventos-futuros/'.Str::random(20).'.jpg';

            $manager = new ImageManager(new Driver);
            $image = $manager->read($file);
            $image->cover(1200, 800);
            $encoded = $image->toJpeg(80);

            Storage::disk('b2')->put($filename, (string) $encoded);
            $coverImagePath = $filename;
        }

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


    public function acceptInvitation(\App\Models\Event $event)
    {
        $photographerId = auth()->user()->photographer->id;

        $pivot = $event->collaborators()->where('photographer_id', $photographerId)->first();

        if (!$pivot || !in_array($pivot->pivot->status, ['pending', 'invited'])) {
            return redirect()->back()->with('error', 'No tenés invitaciones pendientes para este evento.');
        }

        $event->collaborators()->updateExistingPivot($photographerId, ['status' => 'approved']);

        return redirect()->back()->with('success', '¡Invitación aceptada! Ya podés subir fotos a este evento.');
    }

    public function rejectInvitation(\App\Models\Event $event)
    {
        $photographerId = auth()->user()->photographer->id;

        $event->collaborators()->detach($photographerId);

        return redirect()->back()->with('success', 'Invitación rechazada.');
    }







    
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
            Storage::disk('b2')->delete($coverImagePath);
            $coverImagePath = null;
        }

        if ($request->hasFile('cover_image')) {

            if ($coverImagePath) {
                Storage::disk('b2')->delete($coverImagePath);
            }

            $file = $request->file('cover_image');
            $filename = 'eventos-futuros/'.Str::random(20).'.jpg';

            $manager = new ImageManager(new Driver);
            $image = $manager->read($file);
            $image->cover(1200, 800);
            $encoded = $image->toJpeg(80);

            Storage::disk('b2')->put($filename, (string) $encoded);
            $coverImagePath = $filename;
        }


        $opportunity->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'latitude' => (float) $validated['latitude'],      //   
            'longitude' => (float) $validated['longitude'],    //   
            'event_date' => $eventDateTime,
            'expiry_date' => $expiryDate,
            'cover_image' => $coverImagePath,
        ]);

        return redirect()->route('photographer.opportunities.index')
            ->with('success', 'Oportunidad actualizada exitosamente');
    }

    
    public function destroy($id)
    {
        $photographer = Auth::user()->photographer;

        $opportunity = FutureEvent::where('photographer_id', $photographer->id)
            ->findOrFail($id);


        if ($opportunity->cover_image) {
            Storage::disk('b2')->delete($opportunity->cover_image);
        }

        $opportunity->delete();

        return redirect()->route('photographer.opportunities.index')
            ->with('success', 'Oportunidad eliminada exitosamente');
    }
}
