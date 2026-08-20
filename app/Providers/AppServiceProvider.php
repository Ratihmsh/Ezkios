<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Blade directive untuk permission
        Blade::if('permission', function ($permission) {
            if (!Auth::check()) {
                return false;
            }
            return Auth::user()->hasPermission($permission);
        });
    }
}
