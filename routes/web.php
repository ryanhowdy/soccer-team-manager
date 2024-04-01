<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegisterController;

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

Route::get('/', [HomeController::class, 'index'])->name('index');

Route::get( '/login',                 [LoginController::class, 'create'])->name('login');
Route::post('/login',                 [LoginController::class, 'store']);
Route::get( '/forgot-password',       [ForgotPasswordController::class, 'create'])->name('password.request');
Route::post('/forgot-password',       [ForgotPasswordController::class, 'store'])->name('password.email');
Route::get( '/reset-password/{code}', [PasswordResetController::class, 'create'])->name('password.reset');
Route::post('/reset-password/{code}', [PasswordResetController::class, 'store'])->name('password.store');
Route::get( '/register',              [RegisterController::class, 'create'])->name('register');
Route::post('/register',              [RegisterController::class, 'store']);

// Must be authed
Route::middleware(['auth'])->group(function () {
    Route::any( '/home',   [HomeController::class, 'home'])->name('home');
});
