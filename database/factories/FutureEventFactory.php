<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use Illuminate\Support\Str;


class FutureEventFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->sentence(3);

        // Dimensiones para portada (landscape)
        $width = 1280;
        $height = 720;

        $uniqueSeed = Str::random(10) . time() . mt_rand(1, 9999);

        // Generar fecha futura aleatoria (entre 1 y 90 días)
        $eventDate = Carbon::now()->addDays($this->faker->numberBetween(1, 90));

        return [
            'title' => rtrim($name, '.'),
            'description' => $this->faker->paragraph(3),
            'location' => $this->faker->city() . ', ' . $this->faker->state(),
            'event_date' => $eventDate,
            'expiry_date' => $eventDate->copy()->addDays(7),

            //  Usar /seed/ para garantizar imágenes únicas
            'cover_image' => "https://picsum.photos/seed/{$uniqueSeed}/{$width}/{$height}",

            'status' => 'upcoming',
        ];
    }

    /**
     * Estado: Evento próximo (en 3-7 días)
     */
    public function soon(): static
    {
        return $this->state(fn(array $attributes) => [
            'event_date' => Carbon::now()->addDays($this->faker->numberBetween(3, 7)),
        ]);
    }

    /**
     * Estado: Evento lejano (más de 30 días)
     */
    public function distant(): static
    {
        return $this->state(fn(array $attributes) => [
            'event_date' => Carbon::now()->addDays($this->faker->numberBetween(30, 90)),
        ]);
    }

    /**
     * Estado: Evento que ya pasó (para testing de conversión)
     */
    public function past(): static
    {
        return $this->state(fn(array $attributes) => [
            'event_date' => Carbon::now()->subDays($this->faker->numberBetween(1, 5)),
            'status' => 'upcoming', // Mantener como upcoming para que sea convertido
        ]);
    }
}
