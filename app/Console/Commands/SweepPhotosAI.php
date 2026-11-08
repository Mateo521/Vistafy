<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Photo;
use App\Jobs\ProcessPhotoAI;

class SweepPhotosAI extends Command
{

    protected $signature = 'ai:sweep {event_id? : ID del evento específico} {--force : Reprocesar fotos que ya tienen datos}';

    protected $description = 'Encola las fotos existentes para que la IA de Python extraiga rostros y dorsales.';

    public function handle()
    {
        $eventId = $this->argument('event_id');
        $force = $this->option('force');

        $query = Photo::query();

        if ($eventId) {
            $query->where('event_id', $eventId);
            $this->info("Filtrando por el evento ID: {$eventId}");
        }


        if (!$force) {
            $query->whereNull('faces_processed_at');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('No se encontraron fotos pendientes de procesar bajo estos criterios.');
            return;
        }

        $this->info("Se encontraron {$total} fotos. Iniciando envío a la cola de procesamiento...");
        

        $bar = $this->output->createProgressBar($total);
        $bar->start();


        foreach ($query->cursor() as $photo) {

            ProcessPhotoAI::dispatch($photo->id);
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("¡Éxito! Las {$total} fotos fueron enviadas a la cola.");
        $this->comment("Supervisor y Python las están procesando en segundo plano en este momento.");
    }
}