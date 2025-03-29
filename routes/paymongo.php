<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/primary/callback', [App\Http\Controllers\PaymongoTestController::class, 'callback']);
Route::post('/test', [App\Http\Controllers\PaymongoTestController::class, 'test']);

Route::post('/secondary/webhook/{id}', function () {
    return 'Hello World';
});