<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

\Illuminate\Support\Facades\Schedule::call(function () {
    \App\Models\Order::where('status', \App\Enums\OrderStatusEnum::PENDING)
         ->where('created_at', '<', \Carbon\Carbon::now()->subDays(7))
         ->update(['status' => \App\Enums\OrderStatusEnum::CANCELLED]);
})->daily()->name('cancel-expired-orders')->withoutOverlapping();

// Gerenciador de Lixo do Storage Cloud e Local
Schedule::command('storage:clean-zips')->dailyAt('02:00');
