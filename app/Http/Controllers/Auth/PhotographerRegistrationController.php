<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PhotographerRegisteredMail;
use App\Models\Photographer;
use App\Models\User;
use App\Rules\Turnstile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PhotographerRegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/RegisterPhotographer', [
            'regions' => $this->getRegions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => mb_strtolower(trim((string) $request->input('email'))),
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'business_name' => 'required|string|max:255',
            'region' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'turnstile_response' => ['required', new Turnstile()],
        ]);

        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            try {
                $file = $request->file('profile_photo');
                $filename = 'photographers/profiles/'.Str::random(20).'.jpg';

                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);
                $image->cover(800, 800);
                $encoded = $image->toJpeg(80);

                Storage::disk('b2')->put($filename, (string) $encoded);
                $profilePhotoPath = $filename;
            } catch (\Exception $e) {
                Log::error('Fallo en registro de fotógrafo (Imagen): '.$e->getMessage(), [
                    'exception' => $e,
                ]);

                return back()->withErrors([
                    'profile_photo' => 'No se pudo procesar la foto de perfil. Intentá con otra imagen.',
                ])->withInput();
            }
        }

        $user = DB::transaction(function () use ($request, $profilePhotoPath) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'photographer',
            ]);

            Photographer::create([
                'user_id' => $user->id,
                'business_name' => $request->business_name,
                'slug' => Str::slug($request->business_name.'-'.$user->id),
                'region' => $request->region,
                'phone' => $request->phone,
                'bio' => $request->bio,
                'profile_photo' => $profilePhotoPath,
                'is_active' => false,
                'is_verified' => false,
                'status' => 'pending',
            ]);

            return $user;
        });

        try {
            Mail::to($user->email)->send(new PhotographerRegisteredMail($user));
        } catch (\Exception $e) {
            Log::error('Error enviando correo a fotógrafo nuevo: '.$e->getMessage());
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('photographer.dashboard')
            ->with('info', 'Tu cuenta está pendiente de aprobación por nuestro equipo.');
    }

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