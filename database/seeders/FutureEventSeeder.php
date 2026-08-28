<?php

namespace Database\Seeders;

use App\Models\FutureEvent;
use App\Models\Photographer;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FutureEventSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔮 Creando eventos futuros con coordenadas...');

        $photographers = Photographer::where('status', 'approved')->get();

        if ($photographers->isEmpty()) {
            $this->command->warn('  No hay fotógrafos aprobados. Creando fotógrafos de prueba...');
            $photographers = Photographer::factory(5)->create(['status' => 'approved']);
        }

        
        $specificEvents = [
            [
                'title' => 'Tech Summit Argentina 2025',
                'description' => 'La mayor conferencia de tecnología del año. Miles de asistentes, startups e inversores.',
                'location' => 'Centro Costa Salguero, Buenos Aires',
                'latitude' => -34.5633,
                'longitude' => -58.3816,
                'event_date' => Carbon::now()->addDays(15)->setTime(9, 0),
            ],
            [
                'title' => 'Festival Lollapalooza Argentina',
                'description' => 'Tres días de música con artistas internacionales.',
                'location' => 'Hipódromo de San Isidro',
                'latitude' => -34.4682,
                'longitude' => -58.5135,
                'event_date' => Carbon::now()->addDays(30)->setTime(14, 0),
            ],
            [
                'title' => 'Feria del Libro Buenos Aires',
                'description' => 'La feria del libro más grande de Latinoamérica.',
                'location' => 'La Rural, Palermo',
                'latitude' => -34.5831,
                'longitude' => -58.4353,
                'event_date' => Carbon::now()->addDays(45)->setTime(11, 0),
            ],
            [
                'title' => 'Maratón de Buenos Aires',
                'description' => '42K por las calles más emblemáticas.',
                'location' => 'Obelisco (Largada)',
                'latitude' => -34.6037,
                'longitude' => -58.3816,
                'event_date' => Carbon::now()->addDays(60)->setTime(7, 0),
            ],
            [
                'title' => 'Comic Con Argentina',
                'description' => 'Convención de comics, videojuegos y cultura pop.',
                'location' => 'Estadio Obras Sanitarias',
                'latitude' => -34.5645,
                'longitude' => -58.4283,
                'event_date' => Carbon::now()->addDays(25)->setTime(10, 0),
            ],
            [
                'title' => 'Fashion Week Buenos Aires',
                'description' => 'Pasarela con diseñadores nacionales e internacionales.',
                'location' => 'La Usina del Arte, La Boca',
                'latitude' => -34.6345,
                'longitude' => -58.3623,
                'event_date' => Carbon::now()->addDays(35)->setTime(19, 30),
            ],
            [
                'title' => 'Fiesta Nacional de la Vendimia',
                'description' => 'El evento más importante de Mendoza.',
                'location' => 'Teatro Griego, Mendoza',
                'latitude' => -32.8908,
                'longitude' => -68.8272,
                'event_date' => Carbon::now()->addDays(50)->setTime(21, 0),
            ],
            [
                'title' => 'Festival Nacional del Folklore Cosquín',
                'description' => 'Nueve noches de folklore argentino.',
                'location' => 'Cosquín, Córdoba',
                'latitude' => -31.2464,
                'longitude' => -64.4656,
                'event_date' => Carbon::now()->addDays(55)->setTime(20, 0),
            ],
            [
                'title' => 'Ultra Music Festival Argentina',
                'description' => 'Festival de música electrónica.',
                'location' => 'Costanera Norte',
                'latitude' => -34.5547,
                'longitude' => -58.4137,
                'event_date' => Carbon::now()->addDays(40)->setTime(16, 0),
            ],
            [
                'title' => 'Festival de Tango Buenos Aires',
                'description' => 'Dos semanas de tango y milongas.',
                'location' => 'Usina del Arte',
                'latitude' => -34.6345,
                'longitude' => -58.3623,
                'event_date' => Carbon::now()->addDays(70)->setTime(18, 0),
            ],
        ];

        foreach ($specificEvents as $eventData) {
            FutureEvent::create(array_merge($eventData, [
                'photographer_id' => $photographers->random()->id,
                'status' => 'upcoming',
                'expiry_date' => Carbon::parse($eventData['event_date'])->addDays(7),
            ]));
        }

        $total = FutureEvent::count();
        
        $this->command->info(" {$total} eventos futuros creados con coordenadas");
        $this->command->info('  Todos los eventos tienen ubicación real en el mapa');
    }
}
