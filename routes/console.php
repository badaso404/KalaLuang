<?php

use App\Models\Visit;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('vacancies:expire')
    ->dailyAt('01:00')
    ->withoutOverlapping();

Schedule::command('model:prune', ['--model' => [Visit::class]])
    ->dailyAt('01:30')
    ->withoutOverlapping();
