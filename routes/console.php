<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Comando de inspiración por defecto de Laravel
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

//  Conversión automática de eventos futuros (cada hora)
Schedule::command('events:convert-future')
    ->hourly()
    ->withoutOverlapping() // Evitar ejecuciones simultáneas
    ->runInBackground(); // Ejecutar en segundo plano

// 🧹 Opcional: Limpiar logs antiguos (cada semana)
Schedule::command('logs:clear')
    ->weekly()
    ->sundays()
    ->at('02:00');

// 📧 Opcional: Recordatorio a fotógrafos sobre eventos próximos (cada día)
// Schedule::command('events:notify-photographers')->daily()->at('09:00');
