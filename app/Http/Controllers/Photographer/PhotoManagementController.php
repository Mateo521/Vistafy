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

    
    public function create()
    {
        return Inertia::render('Photographer/Photos/Create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
        ]);

        $photographer = auth()->user()->photographer;
        
        
        $photo = new Photo();
        $photo->photographer_id = $photographer->id;
        $photo->title = $request->title;
        $photo->description = $request->description;
        $photo->price = $request->price;
        $photo->is_active = true;
        
    
        $photo->original_path = 'temp';
        $photo->watermarked_path = 'temp';
        $photo->save();

        
        $paths =$this->imageService->processPhoto(
            $request->file('photo'),
            $photo->unique_id,
            $photographer->business_name
        );


        $photo->update($paths);

        return redirect()->route('photographer.photos.index')
            ->with('success', 'Foto subida exitosamente');
    }


    public function edit($id)
    {
        $photographer = auth()->user()->photographer;
        
        $photo = Photo::where('photographer_id', $photographer->id)
            ->findOrFail($id);

        return Inertia::render('Photographer/Photos/Edit', [
            'photo' => $photo,
        ]);
    }


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


    public function destroy($id)
    {
        $photographer = auth()->user()->photographer;
        
        $photo = Photo::where('photographer_id', $photographer->id)
            ->findOrFail($id);


        $this->imageService->deletePhotoFiles($photo);


        $photo->delete();

        return redirect()->route('photographer.photos.index')
            ->with('success', 'Foto eliminada exitosamente');
    }
}
