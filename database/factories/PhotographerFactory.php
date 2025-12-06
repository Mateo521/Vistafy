<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotographerFactory extends Factory
{
    public function definition(): array
    {
        $regions = [
            'Buenos Aires', 'CABA', 'Córdoba', 'Santa Fe', 'Mendoza',
            'Tucumán', 'Rosario', 'Salta', 'Neuquén', 'Entre Ríos',
        ];

        return [
            'user_id' => User::factory(), // Crea un usuario automáticamente
            'business_name' => $this->faker->company() . ' Photography',
            'phone' => $this->faker->phoneNumber(),
            'region' => $this->faker->randomElement($regions),
            'bio' => $this->faker->paragraph(3),
            'profile_photo' => null, // O una URL externa
            'banner_photo' => null,
            'status' => 'approved', // Por defecto aprobados
            'is_active' => true,
            'is_verified' => $this->faker->boolean(70), // 70% de probabilidad de estar verificado
        ];
    }
}