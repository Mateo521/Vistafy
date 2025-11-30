<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Photographer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class PhotographerRegistrationController extends Controller
{
    /**
     * Mostrar formulario de registro para fotógrafos
     */
    public function create(): Response
    {
        return Inertia::render('Auth/RegisterPhotographer', [
            'regions' => $this->getRegions(),
        ]);
    }

    /**
     * Procesar registro de fotógrafo
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'business_name' => 'required|string|max:255',
            'region' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'profile_photo' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Crear usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'photographer',
        ]);

        // Subir foto de perfil si existe
        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('photographers/profiles', 'public');
        }

        // Crear perfil de fotógrafo (estado: pending)
        Photographer::create([
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'slug' => Str::slug($request->business_name . '-' . $user->id),
            'region' => $request->region,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'profile_photo' => $profilePhotoPath,
            'is_active' => false, // ✅ Inactivo hasta aprobación
            'is_verified' => false,
            'status' => 'pending', // ✅ Pendiente de aprobación
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('photographer.dashboard')->with('info', 'Tu cuenta está pendiente de aprobación por nuestro equipo.');
    }

    /**
     * Regiones disponibles
     */
    private function getRegions(): array
    {
        return [
            'Buenos Aires',
            'CABA',
            'Córdoba',
            'Santa Fe',
            'Mendoza',
            'Tucumán',
            'Salta',
            'Entre Ríos',
            'Neuquén',
            'San Juan',
            'Chaco',
            'Corrientes',
            'Formosa',
            'Jujuy',
            'La Pampa',
            'La Rioja',
            'Misiones',
            'Río Negro',
            'San Luis',
            'Santa Cruz',
            'Santiago del Estero',
            'Tierra del Fuego',
            'Catamarca',
            'Chubut',
        ];
    }
}
