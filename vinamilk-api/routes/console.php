<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule daily report at 8:00 AM every day
Schedule::command('report:daily')
    ->dailyAt('08:00')
    ->description('Send daily report to admins');

// Inventory management schedules
Schedule::command('inventory:release-expired')
    ->everyMinute()
    ->description('Release expired stock reservations');

Schedule::command('inventory:check-low-stock')
    ->hourly()
    ->description('Check for low stock and out of stock items');
