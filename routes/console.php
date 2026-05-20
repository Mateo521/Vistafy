<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::command('events:convert-future')
    ->hourly()
    ->withoutOverlapping()  
    ->runInBackground(); 


Schedule::command('logs:clear')
    ->weekly()
    ->sundays()
    ->at('02:00');

