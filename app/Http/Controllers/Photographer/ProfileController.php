<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'photographer']);
    }

    /**
     * Mostrar perfil del fotógrafo
     */
    public function show()
    {
        $photographer = auth()->user()->photographer->load('photos');

        return Inertia::render('Photographer/Profile/Show', [
            'photographer' => $photographer,
        ]);
    }

    /**
     * Formulario de edición
     */
    public function edit()
    {
        $photographer = auth()->user()->photographer;

        return Inertia::render('Photographer/Profile/Edit', [
            'photographer' => $photographer,
        ]);
    }

    /**
     * Actualizar perfil
     */
    public function update(Request $request)
    {
        $photographer = auth()->user()->photographer;

        $request->validate([
            'business_name' => 'required|string|max:255',
            'region' => 'required|in:norte,centro,sur',
            'bio' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048',
            'cover_photo' => 'nullable|image|max:5120',
        ]);

        $data =$request->only(['business_name', 'region', 'bio', 'phone']);

        // Manejar foto de perfil
        if ($request->hasFile('profile_photo')) {
            // Eliminar foto anterior
            if ($photographer->profile_photo) {
                Storage::disk('public')->delete($photographer->profile_photo);
            }

            $path =$request->file('profile_photo')->store('photographers/profiles', 'public');
            $data['profile_photo'] = $path;
        }

        // Manejar foto de portada
        if ($request->hasFile('cover_photo')) {
            // Eliminar foto anterior
            if ($photographer->cover_photo) {
                Storage::disk('public')->delete($photographer->cover_photo);
            }

            $path =$request->file('cover_photo')->store('photographers/covers', 'public');
            $data['cover_photo'] = $path;
        }

        $photographer->update($data);

        return redirect()->route('photographer.profile')->with('success', 'Perfil actualizado exitosamente');
    }
}
