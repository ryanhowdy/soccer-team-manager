<?php
 
namespace App\Providers;
 
use App\View\Composers\ProfileComposer;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use App\Models\ManagedPlayer;
 
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
            $managedPlayers = ManagedPlayer::where('user_id', Auth()->user()->id)
                ->with('player')
                ->get();

            $view->with('navPlayers', $managedPlayers); 
        });
    }
}
