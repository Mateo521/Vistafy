<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Photo;
use App\Models\Photographer;
use App\Models\User;
use App\Models\FutureEvent;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Usuario ADMIN
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_admin' => true,
        ]);

        // 2. Crear TU Usuario (Fotógrafo Principal)
        $myUser = User::factory()->create([
            'name' => 'Yo Fotógrafo',
            'email' => 'foto@empresa.com',
            'password' => bcrypt('password'),
            'role' => 'photographer',
        ]);

        $myPhotographer = Photographer::factory()->create([
            'user_id' => $myUser->id,
            'business_name' => 'Mi Estudio Pro',
            'status' => 'approved',
            'is_verified' => true,
            'region' => 'CABA',
        ]);

        // 3. Crear 20 Fotógrafos "Extra"
        $otherPhotographers = User::factory(20)
            ->create(['role' => 'photographer'])
            ->map(function ($user) {
                return Photographer::factory()->create([
                    'user_id' => $user->id,
                    'status' => 'approved',
                ]);
            });

        // ----------------------------------------------------------------
        // ESCENARIO A: MIS EVENTOS (Soy dueño, otros colaboran)
        // ----------------------------------------------------------------

        $myEvents = Event::factory(5)->create([
            'photographer_id' => $myPhotographer->id,
            'is_active' => true,
            'is_private' => false,
        ]);

        foreach ($myEvents as $event) {
            Photo::factory(10)->create([
                'event_id' => $event->id,
                'photographer_id' => $myPhotographer->id,
            ]);

            $collaborators = $otherPhotographers->random(2);

            foreach ($collaborators as $collab) {
                $event->collaborators()->attach($collab->id);

                Photo::factory(5)->create([
                    'event_id' => $event->id,
                    'photographer_id' => $collab->id,
                ]);
            }
        }

        // ----------------------------------------------------------------
        // ESCENARIO B: COLABORACIONES (Soy invitado, otros son dueños)
        // ----------------------------------------------------------------

        $hosts = $otherPhotographers->random(3);

        foreach ($hosts as $host) {
            $event = Event::factory()->create([
                'photographer_id' => $host->id,
                'name' => 'Evento de ' . $host->business_name,
                'is_active' => true,
            ]);

            $event->collaborators()->attach($myPhotographer->id);

            Photo::factory(8)->create([
                'event_id' => $event->id,
                'photographer_id' => $myPhotographer->id,
            ]);

            Photo::factory(8)->create([
                'event_id' => $event->id,
                'photographer_id' => $host->id,
            ]);
        }

        // ----------------------------------------------------------------
        // ESCENARIO C: RELLENO (Eventos random)
        // ----------------------------------------------------------------

        $otherPhotographers->each(function ($photographer) {
            Event::factory(2)
                ->for($photographer)
                ->has(
                    Photo::factory()->count(5)->state(['photographer_id' => $photographer->id])
                )
                ->create();
        });

        // ================================================================
        //  NUEVOS EVENTOS FUTUROS (Oportunidades para fotógrafos)
        // ================================================================

        // Mezclar todos los fotógrafos (incluyendo a ti)
        $allPhotographers = $otherPhotographers->push($myPhotographer);

        // 1️ Crear 10 eventos futuros TUYOS (publicaste oportunidades)
        FutureEvent::factory(10)->create([
            'photographer_id' => $myPhotographer->id,
            'status' => 'upcoming',
        ]);

        // 2️ Crear 38 eventos futuros distribuidos entre otros fotógrafos
        // (Para llegar a 48 total: 10 tuyos + 38 de otros = 48)
        $allPhotographers->each(function ($photographer) {
            // Cada fotógrafo crea 1-3 eventos futuros
            $count = rand(1, 3);

            FutureEvent::factory($count)->create([
                'photographer_id' => $photographer->id,
                'status' => 'upcoming',
            ]);
        });

        // 3️ Crear algunos eventos "próximos" (en 3-7 días) para testing
        FutureEvent::factory(5)
            ->soon() // Usa el state 'soon' del factory
            ->create([
                'photographer_id' => $allPhotographers->random()->id,
            ]);

        //  Crear algunos eventos "lejanos" (más de 30 días)
        FutureEvent::factory(5)
            ->distant() // Usa el state 'distant' del factory
            ->create([
                'photographer_id' => $allPhotographers->random()->id,
            ]);

        // ================================================================
        //  RESUMEN EN CONSOLA
        // ================================================================

        $totalEvents = Event::count();
        $totalFutureEvents = FutureEvent::count();
        $totalPhotos = Photo::count();
        $totalPhotographers = Photographer::count();

        echo "\n=============================================\n";
        echo "  Base de datos poblada con éxito.\n";
        echo "---------------------------------------------\n";
        echo " Admin:      admin@empresa.com / password\n";
        echo " Fotógrafo:  foto@empresa.com / password\n";
        echo "---------------------------------------------\n";
        echo " ESTADÍSTICAS:\n";
        echo "   • Fotógrafos: {$totalPhotographers}\n";
        echo "   • Eventos pasados: {$totalEvents}\n";
        echo "   • Eventos futuros: {$totalFutureEvents}\n";
        echo "   • Fotos: {$totalPhotos}\n";
        echo "---------------------------------------------\n";
        echo " PRUEBAS:\n";
        echo "1. Dashboard > Mis Eventos: {$myEvents->count()} eventos\n";
        echo "2. Dashboard > Colaboraciones: 3 eventos\n";
        echo "3. Web Pública > Eventos Futuros: {$totalFutureEvents} oportunidades\n";
        echo "   (Solo 6 visibles para no-fotógrafos)\n";
        echo "=============================================\n";
    }
}
