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
        //Pista de auditoria
        CodigoVerificacion::observe(PistaAuditoriaObserver::class);
        Role::observe(PistaAuditoriaObserver::class);
        User::observe(PistaAuditoriaObserver::class);

        //Añadir mas modelos a desarrollar y observar para auditar
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
