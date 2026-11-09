<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MigrateB2Originals extends Command
{

    protected $signature = 'b2:migrate-originals';

    protected $description = 'Migra las fotos originales del bucket público al privado de forma segura';

    public function handle()
    {
        $total = Photo::whereNotNull('original_path')->count();
        
        if ($total === 0) {
            $this->info('No hay fotos para migrar en la base de datos.');
            return;
        }

        $this->info("Iniciando migración de {$total} fotos hacia el bucket privado...");
        

        $bar = $this->output->createProgressBar($total);
        $bar->start();


        Photo::whereNotNull('original_path')->chunk(100, function ($photos) use ($bar) {
            foreach ($photos as $photo) {
                $path = $photo->original_path;
                
                try {

                    if (Storage::disk('b2')->exists($path) && !Storage::disk('b2_private')->exists($path)) {
                        

                        $stream = Storage::disk('b2')->readStream($path);
                        Storage::disk('b2_private')->writeStream($path, $stream);
                        

                        Storage::disk('b2')->delete($path);
                    }
                } catch (\Exception $e) {

                    Log::error("Fallo al migrar foto ID {$photo->id}: " . $e->getMessage());
                }
                
                // Avanzamos la barra 1 paso
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info('¡Migración completada! Tu bóveda de originales ahora es privada.');
    }
}