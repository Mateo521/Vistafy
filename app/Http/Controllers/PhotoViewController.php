<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Inertia\Inertia;
use Inertia\Response;

class PhotoViewController extends Controller
{
    /**
     * Mostrar la página de visualización de una foto
     */
    public function show($photographer, $year, $month, $day, $type, $filename): Response
    {
       
        $constructedPath = "photos/{$photographer}/{$year}/{$month}/{$day}/{$type}/{$filename}";

   
        $column = $type === 'thumbnails' ? 'thumbnail_path' : 'watermarked_path';

        $photo = Photo::where('photographer_id', $photographer)
            ->where('is_active', true)
            ->where($column, $constructedPath) 
            ->with(['event', 'photographer.user'])
            ->firstOrFail(); 

        return Inertia::render('Photos/View', [
            'photo' => [
                'id' => $photo->id,
                'unique_id' => $photo->unique_id,
                'title' => $photo->title ?? $photo->original_name,
                'description' => $photo->description,
                'watermarked_url' => $photo->watermarked_url,  
                'thumbnail_url' => $photo->thumbnail_url,
                'width' => $photo->width,
                'height' => $photo->height,
                'price' => $photo->price ?? 2500,
                'downloads' => $photo->downloads,
                'event' => $photo->event ? [
                    'id' => $photo->event->id,
                    'name' => $photo->event->name,
                    'slug' => $photo->event->slug,
                ] : null,
                'photographer' => [
                    'id' => $photo->photographer->id,
                    'business_name' => $photo->photographer->business_name,
                    'slug' => $photo->photographer->slug ?? $photo->photographer->id,
                    'profile_photo_url' => $photo->photographer->profile_photo_url ?? null,
                ],
            ],
        ]);
    }
}