<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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

        $manager = new ImageManager(new Driver);

        if ($request->hasFile('profile_photo')) {
            if ($photographer->profile_photo) {
                Storage::disk('b2')->delete($photographer->profile_photo);
            }

            $file = $request->file('profile_photo');
            $filename = 'photographers/profiles/'.Str::random(20).'.jpg';

            $image = $manager->read($file);
            $image->cover(800, 800);
            $encoded = $image->toJpeg(80);

            Storage::disk('b2')->put($filename, (string) $encoded);
            $validated['profile_photo'] = $filename;
        } else {
            unset($validated['profile_photo']);
        }

        if ($request->hasFile('banner_photo')) {
            if ($photographer->banner_photo) {
                Storage::disk('b2')->delete($photographer->banner_photo);
            }

            $file = $request->file('banner_photo');
            $filename = 'photographers/banners/'.Str::random(20).'.jpg';

            $image = $manager->read($file);
            $image->cover(1920, 400);
            $encoded = $image->toJpeg(80);

            Storage::disk('b2')->put($filename, (string) $encoded);
            $validated['banner_photo'] = $filename;
        } else {
            unset($validated['banner_photo']);
        }

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
