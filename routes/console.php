<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nexteck Automated Follow-Up Sequences Scheduler
Schedule::command('nexteck:process-followups')
    ->everyThirtyMinutes()
    ->weekdays()
    ->withoutOverlapping();

// Nexteck Social Media Publishing Scheduler
Schedule::command('nexteck:publish-social')
    ->everyFiveMinutes()
    ->withoutOverlapping();
