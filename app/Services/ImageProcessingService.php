<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageProcessingService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Procesar y guardar foto con 3 versiones
     */
    public function processPhoto($file, $photographerId)
    {
        \Log::info(' Iniciando procesamiento de imagen');

        // Generar nombres únicos
        $uniqueId = $this->generateUniqueId();
        $timestamp = now()->format('Y/m/d');

        // Rutas de almacenamiento
        $basePath = "photos/{$photographerId}/{$timestamp}";
        $originalPath = "{$basePath}/originals";
        $watermarkedPath = "{$basePath}/watermarked";
        $thumbnailPath = "{$basePath}/thumbnails";

        try {
            // Leer la imagen original
            $image = $this->manager->read($file);
            $width = $image->width();
            $height = $image->height();

            \Log::info(' Dimensiones originales', ['width' => $width, 'height' => $height]);

            // 1. Guardar ORIGINAL (sin marca de agua) - PRIVADO
            $originalFilename = "{$uniqueId}_original.jpg";
            $originalFullPath = "{$originalPath}/{$originalFilename}";

            $encoded = $image->toJpeg(95);
            Storage::disk('public')->put($originalFullPath, (string) $encoded);
            \Log::info(' Original guardado', ['path' => $originalFullPath]);

            // 2. Crear PREVIEW CON MARCA DE AGUA (estilo Shutterstock)
            $watermarkedFilename = "{$uniqueId}_watermarked.jpg";
            $watermarkedFullPath = "{$watermarkedPath}/{$watermarkedFilename}";

            $watermarkedImage = $this->manager->read($file);

            // Redimensionar si es muy grande (para optimizar)
            if ($watermarkedImage->width() > 1920 || $watermarkedImage->height() > 1920) {
                $watermarkedImage->scale(width: 1920, height: 1920);
            }

            //  APLICAR MARCA DE AGUA EN PATRÓN
            $watermarkedImage = $this->addTiledWatermark($watermarkedImage, $photographerId);

            $encodedWatermarked = $watermarkedImage->toJpeg(85);
            Storage::disk('public')->put($watermarkedFullPath, (string) $encodedWatermarked);
            \Log::info(' Watermarked guardado', ['path' => $watermarkedFullPath]);

            // 3. Crear THUMBNAIL (sin marca de agua, es muy pequeño)
            $thumbnailFilename = "{$uniqueId}_thumb.jpg";
            $thumbnailFullPath = "{$thumbnailPath}/{$thumbnailFilename}";

            $thumbnailImage = $this->manager->read($file);
            $thumbnailImage->cover(400, 400);

            $encodedThumb = $thumbnailImage->toJpeg(80);
            Storage::disk('public')->put($thumbnailFullPath, (string) $encodedThumb);
            \Log::info(' Thumbnail guardado', ['path' => $thumbnailFullPath]);

            // Retornar las rutas completas
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
            \Log::error(' Error en processPhoto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     *  MARCA DE AGUA EN PATRÓN (ESTILO SHUTTERSTOCK) - CON TRANSPARENCIA
     */
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
            $logoFullPath = storage_path('app/public/' . $logoPath);

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


    /**
     * Generar ID único de 6 caracteres
     */
    protected function generateUniqueId()
    {
        do {
            $uniqueId = strtoupper(Str::random(6));
        } while (\App\Models\Photo::where('unique_id', $uniqueId)->exists());

        return $uniqueId;
    }

    /**
     * Eliminar archivos de una foto
     */
    public function deletePhoto($photo)
    {
        Storage::disk('public')->delete([
            $photo->original_path,
            $photo->watermarked_path,
            $photo->thumbnail_path,
        ]);
    }

    /**
     * Actualizar foto (eliminar la anterior y procesar la nueva)
     */
    public function updatePhoto($file, $photo)
    {
        $this->deletePhoto($photo);
        return $this->processPhoto($file, $photo->photographer_id);
    }
}
