<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Photographer;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $photographers = Photographer::all();

        if ($photographers->isEmpty()) {
            $this->command->warn('No hay fotógrafos. Ejecuta PhotographerSeeder primero.');
            return;
        }

        $eventTypes = [
            'Boda',
            'XV Años',
            'Graduación',
            'Bautizo',
            'Primera Comunión',
            'Cumpleaños',
            'Evento Corporativo',
            'Sesión de Fotos',
        ];

        foreach ($photographers as $photographer) {
            for ($i = 0; $i < rand(2, 5); $i++) {
                Event::create([
                    'photographer_id' => $photographer->id,
                    'name' => $eventTypes[array_rand($eventTypes)] . ' ' . fake()->firstName(),
                    'description' => fake()->sentence(10),
                    'event_date' => fake()->dateTimeBetween('-6 months', '+3 months'),
                    'location' => fake()->city() . ', ' . fake()->state(),
                    'is_private' => fake()->boolean(30), // 30% privados
                    'access_code' => fake()->boolean(30) ? strtoupper(fake()->bothify('####??')) : null,
                ]);
            }
        }

        $this->command->info('Eventos creados exitosamente.');
    }
}
