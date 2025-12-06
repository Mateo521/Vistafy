<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_admin' => true,
        ]);

        // 2. Crear Tu Cuenta de Fotógrafo (Para pruebas manuales)
        $myUser = User::factory()->create([
            'name' => 'Mi Fotografo',
            'email' => 'foto@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'photographer',
        ]);

        $myPhotographer = Photographer::factory()->create([
            'user_id' => $myUser->id,
            'business_name' => 'Mi Estudio Pro',
            'status' => 'approved',
        ]);

        // Crear eventos y fotos para MÍ
        Event::factory(5) // 5 eventos
            ->for($myPhotographer)
            ->has(
                Photo::factory()
                    ->count(15) // 15 fotos por evento
                    ->state(function (array $attributes, Event $event) use ($myPhotographer) {
                        return ['photographer_id' => $myPhotographer->id];
                    })
            )
            ->create();


        // 3. Crear 20 Fotógrafos Aleatorios con datos masivos
        $photographers = User::factory(20)
            ->create(['role' => 'photographer'])
            ->each(function ($user) {
                
                $photographer = Photographer::factory()->create([
                    'user_id' => $user->id,
                    // Algunos rechazados o pendientes para probar filtros admin
                    'status' => fake()->randomElement(['approved', 'approved', 'approved', 'pending', 'suspended']),
                ]);

                // Solo crear eventos si está aprobado
                if ($photographer->status === 'approved') {
                    Event::factory(rand(3, 8)) // Entre 3 y 8 eventos por fotógrafo
                        ->for($photographer)
                        ->has(
                            Photo::factory()
                                ->count(rand(10, 30)) // Entre 10 y 30 fotos por evento
                                ->state(function (array $attributes, Event $event) use ($photographer) {
                                    return ['photographer_id' => $photographer->id];
                                })
                        )
                        ->create();
                }
            });

        echo "Base de datos poblada con éxito.\n";
        echo "Admin: admin@empresa.com / password\n";
        echo "Fotógrafo: foto@empresa.com / password\n";
    }
}