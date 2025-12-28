<?php

use App\Jobs\SendDailySalesReport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily sales report to run every evening at 11:30 PM
Schedule::job(new SendDailySalesReport)
    ->dailyAt('23:30')
    ->timezone(config('app.timezone'))
    ->name('daily-sales-report')
    ->onSuccess(function () {
        \Log::info('Daily sales report sent successfully');
    })
    ->onFailure(function () {
        \Log::error('Daily sales report failed to send');
    });

