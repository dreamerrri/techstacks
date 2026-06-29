<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule HR/Admin notification generation to run daily at 9 AM
Schedule::command('notifications:generate-hr-admin')->dailyAt('09:00');

// Schedule auto clock-out to run every hour
Schedule::command('attendance:auto-clock-out')->hourly();
