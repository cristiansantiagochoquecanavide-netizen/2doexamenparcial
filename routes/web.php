<?php

use Illuminate\Support\Facades\Route;

// Servir la aplicación React para todas las rutas web
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
