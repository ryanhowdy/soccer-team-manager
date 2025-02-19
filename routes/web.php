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
    Route::get( '/games/{id}/edit',    [\App\Http\Controllers\GameController::class, 'edit'])->name('games.edit');
    Route::post('/games/{id}',         [\App\Http\Controllers\GameController::class, 'update'])->name('games.update');

    // Teams
    Route::get( '/teams',           [\App\Http\Controllers\TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams',           [\App\Http\Controllers\TeamController::class, 'store'])->name('teams.store');
    Route::get( '/teams/{id}',      [\App\Http\Controllers\TeamController::class, 'show'])->name('teams.show');
    Route::get( '/teams/{id}/edit', [\App\Http\Controllers\TeamController::class, 'edit'])->name('teams.edit');
    Route::post('/teams/{id}/edit', [\App\Http\Controllers\TeamController::class, 'update'])->name('teams.update');

    // Clubs
    Route::post('/clubs',             [\App\Http\Controllers\ClubController::class, 'store'])->name('clubs.store');
    Route::get( '/clubs/{club}/edit', [\App\Http\Controllers\ClubController::class, 'edit'])->name('clubs.edit');
    Route::post('/clubs/{club}/edit', [\App\Http\Controllers\ClubController::class, 'update'])->name('clubs.update');

    // Stats
    Route::get( '/stats/teams',   [\App\Http\Controllers\StatsTeamController::class, 'index'])->name('stats.teams.index');
    Route::get( '/stats/players', [\App\Http\Controllers\StatsTeamController::class, 'index'])->name('stats.players.index');

    // Competitions
    Route::get( '/compeitions',               [\App\Http\Controllers\CompetitionController::class, 'index'])->name('competitions.index');
    Route::post('/compeitions',               [\App\Http\Controllers\CompetitionController::class, 'store'])->name('competitions.store');
    Route::get( '/compeitions/{competition}', [\App\Http\Controllers\CompetitionController::class, 'show'])->name('competitions.show');

    // Players
    Route::get( '/players',                           [\App\Http\Controllers\PlayerController::class, 'index'])->name('players.index');
    Route::post('/players',                           [\App\Http\Controllers\PlayerController::class, 'store'])->name('players.store');
    Route::get( '/players/{player}',                  [\App\Http\Controllers\PlayerController::class, 'show'])->name('players.show');
    Route::get( '/players/{player}/seasons/{season}', [\App\Http\Controllers\PlayerController::class, 'seasonShow'])->name('players.seasons.show');
    Route::get( '/players/{player}/edit',             [\App\Http\Controllers\PlayerController::class, 'edit'])->name('players.edit');
    Route::post('/players/{player}/edit',             [\App\Http\Controllers\PlayerController::class, 'update'])->name('players.update');

    // Rosters
    Route::get( '/rosters',          [\App\Http\Controllers\RosterController::class, 'index'])->name('rosters.index');
    Route::post('/rosters/{roster}', [\App\Http\Controllers\RosterController::class, 'update'])->name('rosters.update');

    // Seasons
    Route::post('/seasons', [\App\Http\Controllers\SeasonController::class, 'store'])->name('seasons.store');

    // Locations
    Route::get( '/locations', [\App\Http\Controllers\LocationController::class, 'index'])->name('locations.index');
    Route::post('/locations', [\App\Http\Controllers\LocationController::class, 'store'])->name('locations.store');

    // Formations
    Route::get( '/formations', [\App\Http\Controllers\FormationController::class, 'index'])->name('formations.index');
    Route::post('/formations', [\App\Http\Controllers\FormationController::class, 'store'])->name('formations.store');

    // AJAX
    // TODO - these are all wrong
    Route::post('/ajax/game/start',      [\App\Http\Controllers\AjaxController::class, 'gameStart'])->name('ajax-start-game');
    Route::post('/ajax/game/event',      [\App\Http\Controllers\AjaxController::class, 'saveEvent'])->name('ajax-create-event');
    Route::post('/ajax/game/end',        [\App\Http\Controllers\AjaxController::class, 'gameEnd'])->name('ajax-end-game');
    Route::post('/ajax/player/position', [\App\Http\Controllers\AjaxController::class, 'savePlayerPosition'])->name('ajax-create-player-position');
    Route::post('/ajax/rosters',         [\App\Http\Controllers\AjaxController::class, 'saveRoster'])->name('ajax-create-roster');
    // TODO - these are all wrong

    Route::post('/ajax/rosters/{roster}/destroy', [\App\Http\Controllers\Ajax\RosterController::class, 'destroy'])->name('ajax.rosters.destroy');

    Route::post('/ajax/games/{result}', [\App\Http\Controllers\Ajax\ResultController::class, 'update'])->name('ajax.results.update');

    Route::post('/ajax/competitions/{competition}', [\App\Http\Controllers\Ajax\CompetitionController::class, 'update'])->name('ajax.competitions.update');
});
