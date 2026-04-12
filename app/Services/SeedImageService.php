<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class SeedImageService
{
    protected $imageService;
    protected $photoUrls;

    protected $disk = 'b2';
    
    public function __construct(ImageProcessingService $imageService)
    {
        $this->imageService = $imageService;
        $this->loadPhotoUrls();
    }

    /**
     * Cargar URLs de fotos desde el archivo JSON
     */
    protected function loadPhotoUrls()
    {
        if (Storage::disk('local')->exists('seed-photo-urls.json')) {
            $this->photoUrls = json_decode(
                Storage::disk('local')->get('seed-photo-urls.json'),
                true
            );
        } else {
            $this->photoUrls = [];
        }
    }

    /**
     * Procesar foto de seed usando el servicio real
     */


    public function processSeedPhoto($photographerId, $index = null)
    {
        if (empty($this->photoUrls)) {
            throw new \Exception('No hay URLs de fotos disponibles. Ejecuta: php artisan seed:images');
        }

        // Si no se especifica índice, usar uno aleatorio
        if ($index === null) {
            $index = array_rand($this->photoUrls);
        } else {
            $index = $index % count($this->photoUrls);
        }

        $url = $this->photoUrls[$index];
        $tempPath = null;

        try {
            // Descargar imagen temporalmente
            $tempPath = $this->downloadTempImage($url, $index);

            if (!$tempPath || !file_exists($tempPath)) {
                throw new \Exception("Error descargando imagen desde: {$url}");
            }

            // Verificar que el archivo descargado sea válido
            if (filesize($tempPath) === 0) {
                throw new \Exception("Imagen descargada está vacía: {$url}");
            }

            // Crear UploadedFile fake para simular upload
            $uploadedFile = new UploadedFile(
                $tempPath,
                "seed_photo_{$index}.jpg",
                'image/jpeg',
                null,
                true // test mode
            );

            // Procesar con el servicio real (con marca de agua)
            $result = $this->imageService->processPhoto($uploadedFile, $photographerId);

            return $result;

        } catch (\Exception $e) {
            // Log del error con contexto
            \Log::error("Error en processSeedPhoto", [
                'photographer_id' => $photographerId,
                'index' => $index,
                'url' => $url,
                'temp_path' => $tempPath,
                'error' => $e->getMessage(),
            ]);

            // Re-lanzar la excepción para que el seeder la capture
            throw $e;

        } finally {
            // Limpiar archivo temporal siempre (aunque haya error)
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }



    /**
     * Descargar imagen temporalmente
     */
    protected function downloadTempImage($url, $index)
    {
        $tempDir = storage_path('app/temp-seeds');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = "{$tempDir}/photo_{$index}_" . time() . ".jpg";

        $imageContent = @file_get_contents($url);

        if ($imageContent === false) {
            return false;
        }

        file_put_contents($tempPath, $imageContent);

        return $tempPath;
    }

    /**
     * Obtener ruta de imagen de evento futuro
     */
public function getFutureEventImage($name)
    {
        $localPath = "future-events/{$name}.jpg";
        $cloudPath = "seeds/future-events/{$name}.jpg";

        // Si el archivo existe localmente y NO está en la nube, lo subimos
        if (Storage::disk('public')->exists($localPath)) {
            if (!Storage::disk($this->disk)->exists($cloudPath)) {
                $content = Storage::disk('public')->get($localPath);
                Storage::disk($this->disk)->put($cloudPath, $content, 'public');
            }
            return $cloudPath;
        }

        return null;
    }
    /**
     * Obtener ruta de imagen de fotógrafo
     */
  public function getPhotographerImage($index)
    {
        $localPath = "photographers/profiles/photographer_{$index}.jpg";
        $cloudPath = "seeds/photographers/profiles/photographer_{$index}.jpg";

        if (Storage::disk('public')->exists($localPath)) {
            if (!Storage::disk($this->disk)->exists($cloudPath)) {
                $content = Storage::disk('public')->get($localPath);
                Storage::disk($this->disk)->put($cloudPath, $content, 'public');
            }
            return $cloudPath;
        }

        return null;
    }

    /**
     * Obtener ruta de imagen de banner de fotógrafo
     */
   public function getPhotographerBanner($index)
    {
        $localPath = "photographers/banners/banner_{$index}.jpg";
        $cloudPath = "seeds/photographers/banners/banner_{$index}.jpg";

        if (Storage::disk('public')->exists($localPath)) {
            if (!Storage::disk($this->disk)->exists($cloudPath)) {
                $content = Storage::disk('public')->get($localPath);
                Storage::disk($this->disk)->put($cloudPath, $content, 'public');
            }
            return $cloudPath;
        }

        return null;
    }
}
