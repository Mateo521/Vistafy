<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventChatController extends Controller
{

    public function index(Event $event)
    {
        $photographer = auth()->user()->photographer;

        $isCreator = $event->photographer_id === $photographer->id;
        $isCollaborator = $event->collaborators()->where('photographer_id', $photographer->id)->where('event_photographer.status', 'approved')->exists();

        if (!$isCreator && !$isCollaborator) {
            abort(403, 'No tienes acceso a la sala de operaciones de este evento.');
        }


        $messages = $event->messages()->with('photographer.user')->oldest()->get();

        return Inertia::render('Photographer/Events/Chat', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'location' => $event->location,
                'cover_image' => $event->cover_image_url, 
            ],
            'messages' => $messages,
            'currentPhotographerId' => $photographer->id
        ]);
    }


public function store(Request $request, Event $event)
    {
        $photographer = auth()->user()->photographer;

        $isCreator = $event->photographer_id === $photographer->id;
        $isCollaborator = $event->collaborators()->where('photographer_id', $photographer->id)->where('event_photographer.status', 'approved')->exists();

        if (!$isCreator && !$isCollaborator) {
            abort(403, 'No tienes permiso para escribir aquí.');
        }


        $request->validate([
            'message' => 'required|string|max:1000'
        ]);


        // dd('Intentando guardar...', $request->message, $photographer->id, $event->id);

        EventMessage::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'message' => $request->message
        ]);

        return back(); 
    }
}