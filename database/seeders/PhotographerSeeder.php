<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Photographer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PhotographerSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('is_admin', true)->first()?->id ?? 1;

        $regions = [
            'Buenos Aires',
            'CABA',
            'Córdoba',
            'Santa Fe',
            'Mendoza',
            'Tucumán',
            'Rosario',
        ];

        $photographers = [
            [
                'name' => 'Estudio Luz y Sombra',
                'email' => 'contacto@luzysombra.com',
                'bio' => 'Especializados en fotografía de eventos corporativos y sociales. Con más de 10 años de experiencia capturando momentos únicos.',
            ],
            [
                'name' => 'Foto Momentos',
                'email' => 'info@fotomomentos.com',
                'bio' => 'Fotógrafos profesionales enfocados en bodas y eventos familiares. Trabajamos con equipos de última generación.',
            ],
            [
                'name' => 'Click Perfecto',
                'email' => 'contacto@clickperfecto.com',
                'bio' => 'Tu evento merece las mejores fotos. Especializados en fiestas de 15 años y eventos infantiles.',
            ],
            [
                'name' => 'Vision Studio',
                'email' => 'hola@visionstudio.com',
                'bio' => 'Fotografía artística y comercial. Cubrimos eventos de todo tipo con un enfoque creativo y profesional.',
            ],
            [
                'name' => 'Flash Eventos',
                'email' => 'info@flasheventos.com',
                'bio' => 'Cobertura completa de eventos sociales y empresariales. Entrega rápida y calidad garantizada.',
            ],
        ];

        foreach ($photographers as $index => $photographerData) {
            // Verificar si el usuario ya existe
            $user = User::updateOrCreate(
                ['email' => $photographerData['email']],
                [
                    'name' => $photographerData['name'],
                    'password' => Hash::make('password'),
                    'role' => 'photographer',
                    'email_verified_at' => now(),
                    'is_admin' => false,
                ]
            );

            //  Crear imágenes de muestra
            $profilePhotoPath = $this->createSampleImage('profile', $index + 1);
            $bannerPhotoPath = $this->createSampleImage('banner', $index + 1);

            // Verificar si el fotógrafo ya existe
            Photographer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $photographerData['name'],
                    'slug' => Str::slug($photographerData['name'] . '-' . $user->id),
                    'region' => $regions[array_rand($regions)],
                    'bio' => $photographerData['bio'],
                    'phone' => '+54 9 11 ' . rand(1000, 9999) . '-' . rand(1000, 9999),
                    'profile_photo' => $profilePhotoPath,  //  AGREGAR
                    'banner_photo' => $bannerPhotoPath,    //  AGREGAR
                    'is_active' => true,
                    'is_verified' => true,
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $adminId,
                ]
            );
        }

        $this->command->info('✓ ' . count($photographers) . ' fotógrafos creados/actualizados correctamente');
    }
    /**
     *  Crear una imagen de muestra con placeholders reales
     */
    private function createSampleImage(string $type, int $index): string
    {
        $folder = $type === 'profile' ? 'photographers/profiles' : 'photographers/banners';
        
        // Crear carpeta si no existe
        Storage::disk('public')->makeDirectory($folder);

        // Generar nombre de archivo único
        $filename = Str::random(40) . '.jpg';
        $path = $folder . '/' . $filename;

        // Descargar imagen de placeholder (con diferentes semillas para variedad)
        $size = $type === 'profile' ? '400x400' : '1200x400';
        $seed = $index + ($type === 'profile' ? 100 : 200);
        $imageUrl = "https://picsum.photos/{$size}?random={$seed}";
        
        try {
            // Intentar descargar imagen real
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0',
                ],
            ]);
            
            $imageData = @file_get_contents($imageUrl, false, $context);
            
            if ($imageData === false) {
                throw new \Exception('Failed to download image');
            }
            
            Storage::disk('public')->put($path, $imageData);
            $this->command->info("  ✓ Imagen {$type} creada: {$filename}");
            
        } catch (\Exception $e) {
            // Si falla, crear imagen simple de 1x1 pixel
            $this->command->warn("  ⚠ No se pudo descargar imagen, creando placeholder simple");
            
            $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
            Storage::disk('public')->put($path, $imageData);
        }

        return $path;
    }
}
