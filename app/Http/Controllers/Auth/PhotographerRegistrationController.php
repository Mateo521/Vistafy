<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Photographer;
use App\Rules\Turnstile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use App\Mail\PhotographerRegisteredMail;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Inertia\Inertia;
use Inertia\Response;

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

   
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'photographer',
        ]);


        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            try {
                $file = $request->file('profile_photo');
                $filename = 'photographers/profiles/' . Str::random(20) . '.jpg';

                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);
                $image->cover(800, 800);
                $encoded = $image->toJpeg(80);

                \Illuminate\Support\Facades\Storage::disk('b2')->put($filename, (string) $encoded);
                $profilePhotoPath = $filename;

            } catch (\Exception $e) {
                
                \Illuminate\Support\Facades\Log::error('Fallo en registro de fotógrafo (Imagen): ' . $e->getMessage(), [
                    'exception' => $e
                ]);

            
                abort(500, 'ERROR EN IMAGEN: ' . $e->getMessage());
            }
        }

       
        Photographer::create([
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'slug' => Str::slug($request->business_name . '-' . $user->id),
            'region' => $request->region,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'profile_photo' => $profilePhotoPath,
            'is_active' => false, 
            'is_verified' => false,
            'status' => 'pending', 
        ]);

       
        Mail::to($user->email)->send(new \App\Mail\PhotographerRegisteredMail($user));

        event(new Registered($user));
        Auth::login($user);

    

        return redirect()->route('photographer.dashboard')->with('info', 'Tu cuenta está pendiente de aprobación por nuestro equipo.');
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
