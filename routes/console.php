<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cadangan otomatis harian pukul 01.00 (butuh cron / Task Scheduler menjalankan `php artisan schedule:run` tiap menit).
Schedule::command('sikaset:backup')->dailyAt('01:00')->withoutOverlapping()->onOneServer();
