<?php

use Illuminate\Support\Facades\Route;



Route::get('/google/redirect', [App\Http\Controllers\Login\GoogleLoginController::class, 'redirect'])->name('google.redirect');
Route::get('/google/callback', [App\Http\Controllers\Login\GoogleLoginController::class, 'handleCallback'])->name('google.callback');

Route::get('/facebook/redirect', [App\Http\Controllers\Login\FacebookLoginController::class, 'redirect'])->name('facebook.redirect');
Route::get('/facebook/callback', [App\Http\Controllers\Login\FacebookLoginController::class, 'handleCallback'])->name('facebook.callback');


Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

