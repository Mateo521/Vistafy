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
        $this->command->info('🔮 Creando eventos futuros...');

        //  Obtener fotógrafos existentes
        $photographers = Photographer::where('status', 'approved')->get();

        if ($photographers->isEmpty()) {
            $this->command->warn('⚠️  No hay fotógrafos aprobados. Creando fotógrafos de prueba...');
            $photographers = Photographer::factory(5)->create(['status' => 'approved']);
        }

        // ============================================
        // Eventos Específicos Manuales
        // ============================================
        
        $specificEvents = [
            [
                'title' => 'Conferencia Tech Summit 2025',
                'description' => 'La mayor conferencia de tecnología del año. Miles de asistentes esperados, startups, inversores y líderes tech.',
                'location' => 'Centro de Convenciones, Buenos Aires',
                'event_date' => Carbon::now()->addDays(15)->setTime(9, 0),
                'cover_image' => 'https://picsum.photos/1280/720?random=tech1',
            ],
            [
                'title' => 'Festival de Música Electrónica Sunrise',
                'description' => 'Dos días de música electrónica con los mejores DJs internacionales. Escenarios múltiples, arte visual y experiencias inmersivas.',
                'location' => 'Parque de la Ciudad, CABA',
                'event_date' => Carbon::now()->addDays(30)->setTime(20, 0),
                'cover_image' => 'https://picsum.photos/1280/720?random=music1',
            ],
            [
                'title' => 'Feria Gastronómica Internacional',
                'description' => 'Evento culinario con chefs de renombre mundial. Degustaciones, talleres y showcooking en vivo.',
                'location' => 'La Rural, Palermo',
                'event_date' => Carbon::now()->addDays(45)->setTime(12, 0),
                'cover_image' => 'https://picsum.photos/1280/720?random=food1',
            ],
            [
                'title' => 'Maratón Buenos Aires 2025',
                'description' => '42K por las calles más emblemáticas de la ciudad. Más de 15,000 corredores participantes.',
                'location' => 'Obelisco (Largada)',
                'event_date' => Carbon::now()->addDays(60)->setTime(7, 0),
                'cover_image' => 'https://picsum.photos/1280/720?random=sport1',
            ],
            [
                'title' => 'Exposición de Arte Contemporáneo',
                'description' => 'Muestra de artistas emergentes y consagrados. Instalaciones, performance y networking.',
                'location' => 'MALBA, Palermo',
                'event_date' => Carbon::now()->addDays(20)->setTime(18, 0),
                'cover_image' => 'https://picsum.photos/1280/720?random=art1',
            ],
            [
                'title' => 'Comic Con Argentina 2025',
                'description' => 'Convención de comics, videojuegos, anime y cultura pop. Invitados internacionales y cosplay.',
                'location' => 'Estadio Obras, CABA',
                'event_date' => Carbon::now()->addDays(25)->setTime(10, 0),
                'cover_image' => 'https://picsum.photos/1280/720?random=comic1',
            ],
            [
                'title' => 'Desfile de Modas Fashion Week',
                'description' => 'Pasarela con diseñadores nacionales e internacionales. Vanguardia y alta costura.',
                'location' => 'La Usina del Arte, La Boca',
                'event_date' => Carbon::now()->addDays(35)->setTime(19, 30),
                'cover_image' => 'https://picsum.photos/1280/720?random=fashion1',
            ],
            [
                'title' => 'Feria del Libro Buenos Aires',
                'description' => 'La feria del libro más importante de Latinoamérica. Autores, conferencias y firmas de libros.',
                'location' => 'La Rural, Palermo',
                'event_date' => Carbon::now()->addDays(50)->setTime(14, 0),
                'cover_image' => 'https://picsum.photos/1280/720?random=book1',
            ],
        ];

        //  Asignar cada evento a un fotógrafo random
        foreach ($specificEvents as $eventData) {
            FutureEvent::create(array_merge($eventData, [
                'photographer_id' => $photographers->random()->id, //  ASIGNACIÓN RANDOM
                'status' => 'upcoming',
                'expiry_date' => Carbon::parse($eventData['event_date'])->addDays(7),
            ]));
        }

        // ============================================
        // Eventos generados con Factory
        // ============================================
        
        // 5 eventos próximos distribuidos entre fotógrafos
        $photographers->random(5)->each(function ($photographer) {
            FutureEvent::factory()->soon()->create([
                'photographer_id' => $photographer->id,
            ]);
        });

        // 3 eventos lejanos
        $photographers->random(3)->each(function ($photographer) {
            FutureEvent::factory()->distant()->create([
                'photographer_id' => $photographer->id,
            ]);
        });

        // ============================================
        // 1 Evento que ya pasó (para testing)
        // ============================================
        
        FutureEvent::create([
            'photographer_id' => $photographers->first()->id,
            'title' => '🧪 EVENTO DE PRUEBA (Ya Pasó)',
            'description' => 'Este evento ya pasó. Ejecuta "php artisan events:convert-future" para convertirlo.',
            'location' => 'Test Location',
            'event_date' => Carbon::now()->subDays(2),
            'expiry_date' => Carbon::now()->addDays(5),
            'cover_image' => 'https://picsum.photos/1280/720?random=test1',
            'status' => 'upcoming',
        ]);

        $total = FutureEvent::count();
        
        $this->command->info(" {$total} eventos futuros creados exitosamente");
        $this->command->info('');
        $this->command->line('📋 Distribución:');
        $this->command->line("   • Eventos específicos: 8");
        $this->command->line("   • Próximos (Factory): 5");
        $this->command->line("   • Lejanos (Factory): 3");
        $this->command->line("   • Para testing (pasado): 1");
        $this->command->info('');
        $this->command->line('👥 Fotógrafos con eventos futuros:');
        
        Photographer::whereHas('futureEvents')->get()->each(function ($photographer) {
            $count = $photographer->futureEvents()->count();
            $this->command->line("   • {$photographer->business_name}: {$count} eventos");
        });
        
        $this->command->info('');
        $this->command->warn('💡 Prueba la conversión: php artisan events:convert-future');
    }
}
