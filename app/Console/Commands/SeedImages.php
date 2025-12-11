<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use App\Services\ImageProcessingService;

class SeedImages extends Command
{
    protected $signature = 'seed:images {--force : Forzar descarga incluso si ya existen}';
    protected $description = 'Descarga imágenes de seed y las procesa con marca de agua';

    protected $imageService;

    public function __construct(ImageProcessingService $imageService)
    {
        parent::__construct();
        $this->imageService = $imageService;
    }

    public function handle()
    {
        $this->info(' Descargando y procesando imágenes de seed...');

        $force = $this->option('force');

        // Crear directorios temporales
        $tempDir = storage_path('app/temp-seeds');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // ================================================================
        //  IMÁGENES PARA EVENTOS FUTUROS (cover images - SIN marca de agua)
        // ================================================================
        $this->info("\n Descargando covers de eventos futuros...");

        $futureEventImages = [
            'tech2025' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1280&h=720&fit=crop',
            'lolla2025' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1280&h=720&fit=crop',
            'feria2025' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=1280&h=720&fit=crop',
            'maraton2025' => 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=1280&h=720&fit=crop',
            'comic2025' => 'https://images.unsplash.com/photo-1612036782180-6f0b6cd846fe?w=1280&h=720&fit=crop',
            'cerveza' => 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=1280&h=720&fit=crop',
            'arte' => 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=1280&h=720&fit=crop',
            'jazz' => 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=1280&h=720&fit=crop',
            'rally' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=1280&h=720&fit=crop',
            'cine' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1280&h=720&fit=crop',
            'vendimia' => 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?w=1280&h=720&fit=crop',
            'montana' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=1280&h=720&fit=crop',
            'folclore' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=1280&h=720&fit=crop',
            'artesanias' => 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?w=1280&h=720&fit=crop',
            'electronica' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=1280&h=720&fit=crop',
            'pinguino' => 'https://images.unsplash.com/photo-1551244072-5d12893278ab?w=1280&h=720&fit=crop',
            'glaciar' => 'https://images.unsplash.com/photo-1531366936337-7c912a4589a7?w=1280&h=720&fit=crop',
            'tango' => 'https://images.unsplash.com/photo-1504609773096-104ff2c73ba4?w=1280&h=720&fit=crop',
            'ushuaia' => 'https://images.unsplash.com/photo-1609137144813-7d9921338f24?w=1280&h=720&fit=crop',
            'pachamama' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1280&h=720&fit=crop',
            'carnaval' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=1280&h=720&fit=crop',
            'chamame' => 'https://images.unsplash.com/photo-1511735111819-9a3f7709049c?w=1280&h=720&fit=crop',
            'gaucho' => 'https://images.unsplash.com/photo-1553531087-6c5b6e52fc3d?w=1280&h=720&fit=crop',
            'algodon' => 'https://images.unsplash.com/photo-1509048191080-d2984bad6ae5?w=1280&h=720&fit=crop',
            'test' => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1280&h=720&fit=crop',
        ];

        $this->downloadEventImages($futureEventImages, $force);

        // ================================================================
        //  IMÁGENES GENÉRICAS PARA FOTOS DE EVENTOS (CON marca de agua)
        // ================================================================
        $this->info("\n Descargando y procesando fotos de eventos (con marca de agua)...");

        $photoImages = [
            // Bodas
            'https://images.unsplash.com/photo-1519741497674-611481863552?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=1200&h=800&fit=crop',

            // Eventos corporativos
            'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=1200&h=800&fit=crop',

            // Conciertos
            'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1429962714451-bb934ecdc4ec?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?w=1200&h=800&fit=crop',

            // Fiestas
            'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=1200&h=800&fit=crop',

            // Deportes
            'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=1200&h=800&fit=crop',

            // Retratos
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=1200&h=800&fit=crop',

            // Paisajes urbanos
            'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=1200&h=800&fit=crop',

            // Naturaleza
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1426604966848-d7adac402bff?w=1200&h=800&fit=crop',

            // Gastronomía
            'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1484723091739-30a097e8f929?w=1200&h=800&fit=crop',

            // Arte y cultura
            'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1561214115-f2f134cc4912?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1547891654-e66ed7ebb968?w=1200&h=800&fit=crop',

            // Tecnología
            'https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1488590528505-98d2b5aba04b?w=1200&h=800&fit=crop',

            // Moda
            'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1200&h=800&fit=crop',

            // Arquitectura
            'https://images.unsplash.com/photo-1487958449943-2429e8be8625?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1486718448742-163732cd1544?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1508739773434-c26b3d09e071?w=1200&h=800&fit=crop',

            // Viajes
            'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1527631746610-bca00a040d60?w=1200&h=800&fit=crop',

            // Niños y familia
            'https://images.unsplash.com/photo-1476703993599-0035a21b17a9?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1543342384-1f1350e27861?w=1200&h=800&fit=crop',

            // Mascotas
            'https://images.unsplash.com/photo-1530281700549-e82e7bf110d6?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=1200&h=800&fit=crop',

            // Abstracto
            'https://images.unsplash.com/photo-1557672172-298e090bd0f1?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1506097425191-7ad538b29cef?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1519713958759-6254243c4a53?w=1200&h=800&fit=crop',

            // Blanco y negro
            'https://images.unsplash.com/photo-1490367532201-b9bc1dc483f6?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1445264718223-106271a51cab?w=1200&h=800&fit=crop',
        ];

        // No procesamos fotos aquí, lo haremos en el seeder con photographerId real
        $this->info("     Las fotos se procesarán durante el seeding con marcas de agua personalizadas");

        // Guardar las URLs en un archivo JSON para el seeder
        Storage::disk('local')->put(
            'seed-photo-urls.json',
            json_encode($photoImages, JSON_PRETTY_PRINT)
        );

        $this->info("    " . count($photoImages) . " URLs de fotos guardadas para el seeder");

        // ================================================================
        //  IMÁGENES DE PERFIL PARA FOTÓGRAFOS (SIN marca de agua)
        // ================================================================
        $this->info("\n Descargando avatares de fotógrafos...");

        $photographerImages = [];
        for ($i = 1; $i <= 25; $i++) {
            $photographerImages["photographer_{$i}"] = "https://i.pravatar.cc/400?img={$i}";
        }

        $this->downloadPhotographerImages($photographerImages,  'profiles', $force);

        // ================================================================
//  IMÁGENES DE BANNER PARA FOTÓGRAFOS (SIN marca de agua)
// ================================================================
        $this->info("\n  Descargando banners de fotógrafos...");

        $bannerImages = [];
        for ($i = 1; $i <= 25; $i++) {
            // Banners horizontales (landscape)
            $bannerImages["banner_{$i}"] = "https://picsum.photos/seed/banner{$i}/1920/600";
        }

        $this->downloadPhotographerImages($bannerImages, 'banners', $force); 

        // Limpiar archivos temporales
        $this->info("\n Limpiando archivos temporales...");
        array_map('unlink', glob("{$tempDir}/*.*"));
        @rmdir($tempDir);

        $this->info("\n Proceso completado!");
        $this->info(" Ubicación:");
        $this->info("   • Eventos: storage/app/public/future-events/");
        $this->info("   • Fotógrafos (perfiles): storage/app/public/photographers/profiles/"); // 
        $this->info("   • Fotógrafos (banners): storage/app/public/photographers/banners/");   // 
        $this->info("   • Fotos: Se procesarán durante el seeding");

        return Command::SUCCESS;
    }

    /**
     * Descargar imágenes de eventos futuros (sin marca de agua)
     */
    private function downloadEventImages(array $images, bool $force)
    {
        $bar = $this->output->createProgressBar(count($images));
        $bar->start();

        foreach ($images as $name => $url) {
            $filename = "{$name}.jpg";
            $path = "future-events/{$filename}";

            if (!$force && Storage::disk('public')->exists($path)) {
                $bar->advance();
                continue;
            }

            try {
                $imageContent = @file_get_contents($url);

                if ($imageContent === false) {
                    $this->warn("\n  Error descargando: {$name}");
                    $bar->advance();
                    continue;
                }

                Storage::disk('public')->put($path, $imageContent);

            } catch (\Exception $e) {
                $this->warn("\n  Error: {$name} - " . $e->getMessage());
            }

            $bar->advance();
            usleep(100000); // 100ms delay para no saturar
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Descargar imágenes de fotógrafos (perfiles o banners)
     */
    private function downloadPhotographerImages(array $images, string $folder, bool $force)
    {
        $bar = $this->output->createProgressBar(count($images));
        $bar->start();

        foreach ($images as $name => $url) {
            $filename = "{$name}.jpg";
            $path = "photographers/{$folder}/{$filename}"; //  photographers/profiles/ o photographers/banners/

            if (!$force && Storage::disk('public')->exists($path)) {
                $bar->advance();
                continue;
            }

            try {
                $imageContent = @file_get_contents($url);

                if ($imageContent === false) {
                    $bar->advance();
                    continue;
                }

                Storage::disk('public')->put($path, $imageContent);

            } catch (\Exception $e) {
                $this->warn("\n  Error: {$name}");
            }

            $bar->advance();
            usleep(100000);
        }

        $bar->finish();
        $this->newLine();
    }

}
