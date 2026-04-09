<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send deadline reminders, overdue alerts, budget alerts, low-stock and invoice overdue notices
Schedule::command('notifications:send')->daily();

// Prune old activity logs (keep 90 days)
Schedule::command('logs:prune')->daily();
