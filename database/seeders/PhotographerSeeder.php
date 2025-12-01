<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Photographer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

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

        foreach ($photographers as $photographerData) {
            //  Verificar si el usuario ya existe
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

            //  Verificar si el fotógrafo ya existe
            Photographer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $photographerData['name'],
                    'slug' => Str::slug($photographerData['name'] . '-' . $user->id),
                    'region' => $regions[array_rand($regions)],
                    'bio' => $photographerData['bio'],
                    'phone' => '+54 9 11 ' . rand(1000, 9999) . '-' . rand(1000, 9999),
                    'is_active' => true,
                    'is_verified' => true,
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $adminId,
                ]
            );
        }

        $this->command->info(' ' . count($photographers) . ' fotógrafos creados/actualizados correctamente');
    }
}
