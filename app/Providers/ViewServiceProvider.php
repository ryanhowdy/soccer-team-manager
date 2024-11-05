<?php
 
namespace App\Providers;
 
use App\View\Composers\ProfileComposer;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use App\Models\Player;
 
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ...
    }
 
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add managed players to the navigation
        Facades\View::composer('partials.navigation', function (View $view) {
            $managedPlayers = Player::where('managed', 1)
                ->get();

            $view->with('navPlayers', $managedPlayers); 
        });
    }
}
