<?php

use Illuminate\Support\Facades\Route;

// Serve the Vue SPA for all routes except API
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
