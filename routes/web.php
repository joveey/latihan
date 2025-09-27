<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpotifyUserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/spotify-users', [SpotifyUserController::class, 'index'])->name('spotify.index');
Route::post('/spotify-users/preview', [SpotifyUserController::class, 'preview'])->name('spotify.preview');
Route::post('/spotify-users/store', [SpotifyUserController::class, 'store'])->name('spotify.store');
Route::get('/spotify-users/export', [SpotifyUserController::class, 'export'])->name('spotify.export');