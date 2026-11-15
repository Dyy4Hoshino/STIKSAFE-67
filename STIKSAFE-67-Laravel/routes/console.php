<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('scans:watchdog')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('scans:purge-expired-files')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('idempotency:prune')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
