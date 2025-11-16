<?php

namespace App\Http\Controllers\Photographer;


use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;        // ← AGREGAR ESTA LÍNEA

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\ImageProcessingService;

class PhotoController extends Controller
{
    use AuthorizesRequests;
    protected $imageService;

    public function __construct(ImageProcessingService $imageService)
    {


        $this->imageService = $imageService;
    }

    /**
     * Lista de fotos del fotógrafo
     */
    /**
     * Mostrar listado de fotos del fotógrafo
     */
    public function index(Request $request)
    {
        $photographer = auth()->user()->photographer;

        $query = Photo::where('photographer_id', $photographer->id)
            ->with('event:id,name');  // ← event (singular)

        // Filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('unique_id', 'like', '%' . $request->search . '%')
                    ->orWhere('original_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);  // ← Cambiar whereHas por where directo
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Ordenar
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginar
        $photos = $query->paginate(24)->withQueryString();

        // Eventos para el filtro
        $events = Event::where('photographer_id', $photographer->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Estadísticas
        $stats = [
            'total' => Photo::where('photographer_id', $photographer->id)->count(),
            'active' => Photo::where('photographer_id', $photographer->id)->where('is_active', true)->count(),
            'inactive' => Photo::where('photographer_id', $photographer->id)->where('is_active', false)->count(),
            'total_downloads' => Photo::where('photographer_id', $photographer->id)->sum('downloads'),
        ];

        return Inertia::render('Photographer/Photos/Index', [
            'photos' => $photos,
            'events' => $events,
            'filters' => $request->only(['search', 'event_id', 'is_active', 'sort_by', 'sort_order']),
            'stats' => $stats,
        ]);
    }



    /**
     * Formulario para subir fotos
     */
    /**
     * Formulario para subir fotos
     */
    public function create()
    {
        $photographer = auth()->user()->photographer;


        $events = Event::where('photographer_id', $photographer->id)
            ->select('id', 'name', 'event_date')
            ->orderBy('event_date', 'desc')
            ->get();

        return Inertia::render('Photographer/Photos/Create', [
            'events' => $events,
        ]);
    }


    /**
     * Subir fotos (múltiples)
     */
    /**
     * Subir fotos (múltiples)
     */
    public function store(Request $request)
    {
        // ✅ LOG PARA VER QUÉ LLEGA
        \Log::info('📸 Inicio de subida de fotos', [
            'has_photos' => $request->hasFile('photos'),
            'photos_count' => $request->hasFile('photos') ? count($request->file('photos')) : 0,
            'event_id' => $request->event_id,
            'price' => $request->price,
            'photographer_id' => auth()->user()->photographer->id ?? 'NO_PHOTOGRAPHER',
        ]);

        // Verificar que el usuario tiene un perfil de fotógrafo
        if (!auth()->user()->photographer) {
            \Log::error('❌ Usuario no tiene perfil de fotógrafo');
            return redirect()->back()->with('error', 'No tienes un perfil de fotógrafo activo');
        }

        $request->validate([
            'photos' => 'required|array|min:1|max:50',
            'photos.*' => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'event_id' => 'nullable|exists:events,id',
            'price' => 'nullable|numeric|min:0.01|max:999999.99',
            'is_active' => 'nullable|boolean',
        ]);

        $photographer = auth()->user()->photographer;

        // Verificar que el evento pertenece al fotógrafo (si se proporciona)
        if ($request->event_id) {
            $event = Event::find($request->event_id);
            if (!$event || $event->photographer_id !== $photographer->id) {
                \Log::error('❌ Evento no pertenece al fotógrafo');
                return redirect()->back()->with('error', 'No tienes permiso para subir fotos a este evento');
            }
        }

        $uploadedPhotos = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($request->file('photos') as $index => $file) {
                try {
                    \Log::info("📤 Procesando foto {$index}", [
                        'filename' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                    ]);

                    // Procesar imagen usando el servicio
                    $processed = $this->imageService->processPhoto($file, $photographer->id);

                    \Log::info("✅ Foto procesada exitosamente", [
                        'unique_id' => $processed['unique_id'],
                        'event_id' => $request->event_id,
                        'original_path' => $processed['original_path'], // ← VERIFICAR QUE EXISTE
                    ]);

                    // ✅ CREAR REGISTRO EN BASE DE DATOS (INCLUIR ORIGINAL_PATH)
                    $photo = Photo::create([
                        'photographer_id' => $photographer->id,
                        'event_id' => $request->event_id,
                        'unique_id' => $processed['unique_id'],
                        'original_path' => $processed['original_path'],         // ← AGREGAR ESTA LÍNEA
                        'watermarked_path' => $processed['watermarked_path'],
                        'thumbnail_path' => $processed['thumbnail_path'],
                        'original_name' => $processed['original_name'],
                        'file_size' => $processed['file_size'],
                        'width' => $processed['dimensions']['width'],
                        'height' => $processed['dimensions']['height'],
                        'price' => $request->price ?? 5000,
                        'is_active' => $request->is_active ?? true,
                    ]);

                    \Log::info("💾 Foto guardada en BD", [
                        'photo_id' => $photo->id,
                        'event_id' => $photo->event_id,
                        'original_path' => $photo->original_path, // ← VERIFICAR QUE SE GUARDÓ
                    ]);

                    $uploadedPhotos[] = $photo;

                } catch (\Exception $e) {
                    \Log::error("❌ Error procesando foto {$index}", [
                        'error' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $errors[] = "Error en {$file->getClientOriginalName()}: " . $e->getMessage();
                }
            }

            DB::commit();

            \Log::info("🎊 Proceso completado", [
                'uploaded' => count($uploadedPhotos),
                'errors' => count($errors),
                'event_id' => $request->event_id,
            ]);

            $message = count($uploadedPhotos) . ' foto(s) subida(s) exitosamente';
            if (count($errors) > 0) {
                $message .= '. ' . count($errors) . ' foto(s) fallaron.';
            }

            // Redirigir según el contexto
            if ($request->event_id) {
                return redirect()
                    ->route('photographer.events.show', $request->event_id)
                    ->with('success', $message);
            }

            return redirect()
                ->route('photographer.photos.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("💥 Error general en subida", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Error al subir fotos: ' . $e->getMessage());
        }
    }




    /**
     * Ver detalles de una foto
     */



    public function show($photoId)
    {
        // Cargar la foto manualmente
        $photo = Photo::findOrFail($photoId);

        $photographer = auth()->user()->photographer;

        // Verificar permisos
        if ($photo->photographer_id != $photographer->id) {
            abort(403, 'No autorizado');
        }

        $photo->load('event:id,name,event_date');

        return Inertia::render('Photographer/Photos/Show', [
            'photo' => $photo,
        ]);
    }



    /**
     * Formulario de edición
     */
    public function edit(Photo $photo)
    {
        if ($photo->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No autorizado');
        }

        $photo->load('event:id,name');  // ← event (singular)

        $events = Event::where('photographer_id', auth()->user()->photographer->id)
            ->select('id', 'name', 'event_date')
            ->orderBy('event_date', 'desc')
            ->get();

        return Inertia::render('Photographer/Photos/Edit', [
            'photo' => $photo,
            'events' => $events,
        ]);
    }



    /**
     * Actualizar foto
     */
    public function update(Request $request, Photo $photo)
    {
        if ($photo->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No autorizado');
        }

        $request->validate([
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'is_active' => 'boolean',
            'event_id' => 'nullable|exists:events,id',  // ← event_id (singular)
        ]);

        DB::beginTransaction();

        try {
            // Si se sube una nueva imagen, re-procesar
            if ($request->hasFile('new_image')) {
                // Eliminar archivos antiguos
                if ($photo->original_path) {
                    Storage::disk('public')->delete($photo->original_path);
                }
                if ($photo->watermarked_path) {
                    Storage::disk('public')->delete($photo->watermarked_path);
                }
                if ($photo->thumbnail_path) {
                    Storage::disk('public')->delete($photo->thumbnail_path);
                }

                $processed = $this->imageService->processPhoto($request->file('new_image'), auth()->user()->photographer->id);

                $photo->update([
                    'original_path' => $processed['original_path'],
                    'watermarked_path' => $processed['watermarked_path'],
                    'thumbnail_path' => $processed['thumbnail_path'],
                    'original_name' => $processed['original_name'],
                    'file_size' => $processed['file_size'],
                    'width' => $processed['dimensions']['width'],
                    'height' => $processed['dimensions']['height'],
                ]);
            }

            // Actualizar información
            $photo->update([
                'price' => $request->price,
                'is_active' => $request->is_active ?? $photo->is_active,
                'event_id' => $request->event_id,  // ← Actualizar event_id
            ]);

            DB::commit();

            return redirect()->route('photographer.photos.show', $photo)->with('success', 'Foto actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar foto: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar foto
     */

    public function destroy(Photo $photo)  // ← Debe ser Photo $photo (el nombre debe coincidir con la ruta)
    {
        $photographer = auth()->user()->photographer;

        // DEBUG: Ver qué valores tiene
        \Log::info('Intentando eliminar foto', [
            'photo_id' => $photo->id,
            'photo_photographer_id' => $photo->photographer_id,
            'auth_photographer_id' => $photographer->id,
        ]);

        // Verificar que la foto pertenece al fotógrafo autenticado
        if ($photo->photographer_id !== $photographer->id) {
            \Log::error('Permiso denegado para eliminar foto', [
                'photo_photographer_id' => $photo->photographer_id,
                'auth_photographer_id' => $photographer->id,
            ]);

            abort(403, 'No tienes permiso para eliminar esta foto.');
        }

        DB::beginTransaction();

        try {
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

            DB::commit();

            \Log::info('Foto eliminada exitosamente', ['photo_id' => $photo->id]);

            return back()->with('success', 'Foto eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar foto: ' . $e->getMessage());

            return back()->with('error', 'Error al eliminar la foto: ' . $e->getMessage());
        }
    }

    /**
     * Activar/Desactivar foto
     */
    public function toggleStatus(Photo $photo)
    {
        if ($photo->photographer_id !== auth()->user()->photographer->id) {
            abort(403, 'No autorizado');
        }
        $photo->update([
            'is_active' => !$photo->is_active,
        ]);

        $status = $photo->is_active ? 'activada' : 'desactivada';

        return redirect()->back()->with('success', "Foto {$status} exitosamente");
    }

    /**
     * Subida masiva con AJAX
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:10240',
            'price' => 'required|numeric',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $photographer = auth()->user()->photographer;

        try {
            $processed = $this->imageService->processPhoto($request->file('photo'), $photographer->id);

            $photo = Photo::create([
                'photographer_id' => $photographer->id,
                'unique_id' => $processed['unique_id'],
                'title' => $request->title ?? 'Sin título',
                'description' => $request->description,
                'file_path' => $processed['original_path'],
                'preview_path' => $processed['preview_path'],
                'thumbnail_path' => $processed['thumbnail_path'],
                'original_name' => $processed['original_name'],
                'file_size' => $processed['file_size'],
                'width' => $processed['dimensions']['width'],
                'height' => $processed['dimensions']['height'],
                'price' => $request->price,
                'is_active' => true,
            ]);

            if ($request->event_id) {
                $photo->events()->attach($request->event_id);
            }

            return response()->json([
                'success' => true,
                'photo' => $photo,
                'message' => 'Foto subida exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
