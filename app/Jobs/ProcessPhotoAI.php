<?php

namespace App\Jobs;

use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPhotoAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $photoId;
    public $timeout = 300; 

    public function __construct($photoId)
    {
        $this->photoId = $photoId;
    }

    public function handle()
    {
        $photo = Photo::find($this->photoId);
        if (!$photo) return;

        $imageUrl = $photo->original_url;


        $scriptPath = base_path('scripts/process_ai.py');
        

        $pythonBin = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        $command = escapeshellcmd("$pythonBin $scriptPath") . " " . escapeshellarg($imageUrl) . " 1 1";
        $output = shell_exec($command);

        if (!$output) {
            Log::error("Fallo al ejecutar Python para foto {$this->photoId}");
            return;
        }


        $result = json_decode(trim($output), true);

        if (isset($result['success']) && $result['success']) {
            $hasFaces = $result['faces']['count'] > 0;
            $hasBibs = count($result['bibs']) > 0;

            $photo->update([
                'has_faces' => $hasFaces,
                'face_encodings' => $hasFaces ? $result['faces']['descriptors'] : null,
                'faces_processed_at' => now(),

                'bib_processed' => true,
                'bib_numbers' => $hasBibs ? $result['bibs'] : null,
                'bib_processed_at' => now(),
            ]);

            Log::info("IA Ok para foto {$this->photoId}. Rostros: {$result['faces']['count']}");
        } else {
            Log::error("Error Python para foto {$this->photoId}: " . ($result['error'] ?? 'Sin error descriptivo'));
        }
    }
}