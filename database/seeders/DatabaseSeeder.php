<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,          //  AGREGAR PRIMERO (para tener admin antes que fotógrafos)
            PhotographerSeeder::class,
            // EventSeeder::class,
            // PhotoSeeder::class,
        ]);
    }
}
