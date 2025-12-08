<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotographerFactory extends Factory
{
    public function definition(): array
    {
        $region = $this->faker->randomElement(array_keys(\App\Models\Photographer::REGION_COORDINATES));
        return [
            'user_id' => User::factory(), // Crea un usuario automáticamente
            'business_name' => $this->faker->company() . ' Lorem ipsum...',
            'phone' => $this->faker->phoneNumber(),
            'region' => $region,
            'bio' => $this->faker->paragraph(3),

            'website' => 'https://www.' . $this->faker->domainName(),
            'instagram' => $this->faker->userName(), // Solo el usuario (ej: 'juanperez')
            'facebook' => $this->faker->userName(),  // Solo el usuario

            'profile_photo' => null, // O una URL externa
            'banner_photo' => null,
            'status' => 'approved', // Por defecto aprobados
            'is_active' => true,
            'is_verified' => $this->faker->boolean(70), // 70% de probabilidad de estar verificado
        ];
    }
}