<?php

namespace App\Providers;

use App\Models\Dispatch;
use App\Models\Occurrence;
use App\Observers\DispatchObserver;
use App\Observers\OccurrenceObserver;
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
        Occurrence::observe(OccurrenceObserver::class);
        Dispatch::observe(DispatchObserver::class);
    }
}
