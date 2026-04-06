<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('PROD_ADMIN_EMAIL', 'admin@f33.click');

        
        if (!User::where('email', $adminEmail)->exists()) {
            User::create([
                'name' => 'Administrador',
                'email' => $adminEmail,
                'password' => Hash::make(env('PROD_ADMIN_PASSWORD', 'tu_clave_secreta_aqui')),
                'role' => 'admin',
                'is_admin' => true,
            ]);
            
            $this->command->info('Usuario administrador creado con éxito.');
        } else {
            $this->command->info(' El usuario administrador ya existe en la base de datos.');
        }
    }
}