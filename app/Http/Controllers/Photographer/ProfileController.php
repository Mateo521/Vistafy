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

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'region' => 'required|string|max:100',
            'website' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // 1. Manejo de Foto de Perfil (Guardando en B2)
        if ($request->hasFile('profile_photo')) {
            // Borramos la vieja de la nube (si existe)
            if ($photographer->profile_photo) {
                Storage::disk('b2')->delete($photographer->profile_photo);
            }
            // Guardamos la nueva en la nube
            $validated['profile_photo'] = $request->file('profile_photo')->store('photographers/profiles', 'b2');
        } else {
            unset($validated['profile_photo']);
        }

        // 2. Manejo del Banner (Guardando en B2)
        if ($request->hasFile('banner_photo')) {
            if ($photographer->banner_photo) {
                Storage::disk('b2')->delete($photographer->banner_photo);
            }
            $validated['banner_photo'] = $request->file('banner_photo')->store('photographers/banners', 'b2');
        } else {
            unset($validated['banner_photo']);
        }

        // 3. Actualizar base de datos
        $photographer->update($validated);

        return redirect()->back()->with('success', '¡Perfil actualizado correctamente!');
    }

    public function deleteProfilePhoto()
    {
        $photographer = auth()->user()->photographer;

        if ($photographer->profile_photo) {
            Storage::disk('b2')->delete($photographer->profile_photo);
            $photographer->update(['profile_photo' => null]);
        }

        return redirect()->back()->with('success', 'Foto de perfil eliminada');
    }

    public function deleteBannerPhoto()
    {
        $photographer = auth()->user()->photographer;

        if ($photographer->banner_photo) {
            Storage::disk('b2')->delete($photographer->banner_photo);
            $photographer->update(['banner_photo' => null]);
        }

        return redirect()->back()->with('success', 'Banner eliminado');
    }
}