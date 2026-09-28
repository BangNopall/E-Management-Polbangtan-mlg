<?php

namespace App\Providers;

use App\Models\Ukm;
use App\Observers\UkmObserver;
use Illuminate\Support\Facades\URL;
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
        Ukm::observe(UkmObserver::class);

        // Pastikan seluruh URL asset(), route(), dan form action digenerate via HTTPS saat di balik SSL Proxy
        if (config('app.env') === 'production' || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
