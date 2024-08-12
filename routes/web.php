<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('index');

Route::get( '/login',                 [\App\Http\Controllers\LoginController::class, 'create'])->name('login');
Route::post('/login',                 [\App\Http\Controllers\LoginController::class, 'store']);
Route::get( '/forgot-password',       [\App\Http\Controllers\ForgotPasswordController::class, 'create'])->name('password.request');
Route::post('/forgot-password',       [\App\Http\Controllers\ForgotPasswordController::class, 'store'])->name('password.email');
Route::get( '/reset-password/{code}', [\App\Http\Controllers\PasswordResetController::class, 'create'])->name('password.reset');
Route::post('/reset-password/{code}', [\App\Http\Controllers\PasswordResetController::class, 'store'])->name('password.store');
Route::get( '/register',              [\App\Http\Controllers\RegisterController::class, 'create'])->name('register');
Route::post('/register',              [\App\Http\Controllers\RegisterController::class, 'store']);

// Must be authed
Route::middleware(['auth'])->group(function () {
    Route::any( '/home',          [\App\Http\Controllers\HomeController::class, 'home'])->name('home');
    Route::any( '/home/{teamId}', [\App\Http\Controllers\HomeController::class, 'home'])->name('homeByTeam');

    // Games
    Route::get( '/games',              [\App\Http\Controllers\GameController::class, 'index'])->name('games.index');
    Route::post('/games',              [\App\Http\Controllers\GameController::class, 'store'])->name('games.store');
    Route::get( '/games/{id}',         [\App\Http\Controllers\GameController::class, 'show'])->name('games.show');
    Route::get( '/games/{id}/live',    [\App\Http\Controllers\LiveGameController::class, 'index'])->name('games.live');
    Route::get( '/games/{id}/preview', [\App\Http\Controllers\GameController::class, 'preview'])->name('games.preview');

    // Teams
    Route::get( '/teams',      [\App\Http\Controllers\TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams',      [\App\Http\Controllers\TeamController::class, 'store'])->name('teams.store');
    Route::post('/clubs',      [\App\Http\Controllers\ClubController::class, 'store'])->name('clubs.store');
    Route::get( '/teams/{id}', [\App\Http\Controllers\TeamController::class, 'show'])->name('teams.show');

    // Stats
    Route::get( '/stats/teams',            [\App\Http\Controllers\StatsTeamController::class, 'index'])->name('stats.teams.index');
    Route::get( '/stats/teams/{id}',       [\App\Http\Controllers\StatsTeamController::class, 'show'])->name('stats.teams.show');
    Route::get( '/stats/compeitions',      [\App\Http\Controllers\StatsCompetitionController::class, 'index'])->name('stats.competitions.index');
    Route::get( '/stats/compeitions/{id}', [\App\Http\Controllers\StatsCompetitionController::class, 'show'])->name('stats.competitions.show');
    Route::get( '/stats/players',          [\App\Http\Controllers\StatsTeamController::class, 'index'])->name('stats.players.index');

    // Players
    Route::get( '/players',      [\App\Http\Controllers\PlayerController::class, 'index'])->name('players.index');
    Route::get( '/players/{id}', [\App\Http\Controllers\PlayerController::class, 'show'])->name('players.show');

    // Rosters
    Route::get( '/rosters', [\App\Http\Controllers\RosterController::class, 'index'])->name('rosters.index');

    // Seasons
    Route::post('/seasons', [\App\Http\Controllers\SeasonController::class, 'store'])->name('seasons.store');

    // AJAX
    Route::post('/ajax/game/start', [\App\Http\Controllers\AjaxController::class, 'gameStart'])->name('ajax-start-game');
    Route::post('/ajax/game/event', [\App\Http\Controllers\AjaxController::class, 'saveEvent'])->name('ajax-create-event');
    Route::post('/ajax/game/end', [\App\Http\Controllers\AjaxController::class, 'gameEnd'])->name('ajax-end-game');
    Route::post('/ajax/player/position', [\App\Http\Controllers\AjaxController::class, 'savePlayerPosition'])->name('ajax-create-player-position');
    Route::post('/ajax/rosters', [\App\Http\Controllers\AjaxController::class, 'saveRoster'])->name('ajax-create-roster');
});
