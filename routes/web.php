<?php

use Illuminate\Support\Facades\Route;

Route::get('/', static fn () => response()->json([
    'name' => 'TomeVault API',
    'status' => '🪄 Ready to conjure knowledge',
]));
