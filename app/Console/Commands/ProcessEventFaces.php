<?php
// app/Console/Commands/ProcessEventFaces.php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProcessEventFaces extends Command
{
    protected $signature = 'faces:process-event {event_id}';
    protected $description = 'Procesa todas las fotos de un evento para extraer descriptores faciales usando Python';

    public function handle()
    {
        $eventId = $this->argument('event_id');
        $event = Event::findOrFail($eventId);

        $this->info(" Procesando evento: {$event->name}");
        $this->info("📊 Total de fotos: " . $event->photos()->count());

        $photos = $event->photos()->whereNull('face_descriptors')->get();

        if ($photos->isEmpty()) {
            $this->info(" Todas las fotos ya tienen descriptores");
            return 0;
        }

        $this->info("🔍 Fotos sin procesar: " . $photos->count());

        $bar = $this->output->createProgressBar($photos->count());
        $bar->start();

        $processed = 0;
        $errors = 0;

        foreach ($photos as $photo) {
            try {
                $descriptors = $this->extractFaceDescriptors($photo);

                if (!empty($descriptors)) {
                    $photo->update([
                        'face_descriptors' => json_encode($descriptors),
                    ]);
                    $processed++;
                } else {
                    $this->newLine();
                    $this->warn("    No se detectaron rostros en: {$photo->original_name}");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("   Error en {$photo->original_name}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info(" Procesamiento completado:");
        $this->info("   • Procesadas: {$processed}");
        $this->info("   • Sin rostros: " . ($photos->count() - $processed - $errors));
        $this->info("   • Errores: {$errors}");

        return 0;
    }

    /**
     * Extrae descriptores faciales de una foto usando Python
     */
    protected function extractFaceDescriptors(Photo $photo): array
    {
        // Ruta de la foto original
        $imagePath = storage_path('app/' . $photo->original_path);

        if (!file_exists($imagePath)) {
            throw new \Exception("Archivo no encontrado: {$imagePath}");
        }

        // Ruta al script de Python
        $pythonScript = base_path('scripts/extract_faces.py');

        if (!file_exists($pythonScript)) {
            throw new \Exception("Script de Python no encontrado. Ejecuta: php artisan faces:setup");
        }

        // Ejecutar Python
        $command = sprintf(
            'python "%s" "%s" 2>&1',
            $pythonScript,
            $imagePath
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Error ejecutando Python: " . implode("\n", $output));
        }

        // Python devuelve JSON con los descriptores
        $result = json_decode(implode('', $output), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Respuesta inválida de Python");
        }

        return $result['descriptors'] ?? [];
    }
}
