<?php

use App\Http\Controllers\SecretController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/secret', [SecretController::class, 'store']); //Titok tárolása
Route::get('/secret/{hash}', [SecretController::class, 'show']); //Titok visszakérése