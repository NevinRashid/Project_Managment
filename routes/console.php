<?php

use App\Jobs\ProjectOverdueJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command('app:update-overdue-tasks')->daily();
Schedule::job(new ProjectOverdueJob)->daily();
