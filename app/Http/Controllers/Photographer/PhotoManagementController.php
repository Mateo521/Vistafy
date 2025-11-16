<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhotoManagementController extends Controller
{
    protected $imageService;

    public function __construct(ImageProcessingService $imageService)
    {
        $this->middleware(['auth', 'photographer']);
        $this->imageService = $imageService;
    }

    /**
     * Dashboard del fotógrafo - lista de fotos
     */
    public function index()
    {
        $photographer = auth()->user()->photographer;

        $photos = Photo::where('photographer_id', $photographer->id)
            ->withCount('events')
            ->latest()
            ->paginate(20);

        return Inertia::render('Photographer/Photos/Index', [
            'photos' => $photos,
        ]);
    }

    /**
     * Formulario para subir nueva foto
     */
    public function create()
    {
        return Inertia::render('Photographer/Photos/Create');
    }

    /**
     * Guardar nueva foto
     */
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
        ]);

        $photographer = auth()->user()->photographer;
        
        // Crear registro de foto primero para obtener ID
        $photo = new Photo();
        $photo->photographer_id = $photographer->id;
        $photo->title = $request->title;
        $photo->description = $request->description;
        $photo->price = $request->price;
        $photo->is_active = true;
        
        // Temporal, se actualizará después del procesamiento
        $photo->original_path = 'temp';
        $photo->watermarked_path = 'temp';
        $photo->save();

        // Procesar imagen (marca de agua, thumbnail, etc.)
        $paths =$this->imageService->processPhoto(
            $request->file('photo'),
            $photo->unique_id,
            $photographer->business_name
        );

        // Actualizar con los paths reales
        $photo->update($paths);

        return redirect()->route('photographer.photos.index')
            ->with('success', 'Foto subida exitosamente');
    }

    /**
     * Formulario para editar foto
     */
    public function edit($id)
    {
        $photographer = auth()->user()->photographer;
        
        $photo = Photo::where('photographer_id', $photographer->id)
            ->findOrFail($id);

        return Inertia::render('Photographer/Photos/Edit', [
            'photo' => $photo,
        ]);
    }

    /**
     * Actualizar foto (solo datos, no imagen)
     */
    public function update(Request $request,$id)
    {
        $photographer = auth()->user()->photographer;
        
        $photo = Photo::where('photographer_id', $photographer->id)
            ->findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $photo->update($request->only(['title', 'description', 'price', 'is_active']));

        return redirect()->route('photographer.photos.index')
            ->with('success', 'Foto actualizada exitosamente');
    }

    /**
     * Eliminar foto
     */
    public function destroy($id)
    {
        $photographer = auth()->user()->photographer;
        
        $photo = Photo::where('photographer_id', $photographer->id)
            ->findOrFail($id);

        // Eliminar archivos físicos
        $this->imageService->deletePhotoFiles($photo);

        // Soft delete
        $photo->delete();

        return redirect()->route('photographer.photos.index')
            ->with('success', 'Foto eliminada exitosamente');
    }
}
