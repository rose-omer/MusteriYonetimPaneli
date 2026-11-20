<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

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
        // Tarih ve sayı biçimi uygulama diline uyar (varsayılan: tr).
        Carbon::setLocale(config('app.locale'));
        Number::useLocale(config('app.locale'));
    }
}
