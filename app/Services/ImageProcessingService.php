<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class ImageProcessingService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Procesa una foto: crea versión con marca de agua y thumbnail
     */
    public function processPhoto($uploadedFile, $photoId, $photographerName)
    {
        // Generar nombres únicos
        $filename = time() . '_' .$photoId . '_' . uniqid();
        
        // Paths
        $originalPath = 'photos/originals/' .$filename . '.jpg';
        $watermarkedPath = 'photos/watermarked/' .$filename . '.jpg';
        $thumbnailPath = 'photos/thumbnails/' .$filename . '.jpg';

        // 1. Guardar original
        Storage::disk('public')->put($originalPath, file_get_contents($uploadedFile));

        // 2. Crear versión con marca de agua
        $watermarkedImage =$this->manager->read($uploadedFile);
        
        // Redimensionar si es muy grande (max 1920px de ancho)
        if ($watermarkedImage->width() > 1920) {
            $watermarkedImage->scale(width: 1920);
        }

        // Agregar marca de agua de texto
        $this->addTextWatermark($watermarkedImage,$photoId, $photographerName);

        // Guardar versión con marca de agua
        $encoded =$watermarkedImage->encodeByExtension('jpg', quality: 85);
        Storage::disk('public')->put($watermarkedPath,$encoded);

        // 3. Crear thumbnail
        $thumbnail =$this->manager->read($uploadedFile);
        $thumbnail->cover(400, 400);
        
        $encodedThumb =$thumbnail->encodeByExtension('jpg', quality: 80);
        Storage::disk('public')->put($thumbnailPath,$encodedThumb);

        return [
            'original_path' => $originalPath,
            'watermarked_path' => $watermarkedPath,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    /**
     * Agrega marca de agua de texto a la imagen
     */
    protected function addTextWatermark($image,$photoId, $photographerName)
    {
        $watermarkText = "© Photo Marketplace - ID: {$photoId}";
        $photographerText =$photographerName;

        $width =$image->width();
        $height =$image->height();

        // Calcular tamaño de fuente basado en dimensiones de imagen
        $fontSize = max(20, min(60,$width / 30));

        // Marca de agua central grande (semi-transparente)
        $image->text($watermarkText,$width / 2, $height / 2, function ($font) use ($fontSize) {
            $font->file(public_path('fonts/Arial.ttf')); // Necesitarás una fuente
            $font->size($fontSize * 1.5);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
            $font->opacity(0.15); // Muy transparente
        });

        // Marca de agua inferior derecha (más visible)
        $image->text($watermarkText,$width - 20, $height - 60, function ($font) use ($fontSize) {
            $font->file(public_path('fonts/Arial.ttf'));
            $font->size($fontSize * 0.6);
            $font->color('#ffffff');
            $font->align('right');
            $font->valign('bottom');
            $font->opacity(0.8);
        });

        // Nombre del fotógrafo
        $image->text($photographerText,$width - 20, $height - 30, function ($font) use ($fontSize) {
            $font->file(public_path('fonts/Arial.ttf'));
            $font->size($fontSize * 0.5);
            $font->color('#ffffff');
            $font->align('right');
            $font->valign('bottom');
            $font->opacity(0.7);
        });

        // Marca de agua repetida en diagonal (opcional, más protección)
        $this->addRepeatingWatermark($image,$photoId);

        return $image;
    }

    /**
     * Agrega marca de agua repetida en diagonal
     */
    protected function addRepeatingWatermark($image,$photoId)
    {
        $width =$image->width();
        $height =$image->height();
        $text = "ID: {$photoId}";
        
        $fontSize = max(15,$width / 50);
        $spacing = 300; // Espaciado entre marcas

        for ($y = 0; $y < $height +$spacing; $y +=$spacing) {
            for ($x = -$spacing; $x < $width +$spacing; $x +=$spacing) {
                $image->text($text,$x, $y, function ($font) use ($fontSize) {
                    $font->file(public_path('fonts/Arial.ttf'));
                    $font->size($fontSize);
                    $font->color('#ffffff');
                    $font->align('center');
                    $font->valign('middle');
                    $font->opacity(0.05); // Muy sutil
                    $font->angle(45); // Diagonal
                });
            }
        }

        return $image;
    }

    /**
     * Elimina archivos de una foto
     */
    public function deletePhotoFiles($photo)
    {
        Storage::disk('public')->delete([
            $photo->original_path,
            $photo->watermarked_path,
            $photo->thumbnail_path,
        ]);
    }
}
