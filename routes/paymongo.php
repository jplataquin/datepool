<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('primary/webhook', function () {
    return 'Hello World';
});

Route::post('secondary/webhook/{id}', function () {
    return 'Hello World';
});