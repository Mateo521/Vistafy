<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        $photographer = auth()->user()->photographer;

        return Inertia::render('Photographer/Profile/Edit', [
            'photographer' => $photographer,
        ]);
    }

public function update(Request $request)
    {
        $photographer = auth()->user()->photographer;

        // 1. AGREGAR LOS CAMPOS NUEVOS A LA VALIDACIÓN
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'region' => 'required|string|max:100',
            
            // --- NUEVOS CAMPOS (Faltaban estos) ---
            'website' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            // --------------------------------------

            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // 2. Manejar foto de perfil (Tu lógica estaba bien, la mantenemos)
        if ($request->hasFile('profile_photo')) {
            if ($photographer->profile_photo) {
                Storage::disk('public')->delete($photographer->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('photographers/profiles', 'public');
        } else {
            // Importante: Eliminar la clave si es null para no borrar la foto existente por error
            unset($validated['profile_photo']);
        }

        // 3. Manejar banner
        if ($request->hasFile('banner_photo')) {
            if ($photographer->banner_photo) {
                Storage::disk('public')->delete($photographer->banner_photo);
            }
            $validated['banner_photo'] = $request->file('banner_photo')->store('photographers/banners', 'public');
        } else {
            unset($validated['banner_photo']);
        }

        // 4. Actualizar
        $photographer->update($validated);

        return redirect()->back()->with('success', '¡Perfil actualizado correctamente!');
    }

    public function deleteProfilePhoto()
    {
        $photographer = auth()->user()->photographer;

        if ($photographer->profile_photo) {
            Storage::disk('public')->delete($photographer->profile_photo);
            $photographer->update(['profile_photo' => null]);
        }

        return redirect()->back()->with('success', 'Foto de perfil eliminada');
    }

    public function deleteBannerPhoto()
    {
        $photographer = auth()->user()->photographer;

        if ($photographer->banner_photo) {
            Storage::disk('public')->delete($photographer->banner_photo);
            $photographer->update(['banner_photo' => null]);
        }

        return redirect()->back()->with('success', 'Banner eliminado');
    }
}
