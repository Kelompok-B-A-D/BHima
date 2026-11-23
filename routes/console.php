<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal pengecekan ulang status peraturan JDIH (berlaku/diubah/dicabut) setiap bulan
Schedule::command('bhima:check-jdih-status')->monthly();
