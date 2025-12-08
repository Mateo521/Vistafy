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
            'region' => 'CABA', // Para probar mapas
        ]);

        // 3. Crear 20 Fotógrafos "Extra" (Tus colegas/competencia)
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
        
        // Crear 5 eventos propios
        $myEvents = Event::factory(5)->create([
            'photographer_id' => $myPhotographer->id,
            'is_active' => true,
            'is_private' => false,
        ]);

        foreach ($myEvents as $event) {
            // Subir 10 fotos MÍAS
            Photo::factory(10)->create([
                'event_id' => $event->id,
                'photographer_id' => $myPhotographer->id,
            ]);

            // Invitar a 2 fotógrafos random a colaborar en MI evento
            $collaborators = $otherPhotographers->random(2);
            
            foreach ($collaborators as $collab) {
                // 1. Crear la relación en la tabla pivote (Permiso)
                $event->collaborators()->attach($collab->id);

                // 2. Subir fotos a nombre de ELLOS en MI evento
                Photo::factory(5)->create([
                    'event_id' => $event->id,
                    'photographer_id' => $collab->id, // <--- Importante para el filtro
                ]);
            }
        }

        // ----------------------------------------------------------------
        // ESCENARIO B: COLABORACIONES (Soy invitado, otros son dueños)
        // ----------------------------------------------------------------

        // Tomar 3 fotógrafos random y hacer que creen un evento cada uno
        $hosts = $otherPhotographers->random(3);

        foreach ($hosts as $host) {
            $event = Event::factory()->create([
                'photographer_id' => $host->id, // El dueño es otro
                'name' => 'Evento de ' . $host->business_name,
                'is_active' => true,
            ]);

            // Me invitan a MÍ como colaborador
            $event->collaborators()->attach($myPhotographer->id);

            // Subo fotos YO a SU evento
            Photo::factory(8)->create([
                'event_id' => $event->id,
                'photographer_id' => $myPhotographer->id,
            ]);

            // El dueño también sube sus fotos
            Photo::factory(8)->create([
                'event_id' => $event->id,
                'photographer_id' => $host->id,
            ]);
        }

        // ----------------------------------------------------------------
        // ESCENARIO C: RELLENO (Eventos random para poblar el sitio)
        // ----------------------------------------------------------------
        
        $otherPhotographers->each(function ($photographer) {
            // Crear eventos normales donde solo ellos suben fotos
            Event::factory(2)
                ->for($photographer) // Dueño
                ->has(
                    Photo::factory()->count(5)->state(['photographer_id' => $photographer->id])
                )
                ->create();
        });

        echo "\n=============================================\n";
        echo " Base de datos poblada con éxito.\n";
        echo "---------------------------------------------\n";
        echo "👤 Admin:     admin@empresa.com / password\n";
        echo " Fotógrafo: foto@empresa.com / password\n";
        echo "---------------------------------------------\n";
        echo "PRUEBAS:\n";
        echo "1. Entra al Dashboard > Mis Eventos: Verás tus 5 eventos.\n";
        echo "   Entra a uno, verás fotos tuyas y de otros.\n";
        echo "2. Entra al Dashboard > Colaboraciones: Verás 3 eventos de otros.\n";
        echo "3. Entra a la Web Pública (/eventos) > Busca tus eventos.\n";
        echo "   Usa el filtro de fotógrafo: Deberían aparecer Tú y los Colaboradores.\n";
        echo "=============================================\n";
    }
}