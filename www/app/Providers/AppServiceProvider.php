<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::all();
                foreach ($settings as $setting) {
                    Config::set('settings.' . $setting->key, $setting->value);
                }
            }
        } catch (\Exception $e) {
            // Ignorado durante migrações ou builds assíncronos iniciais
        }
    }
}
