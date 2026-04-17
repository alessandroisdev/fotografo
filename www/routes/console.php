<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::call(function () {
    \App\Models\Order::where('status', \App\Enums\OrderStatusEnum::PENDING)
         ->where('created_at', '<', \Carbon\Carbon::now()->subDays(7))
         ->update(['status' => \App\Enums\OrderStatusEnum::CANCELLED]);
})->daily()->name('cancel-expired-orders')->withoutOverlapping();
