<?php

namespace App\Providers;

use App\Models\Booking;
use App\Observers\BookingObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        Booking::observe(BookingObserver::class);
          if (config('app.env') === 'production') {
        URL::forceScheme('https');
          }
    }
}
