<?php

namespace Database\Factories;

use App\Models\Photographer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->sentence(3);
        $isPrivate = $this->faker->boolean(30); // 30% de eventos privados

        return [
            'photographer_id' => Photographer::factory(),
            'name' => rtrim($name, '.'),
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'description' => $this->faker->paragraph(),
            'long_description' => $this->faker->text(1000),
            // Usamos imágenes de Picsum para no llenar el disco
            'cover_image' => null, 
            'event_date' => $this->faker->dateTimeBetween('-1 year', '+6 months'),
            'location' => $this->faker->city() . ', ' . $this->faker->state(),
            'is_private' => $isPrivate,
            'private_token' => $isPrivate ? Str::random(32) : null,
            'is_active' => true,
        ];
    }
}