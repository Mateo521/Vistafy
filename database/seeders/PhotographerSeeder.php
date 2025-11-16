<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Photographer;
use Illuminate\Support\Facades\Hash;

class PhotographerSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario fotógrafo
        $user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'fotografo@test.com',
            'password' => Hash::make('password'),
            'role' => 'photographer',
            'email_verified_at' => now(),
        ]);

        // Crear perfil de fotógrafo
        Photographer::create([
            'user_id' => $user->id,
            'business_name' => 'Fotografía Profesional JP',
            'region' => 'norte',
            'bio' => 'Fotógrafo profesional con 10 años de experiencia',
            'phone' => '+1234567890',
            'is_active' => true,
        ]);

        // Crear usuario cliente
        User::create([
            'name' => 'María González',
            'email' => 'cliente@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
    }
}
