<?php

namespace App\Console\Commands;

use App\Models\FutureEvent;
use App\Models\Event;
use App\Models\Photographer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConvertFutureEvents extends Command
{
    protected $signature = 'events:convert-future';
    protected $description = 'Convertir eventos futuros que ya pasaron en eventos normales';

    public function handle()
    {
        $this->info('🔄 Buscando eventos futuros para convertir...');

        $futureEvents = FutureEvent::readyToConvert()->get();

        if ($futureEvents->isEmpty()) {
            $this->info(' No hay eventos para convertir');
            return 0;
        }

        $converted = 0;
        $errors = 0;

        foreach ($futureEvents as $futureEvent) {
            try {
                //  VALIDAR que el fotógrafo existe
                if (!$futureEvent->photographer_id) {
                    $this->warn("  Evento sin fotógrafo asignado: {$futureEvent->title}");
                    $errors++;
                    continue;
                }

                $photographer = Photographer::find($futureEvent->photographer_id);
                
                if (!$photographer) {
                    $this->error(" Fotógrafo ID {$futureEvent->photographer_id} no existe para: {$futureEvent->title}");
                    $this->line("   → Asignando a fotógrafo por defecto...");
                    
                    // Buscar un fotógrafo por defecto
                    $photographer = Photographer::where('status', 'approved')->first();
                    
                    if (!$photographer) {
                        $this->error("   → No hay fotógrafos disponibles. Saltando evento.");
                        $errors++;
                        continue;
                    }
                    
                    // Actualizar el evento futuro con el nuevo fotógrafo
                    $futureEvent->update(['photographer_id' => $photographer->id]);
                    $this->info("   → Reasignado a: {$photographer->business_name}");
                }

                //  Crear evento normal
                $event = Event::create([
                    'photographer_id' => $photographer->id,
                    'name' => $futureEvent->title,
                    'slug' => Str::slug($futureEvent->title) . '-' . Str::random(6),
                    'description' => $futureEvent->description,
                    'long_description' => $futureEvent->description,
                    'location' => $futureEvent->location,
                    'event_date' => $futureEvent->event_date,
                    'cover_image' => $futureEvent->cover_image,
                    'is_private' => false,
                    'is_active' => true,
                ]);

                // Marcar como convertido
                $futureEvent->update([
                    'status' => 'converted',
                    'converted_event_id' => $event->id,
                ]);

                $this->info(" Convertido: {$futureEvent->title}");
                $this->line("   → Evento #{$event->id} ({$event->slug})");
                $this->line("   → Fotógrafo: {$photographer->business_name}");
                
                $converted++;

                Log::info(' Evento futuro convertido', [
                    'future_event_id' => $futureEvent->id,
                    'new_event_id' => $event->id,
                    'photographer_id' => $photographer->id,
                    'photographer_name' => $photographer->business_name,
                ]);

            } catch (\Exception $e) {
                $this->error(" Error convirtiendo {$futureEvent->title}: {$e->getMessage()}");
                
                Log::error(' Error en conversión de evento futuro', [
                    'future_event_id' => $futureEvent->id,
                    'photographer_id' => $futureEvent->photographer_id ?? 'NULL',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                $errors++;
            }
        }

        $this->newLine();
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("🎉 Total convertidos: {$converted}");
        
        if ($errors > 0) {
            $this->warn("  Total con errores: {$errors}");
        }
        
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        return $errors > 0 ? 1 : 0;
    }
}
