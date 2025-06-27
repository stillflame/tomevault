<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Models\Tome;
use App\Models\Character;

class RouteBindingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::bind('tome', function ($value) {
            return Tome::where('slug', $value)
                ->orWhere('id', $value)
                ->firstOrFail();
        });

        Route::bind('character', function ($value) {
            return Character::where('slug', $value)
                ->orWhere('id', $value)
                ->firstOrFail();
        });
    }
}
