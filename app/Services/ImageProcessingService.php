<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageProcessingService
{
    protected $manager;

  
    protected $disk = 'b2';

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Procesar y guardar foto con 3 versiones
     */
public function processPhoto($file, $photographerId)
    {
        \Log::info(' Iniciando procesamiento y subida a Backblaze B2');

        $uniqueId = $this->generateUniqueId();
        $timestamp = now()->format('Y/m/d');

        $basePath = "photos/{$photographerId}/{$timestamp}";
        
        try {
            $image = $this->manager->read($file);
            $width = $image->width();
            $height = $image->height();

            // 1. Guardar ORIGINAL 
            $originalFullPath = "{$basePath}/originals/{$uniqueId}_original.jpg";
            $encoded = $image->toJpeg(95);
            
            if (!Storage::disk($this->disk)->put($originalFullPath, (string) $encoded)) {
                throw new \Exception("Fallo al subir el archivo original a B2");
            }
            \Log::info(' Original en la nube', ['path' => $originalFullPath]);

            // 2. Crear PREVIEW CON MARCA DE AGUA
            $watermarkedFullPath = "{$basePath}/watermarked/{$uniqueId}_watermarked.jpg";
            $watermarkedImage = $this->manager->read($file);

            if ($watermarkedImage->width() > 1920 || $watermarkedImage->height() > 1920) {
                $watermarkedImage->scale(width: 1920, height: 1920);
            }

            $watermarkedImage = $this->addTiledWatermark($watermarkedImage, $photographerId);
            $encodedWatermarked = $watermarkedImage->toJpeg(85);

            // Quitamos el 'public' porque el bucket es privado y usamos temporaryUrl
            if (!Storage::disk($this->disk)->put($watermarkedFullPath, (string) $encodedWatermarked)) {
                throw new \Exception("Fallo al subir la vista previa a B2");
            }

            // 3. Crear THUMBNAIL
            $thumbnailFullPath = "{$basePath}/thumbnails/{$uniqueId}_thumb.jpg";
            $thumbnailImage = $this->manager->read($file);
            $thumbnailImage->cover(400, 400);
            $encodedThumb = $thumbnailImage->toJpeg(80);

            if (!Storage::disk($this->disk)->put($thumbnailFullPath, (string) $encodedThumb)) {
                throw new \Exception("Fallo al subir el thumbnail a B2");
            }

            return [
                'unique_id' => $uniqueId,
                'original_path' => $originalFullPath,
                'watermarked_path' => $watermarkedFullPath,
                'thumbnail_path' => $thumbnailFullPath,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'dimensions' => [
                    'width' => $width,
                    'height' => $height,
                ],
            ];

        } catch (\Exception $e) {
            \Log::error('❌ Error subiendo a Backblaze', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
 



    protected function addTiledWatermark($image, $photographerId)
    {
        try {
            // Verificar si la marca de agua está habilitada
            if (!config('app.watermark_enabled', true)) {
                \Log::info(' Marca de agua deshabilitada en .env');
                return $image;
            }

            // Ruta del logo
            $logoPath = config('app.watermark_logo_path', 'watermarks/logo.png');
            $logoFullPath = public_path($logoPath);

            // Verificar que existe el logo
            if (!file_exists($logoFullPath)) {
                \Log::warning(' Logo de marca de agua no encontrado', ['path' => $logoFullPath]);
                return $image;
            }

            \Log::info(' Aplicando marca de agua en patrón');

            //  Cargar el logo manteniendo transparencia
            $watermark = $this->manager->read($logoFullPath);

            // Configuración desde .env
            $opacity = config('app.watermark_opacity', 30); // 0-100
            $tileSize = config('app.watermark_tile_size', 300); // Espaciado en píxeles
            $rotation = config('app.watermark_rotation', -30); // Grados

            // Redimensionar logo si es muy grande
            $maxLogoSize = 200;
            if ($watermark->width() > $maxLogoSize) {
                $watermark->scale(width: $maxLogoSize);
            }

            //  Rotar el logo manteniendo transparencia
            if ($rotation != 0) {
                $watermark->rotate($rotation, background: 'ffffff00'); // Fondo transparente
            }

            // Obtener dimensiones
            $imageWidth = $image->width();
            $imageHeight = $image->height();
            $logoWidth = $watermark->width();
            $logoHeight = $watermark->height();

            // Calcular cuántos logos caben en patrón
            $cols = ceil($imageWidth / $tileSize) + 1;
            $rows = ceil($imageHeight / $tileSize) + 1;

            \Log::info(' Patrón de marca de agua', [
                'cols' => $cols,
                'rows' => $rows,
                'tile_size' => $tileSize,
                'opacity' => $opacity
            ]);

            //  Aplicar marca de agua en patrón CON TRANSPARENCIA
            for ($row = 0; $row < $rows; $row++) {
                for ($col = 0; $col < $cols; $col++) {
                    $x = ($col * $tileSize) - ($logoWidth / 2);
                    $y = ($row * $tileSize) - ($logoHeight / 2);

                    //  Colocar el logo con opacidad preservando transparencia
                    $image->place(
                        element: $watermark,
                        position: 'top-left',
                        offset_x: (int) $x,
                        offset_y: (int) $y,
                        opacity: $opacity
                    );
                }
            }

            \Log::info(' Marca de agua aplicada correctamente');

            return $image;

        } catch (\Exception $e) {
            \Log::error(' Error al aplicar marca de agua', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // En caso de error, devolver la imagen sin marca de agua
            return $image;
        }
    }


 



    protected function generateUniqueId()
    {
        do {
            $uniqueId = strtoupper(Str::random(6));
        } while (\App\Models\Photo::where('unique_id', $uniqueId)->exists());

        return $uniqueId;
    }



public function deletePhoto($photo)
    {
       
        Storage::disk($this->disk)->delete([
            $photo->original_path,
            $photo->watermarked_path,
            $photo->thumbnail_path,
        ]);
        
        \Log::info(' Archivos eliminados de Backblaze', ['id' => $photo->unique_id]);
    }

    public function updatePhoto($file, $photo)
    {
        $this->deletePhoto($photo);
        return $this->processPhoto($file, $photo->photographer_id);
    }
}
