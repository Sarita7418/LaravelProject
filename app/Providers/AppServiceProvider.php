<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Models\CodigoVerificacion;
use App\Models\MenuItem;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\Url;
use App\Models\User;
use App\Observers\PistaAuditoriaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
    }
}