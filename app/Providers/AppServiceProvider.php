<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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

        Blade::withoutDoubleEncoding();
    }
}
