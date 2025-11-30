<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Usar updateOrCreate para evitar duplicados
        $admin = User::updateOrCreate(
            ['email' => 'admin@photomarket.com'], // Buscar por email
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'role' => 'client',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info($admin->wasRecentlyCreated 
            ? '✅ Administrador creado correctamente' 
            : '✅ Administrador actualizado correctamente'
        );

        // Admin de prueba
        $testAdmin = User::updateOrCreate(
            ['email' => 'test@admin.com'],
            [
                'name' => 'Admin Test',
                'password' => Hash::make('password'),
                'role' => 'client',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info($testAdmin->wasRecentlyCreated 
            ? '✅ Admin Test creado correctamente' 
            : '✅ Admin Test actualizado correctamente'
        );

        $this->command->line('');
        $this->command->info('📧 Email: admin@photomarket.com | Password: admin123');
        $this->command->info('📧 Email: test@admin.com | Password: password');
    }
}
