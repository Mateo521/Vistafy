<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\Event;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PhotoController extends Controller
{
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
        ->with('events:id,name');

    // Filtros
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('unique_id', 'like', '%' . $request->search . '%')
              ->orWhere('description', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('event_id')) {
        $query->whereHas('events', function($q) use ($request) {
            $q->where('events.id', $request->event_id);
        });
    }

    if ($request->filled('is_active')) {
        $query->where('is_active', $request->is_active);
    }

    // Ordenar
    $sortBy =$request->get('sort_by', 'created_at');
    $sortOrder =$request->get('sort_order', 'desc');
    $query->orderBy($sortBy,$sortOrder);

    // Paginar
    $photos =$query->paginate(24)->withQueryString();

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
        'photos.*' => 'required|image|mimes:jpeg,jpg,png|max:10240', // 10MB
        'event_id' => 'nullable|exists:events,id',
        'price' => 'required|numeric|min:0.01|max:999.99',
        'is_active' => 'boolean',
    ], [
        'photos.required' => 'Debes subir al menos una foto',
        'photos.*.image' => 'Todos los archivos deben ser imágenes',
        'photos.*.mimes' => 'Solo se permiten imágenes JPG, JPEG o PNG',
        'photos.*.max' => 'Cada imagen no debe superar los 10MB',
    ]);

     $photographer = auth()->user()->photographer;
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

                // Procesar imagen
                $processed =$this->imageService->processPhoto($file,$photographer->id);

                \Log::info("✅ Foto procesada exitosamente", [
                    'unique_id' => $processed['unique_id'],
                    'paths' => [
                        'original' => $processed['original_path'],
                        'watermarked' => $processed['watermarked_path'],
                        'thumbnail' => $processed['thumbnail_path'],
                    ]
                ]);

                // Crear registro en base de datos
                $photo = Photo::create([
                    'photographer_id' => $photographer->id,
                    'unique_id' => $processed['unique_id'],
                    'title' => $request->input("titles.{$index}") ?? 'Sin título',
                    'description' => $request->input("descriptions.{$index}"),
                    'original_path' => $processed['original_path'],           // ✅ Sin marca
                    'watermarked_path' => $processed['watermarked_path'],     // ✅ Con marca
                    'thumbnail_path' => $processed['thumbnail_path'],         // ✅ Miniatura
                    'original_name' => $processed['original_name'],
                    'file_size' => $processed['file_size'],
                    'width' => $processed['dimensions']['width'],
                    'height' => $processed['dimensions']['height'],
                    'price' => $request->price,
                    'is_active' => $request->is_active ?? true,
                ]);

                \Log::info("💾 Foto guardada en BD", ['photo_id' => $photo->id]);

                // Asociar con evento si se especificó
                if ($request->event_id) {
                    $photo->events()->attach($request->event_id);
                    \Log::info("🎉 Foto asociada al evento", ['event_id' => $request->event_id]);
                }

                $uploadedPhotos[] = $photo;

            } catch (\Exception $e) {
                \Log::error("❌ Error procesando foto {$index}", [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
                $errors[] = "Error procesando {$file->getClientOriginalName()}: " . $e->getMessage();
            }
        }

        DB::commit();

        \Log::info("🎊 Proceso completado", [
            'uploaded' => count($uploadedPhotos),
            'errors' => count($errors)
        ]);

        $message = count($uploadedPhotos) . ' foto(s) subida(s) exitosamente';
        if (count($errors) > 0) {
            $message .= '. ' . count($errors) . ' foto(s) fallaron.';
        }

        return redirect()->route('photographer.photos.index')->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("💥 Error general en subida", [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
        return redirect()->back()->with('error', 'Error al subir fotos: ' . $e->getMessage());
    }
}


    /**
     * Ver detalles de una foto
     */
    public function show(Photo $photo)
    {
        $this->authorize('view', $photo);

        $photo->load('events:id,name,event_date');

        return Inertia::render('Photographer/Photos/Show', [
            'photo' => $photo,
        ]);
    }

    /**
     * Formulario de edición
     */
    public function edit(Photo $photo)
    {
        $this->authorize('update', $photo);

        $photo->load('events:id,name');

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
        $this->authorize('update', $photo);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0.01|max:999.99',
            'is_active' => 'boolean',
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'exists:events,id',
            'new_image' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
        ]);

        DB::beginTransaction();

        try {
            // Si se sube una nueva imagen, re-procesar
            if ($request->hasFile('new_image')) {
                $processed = $this->imageService->updatePhoto($request->file('new_image'), $photo);

                $photo->update([
                    'file_path' => $processed['original_path'],
                    'preview_path' => $processed['preview_path'],
                    'thumbnail_path' => $processed['thumbnail_path'],
                    'original_name' => $processed['original_name'],
                    'file_size' => $processed['file_size'],
                    'width' => $processed['dimensions']['width'],
                    'height' => $processed['dimensions']['height'],
                ]);
            }

            // Actualizar información
            $photo->update([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'is_active' => $request->is_active ?? $photo->is_active,
            ]);

            // Actualizar eventos asociados
            if ($request->has('event_ids')) {
                $photo->events()->sync($request->event_ids);
            }

            DB::commit();

            return redirect()->route('photographer.photos.show', $photo)->with('success', 'Foto actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar foto
     */
    public function destroy(Photo $photo)
    {
        $this->authorize('delete', $photo);

        try {
            // Eliminar archivos físicos
            $this->imageService->deletePhoto($photo);

            // Eliminar registro
            $photo->delete();

            return redirect()->route('photographer.photos.index')->with('success', 'Foto eliminada exitosamente');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Activar/Desactivar foto
     */
    public function toggleStatus(Photo $photo)
    {
        $this->authorize('update', $photo);

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
