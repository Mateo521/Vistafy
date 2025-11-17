<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Photographer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PhotographerSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            'Buenos Aires',
            'CABA',
            'Córdoba',
            'Santa Fe',
            'Mendoza',
            'Tucumán',
            'Salta',
            'Entre Ríos',
            'Neuquén',
            'San Juan'
        ];

        $photographers = [
            [
                'name' => 'Estudio Lumina',
                'email' => 'fotografo1@pixelspot.com',
                'bio' => 'Fotógrafo profesional especializado en eventos sociales y corporativos. +10 años de experiencia capturando momentos únicos.',
            ],
            [
                'name' => 'Foto Arte',
                'email' => 'fotografo2@pixelspot.com',
                'bio' => 'Capturamos momentos únicos con creatividad y profesionalismo. Cobertura en todo el país.',
            ],
            [
                'name' => 'Momentos Perfectos',
                'email' => 'fotografo3@pixelspot.com',
                'bio' => 'Estudio fotográfico con equipamiento de última generación. Especializados en bodas y eventos especiales.',
            ],
            [
                'name' => 'Captura Visual',
                'email' => 'fotografo4@pixelspot.com',
                'bio' => 'Fotógrafo freelance apasionado por contar historias a través de imágenes. Arte y técnica en cada foto.',
            ],
            [
                'name' => 'Pixelart Studio',
                'email' => 'fotografo5@pixelspot.com',
                'bio' => 'Equipo de fotógrafos profesionales. Entregas rápidas y calidad garantizada en cada proyecto.',
            ],
            [
                'name' => 'Flash Creativo',
                'email' => 'fotografo6@pixelspot.com',
                'bio' => 'Innovación y creatividad en cada disparo. Especializados en fotografía de eventos deportivos y culturales.',
            ],
            [
                'name' => 'Foco Profesional',
                'email' => 'fotografo7@pixelspot.com',
                'bio' => 'Fotografía profesional para eventos corporativos, sociales y comerciales. Resultados extraordinarios.',
            ],
            [
                'name' => 'Imágenes & Co',
                'email' => 'fotografo8@pixelspot.com',
                'bio' => 'Transformamos momentos en recuerdos eternos. Fotografía artística y documental de alta calidad.',
            ],
            [
                'name' => 'Visual Story',
                'email' => 'fotografo9@pixelspot.com',
                'bio' => 'Contamos tu historia a través de imágenes. Fotografía emotiva y auténtica para tus eventos.',
            ],
            [
                'name' => 'Click Mágico',
                'email' => 'fotografo10@pixelspot.com',
                'bio' => 'Magia en cada clic. Fotografía creativa y personalizada. Hacemos realidad la visión de nuestros clientes.',
            ],
        ];

        foreach ($photographers as $index => $photographerData) {
            // Crear usuario
            $user = User::create([
                'name' => $photographerData['name'],
                'email' => $photographerData['email'],
                'password' => Hash::make('password'),
                'role' => 'photographer',
                'email_verified_at' => now(),
            ]);

            // Crear fotógrafo con los campos correctos
            Photographer::create([
                'user_id' => $user->id,
                'business_name' => $photographerData['name'],
                'slug' => Str::slug($photographerData['name'] . '-' . $user->id),
                'region' => $regions[array_rand($regions)],
                'bio' => $photographerData['bio'],
                'phone' => '+54 9 11 ' . rand(1000, 9999) . '-' . rand(1000, 9999),
                'is_active' => true,
                'is_verified' => true,
            ]);
        }

        // Crear usuario cliente de prueba
        User::create([
            'name' => 'María González',
            'email' => 'cliente@test.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        echo "✅ Seeder completado: 10 fotógrafos y 1 cliente creados.\n";
    }
}
