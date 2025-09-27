<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpotifyUserController;

Route::get('/', function () {
    return redirect()->route('spotify.index');
});

Route::get('/dashboard', [SpotifyUserController::class, 'dashboard'])->name('spotify.dashboard');

Route::prefix('spotify-users')->name('spotify.')->group(function () {
    Route::get('/', [SpotifyUserController::class, 'index'])->name('index');
    Route::post('/preview', [SpotifyUserController::class, 'preview'])->name('preview');
    Route::post('/store', [SpotifyUserController::class, 'store'])->name('store');
    Route::get('/export', [SpotifyUserController::class, 'export'])->name('export');
    Route::get('/debug', [SpotifyUserController::class, 'debug'])->name('debug');
    
});