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
            'phone' => 'nullable|string|max:20',
            'region' => 'required|string|max:100',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Manejar foto de perfil
        if ($request->hasFile('profile_photo')) {
            // Eliminar foto anterior
            if ($photographer->profile_photo) {
                Storage::disk('public')->delete($photographer->profile_photo);
            }

            $path = $request->file('profile_photo')->store('photographers/profiles', 'public');
            $validated['profile_photo'] = $path;
        }

        // Manejar banner
        if ($request->hasFile('banner_photo')) {
            // Eliminar banner anterior
            if ($photographer->banner_photo) {
                Storage::disk('public')->delete($photographer->banner_photo);
            }

            $path = $request->file('banner_photo')->store('photographers/banners', 'public');
            $validated['banner_photo'] = $path;
        }

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
