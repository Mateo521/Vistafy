<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Lista de eventos públicos
     */
    public function index()
    {
        $events = Event::withCount('photos')
            ->where('is_active', true)
            ->where('is_private', false)
            ->latest()
            ->paginate(12);

        return Inertia::render('Events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Ver evento público por slug
     */
    public function show($slug)
    {
        $event = Event::with(['photos' => function($query) {
            $query->where('photos.is_active', true)
                  ->orderBy('event_photo.order');
        }, 'photos.photographer.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('is_private', false)
            ->firstOrFail();

        return Inertia::render('Events/Show', [
            'event' => $event,
        ]);
    }

    /**
     * Ver evento privado por token
     */
    public function showPrivate($token)
    {
        $event = Event::with(['photos' => function($query) {
            $query->where('photos.is_active', true)
                  ->orderBy('event_photo.order');
        }, 'photos.photographer.user'])
            ->where('private_token', $token)
            ->where('is_active', true)
            ->where('is_private', true)
            ->firstOrFail();

        return Inertia::render('Events/ShowPrivate', [
            'event' => $event,
        ]);
    }
}
