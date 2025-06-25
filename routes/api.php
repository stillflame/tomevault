<?php

use App\Http\Controllers\TomeController;
use Illuminate\Support\Facades\Route;


Route::apiResource('tomes', TomeController::class)->only(['index', 'show']); // public routes

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tomes', TomeController::class)->except(['index', 'show']); // protected routes
});

Route::prefix('v1')->group(function () {
    Route::apiResource('tomes', TomeController::class);
//    Route::apiResource('spells', SpellController::class);
//    Route::apiResource('characters', CharacterController::class);
});
