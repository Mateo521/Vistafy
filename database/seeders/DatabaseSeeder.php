<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Photographer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
       
        User::create([
            'name' => 'Admin f33',
            'email' => 'admin@f33.click',
            'password' => Hash::make('admin123'),  
            'role' => 'admin',
            'is_admin' => true,
        ]);

   
        $fotoUser = User::create([
            'name' => 'Juan Fotógrafo',
            'email' => 'foto@f33.click',
            'password' => Hash::make('foto123'),
            'role' => 'photographer',
            'is_admin' => false,
        ]);

       
        Photographer::create([
            'user_id' => $fotoUser->id,
            'business_name' => 'Estudio Pro',
            'slug' => 'estudio-pro',
            'status' => 'approved',  
            'is_active' => true,
            'is_verified' => true,
            'region' => 'San Luis',
        ]);

     
        User::create([
            'name' => 'Pedro Cliente',
            'email' => 'cliente@f33.click',
            'password' => Hash::make('cliente123'),
            'role' => 'client',
            'is_admin' => false,
        ]);

        $this->command->info('Base de datos reseteada: 1 Admin, 1 Fotógrafo y 1 Cliente creados.');
    }
}