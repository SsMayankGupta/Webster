<?php

use App\Jobs\SendDailyMenuWhatsApp;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('meals:lock-headcounts')->everyMinute();
Schedule::job(new SendDailyMenuWhatsApp)->dailyAt('08:00');
