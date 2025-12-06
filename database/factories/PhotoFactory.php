<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Photographer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PhotoFactory extends Factory
{
    public function definition(): array
    {
        // Generamos dimensiones aleatorias para realismo
        $width = $this->faker->numberBetween(800, 1920);
        $height = $this->faker->numberBetween(600, 1080);
        
        // URL de imagen aleatoria
        $imageUrl = "https://picsum.photos/{$width}/{$height}?random=" . mt_rand(1, 50000);

        return [
            'photographer_id' => Photographer::factory(),
            'event_id' => Event::factory(),
            'unique_id' => strtoupper(Str::random(10)),
            'title' => 'IMG_' . $this->faker->numberBetween(1000, 9999),
            'description' => $this->faker->sentence(),
            // Al poner http, tu modelo Photo usará esta URL en lugar de buscar en storage
            'original_path' => $imageUrl, 
            'watermarked_path' => $imageUrl, // En seeding simulamos que es la misma
            'thumbnail_path' => $imageUrl,
            'original_name' => 'foto_demo.jpg',
            'file_size' => $this->faker->numberBetween(1000000, 5000000), // 1MB - 5MB
            'width' => $width,
            'height' => $height,
            'mime_type' => 'image/jpeg',
            'price' => $this->faker->randomFloat(2, 5, 50), // Precio entre 5 y 50
            'is_active' => true,
            'downloads' => $this->faker->numberBetween(0, 100),
            'views' => $this->faker->numberBetween(10, 500),
        ];
    }
}