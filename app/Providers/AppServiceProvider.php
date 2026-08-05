<?php

namespace App\Providers;

use App\Models\Ukm;
use App\Observers\UkmObserver;
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
    }
}
