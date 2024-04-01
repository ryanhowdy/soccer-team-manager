<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        \Laravel\Sanctum\Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add a macro for carbon to display the user timezone
        \Carbon\Carbon::macro('inUserTimezone', function () {
            return $this->tz(config('stm.timezone_display'));
        });
    }
}
