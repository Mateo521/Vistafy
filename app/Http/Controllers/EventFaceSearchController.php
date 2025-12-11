<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventFaceSearchController extends Controller
{
    /**
     * Mostrar página de búsqueda facial
     */
    public function index(Event $event)  
    {
        return Inertia::render('Events/FaceSearch', [
            'event' => $event,
        ]);
    }

    /**
     * Buscar fotos por descriptor facial
     */
    public function search(Request $request, Event $event)
    {
        $request->validate([
            'face_descriptor' => 'required|array|size:128',
            'face_descriptor.*' => 'required|numeric',
            'threshold' => 'nullable|numeric|min:0|max:1',
        ]);

        $searchDescriptor = $request->face_descriptor;
        $threshold = $request->threshold ?? 0.6;

        // Obtener fotos con rostros detectados
        $photos = Photo::where('event_id', $event->id)
            ->where('has_faces', true)
            ->whereNotNull('face_encodings')
            ->where('is_active', true)
            ->get();

        $results = [];

        foreach ($photos as $photo) {
            if (!$photo->face_encodings || !is_array($photo->face_encodings)) {
                continue;
            }

            // Comparar con cada rostro en la foto
            foreach ($photo->face_encodings as $photoDescriptor) {
                if (!is_array($photoDescriptor) || count($photoDescriptor) !== 128) {
                    continue;
                }

                $distance = $this->euclideanDistance($searchDescriptor, $photoDescriptor);

                if ($distance < $threshold) {
                    $results[] = [
                        'id' => $photo->id,
                        'unique_id' => $photo->unique_id,
                        'original_name' => $photo->original_name,
                        'thumbnail_url' => $photo->thumbnail_url,
                        'watermarked_url' => $photo->watermarked_url,
                        'distance' => round($distance, 4),
                        'similarity' => round(max(0, 1 - $distance), 4),
                    ];
                    break;
                }
            }
        }

        // Ordenar por similitud
        usort($results, fn($a, $b) => $a['distance'] <=> $b['distance']);

        return response()->json([
            'success' => true,
            'count' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * Calcular distancia euclidiana
     */
    private function euclideanDistance(array $vec1, array $vec2): float
    {
        if (count($vec1) !== count($vec2)) {
            throw new \InvalidArgumentException('Los vectores deben tener la misma longitud');
        }

        $sum = 0.0;
        for ($i = 0; $i < count($vec1); $i++) {
            $sum += pow($vec1[$i] - $vec2[$i], 2);
        }

        return sqrt($sum);
    }
}
