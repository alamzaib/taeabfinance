<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule notification sending every 2 minutes
Schedule::command('notifications:send --limit=10')
    ->everyTwoMinutes()
    ->withoutOverlapping()
    ->runInBackground();
