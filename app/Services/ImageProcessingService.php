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
        // Inicializar ImageManager con driver GD
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Procesar y guardar foto con 3 versiones
     */
    public function processPhoto($file,$photographerId)
    {
        \Log::info('🖼️ Iniciando procesamiento de imagen');

        // Generar nombres únicos
        $uniqueId =$this->generateUniqueId();
        $timestamp = now()->format('Y/m/d');
        
        // Rutas de almacenamiento
        $basePath = "photos/{$photographerId}/{$timestamp}";
        $originalPath = "{$basePath}/originals";
        $watermarkedPath = "{$basePath}/watermarked";
        $thumbnailPath = "{$basePath}/thumbnails";

        try {
            // Leer la imagen original
            $image =$this->manager->read($file);
            $width =$image->width();
            $height =$image->height();

            \Log::info('📐 Dimensiones originales', ['width' => $width, 'height' => $height]);

            // 1. Guardar ORIGINAL (sin marca de agua - solo para después de pagar)
            $originalFilename = "{$uniqueId}_original.jpg";
            $originalFullPath = "{$originalPath}/{$originalFilename}";
            
            $encoded =$image->toJpeg(95);
            Storage::disk('public')->put($originalFullPath, (string)$encoded);
            \Log::info('✅ Original guardado', ['path' => $originalFullPath]);

            // 2. Crear PREVIEW CON MARCA DE AGUA (para navegación gratuita)
            $watermarkedFilename = "{$uniqueId}_watermarked.jpg";
            $watermarkedFullPath = "{$watermarkedPath}/{$watermarkedFilename}";
            
            $watermarkedImage =$this->manager->read($file);
            
            // Redimensionar si es muy grande
            if ($watermarkedImage->width() > 1920 || $watermarkedImage->height() > 1920) {
                $watermarkedImage->scale(width: 1920, height: 1920);
            }
            
            // TODO: Aplicar marca de agua (después lo implementamos)
            // $this->addWatermark($watermarkedImage,$photographerId);
            
            $encodedWatermarked =$watermarkedImage->toJpeg(85);
            Storage::disk('public')->put($watermarkedFullPath, (string)$encodedWatermarked);
            \Log::info('✅ Watermarked guardado', ['path' => $watermarkedFullPath]);

            // 3. Crear THUMBNAIL (400x400 cuadrado con marca pequeña)
            $thumbnailFilename = "{$uniqueId}_thumb.jpg";
            $thumbnailFullPath = "{$thumbnailPath}/{$thumbnailFilename}";
            
            $thumbnailImage =$this->manager->read($file);
            $thumbnailImage->cover(400, 400);
            
            // TODO: Marca de agua pequeña
            
            $encodedThumb =$thumbnailImage->toJpeg(80);
            Storage::disk('public')->put($thumbnailFullPath, (string)$encodedThumb);
            \Log::info('✅ Thumbnail guardado', ['path' => $thumbnailFullPath]);

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
            \Log::error('💥 Error en processPhoto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Aplicar marca de agua (implementar después)
     */
    protected function addWatermark($image,$photographerId)
    {
        // TODO: Implementar marca de agua con texto o logo
        // Por ahora retorna la imagen sin cambios
        return $image;
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
    public function updatePhoto($file,$photo)
    {
        $this->deletePhoto($photo);
        return $this->processPhoto($file,$photo->photographer_id);
    }
}
