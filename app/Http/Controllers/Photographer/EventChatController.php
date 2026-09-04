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

        
        $creator = $event->photographer; 
        $collaborators = $event->collaborators()->where('event_photographer.status', 'approved')->get();
        
        $participants = collect([$creator])->merge($collaborators)->map(function ($p) {
            return [
                'id' => $p->id,
                
                'name' => $p->user->name ?? $p->business_name ?? 'Fotógrafo',
                'profile_photo_url' => $p->profile_photo_url,
            ];
        })->unique('id')->values();

        return Inertia::render('Photographer/Events/Chat', [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'location' => $event->location,
                'cover_image' => $event->cover_image_url,
            ],
            'messages' => $messages,
            'participants' => $participants, 
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

        EventMessage::create([
            'event_id' => $event->id,
            'photographer_id' => $photographer->id,
            'message' => $request->message
        ]);


        if ($event->messages()->count() === 1) {
            
            $creator = $event->photographer; 
            $collaborators = $event->collaborators()->where('event_photographer.status', 'approved')->get();
            

            $recipients = collect([$creator])
                ->merge($collaborators)
                ->unique('id')
                ->reject(function ($p) use ($photographer) {
                    return $p->id === $photographer->id;
                });


            foreach ($recipients as $recipient) {
                if ($recipient->user && $recipient->user->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($recipient->user->email)
                            ->send(new \App\Mail\EventChatStartedMail($event, $photographer));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Error enviando aviso de chat: ' . $e->getMessage());
                    }
                }
            }
        }

        return back(); 
    }
}