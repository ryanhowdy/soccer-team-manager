<?php

namespace App\Providers;

use App\Chart\Chart;
use Illuminate\Support\ServiceProvider;

class ChartServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('chart', function () {
            return new Chart();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
    }
}
