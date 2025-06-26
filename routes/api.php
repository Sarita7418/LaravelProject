<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;

// 👇 Importa controladores AQUÍ, antes de la función
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

use App\Http\Controllers\Admin\RolPermisoController;

Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    SubstituteBindings::class,
    ThrottleRequests::class,
])
->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest');

    // ✅ Endpoint que devuelve los datos del usuario logueado, incluyendo rol y permisos
    Route::get('/user', function (Request $request) {
        $user = $request->user()->load('role.permisos'); // Asegúrate de tener estas relaciones en el modelo User
        $permisos = $user->role->permisos->pluck('nombre');

        return [
            'id' => $user->id,
            'nombre' => $user->name,
            'email' => $user->email,
            'rol' => $user->role->descripcion, // ← esto depende de cómo nombraste el campo en tu tabla roles
            'permisos' => $permisos,
        ];
    })->middleware('auth:sanctum');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth');

    // 👇 Rutas protegidas para gestión de roles y permisos
    Route::get('/roles', [RolPermisoController::class, 'index']);
    Route::post('/roles', [RolPermisoController::class, 'store']);
    Route::get('/permisos', [RolPermisoController::class, 'permisos']);
    Route::post('/roles/{id}/permisos', [RolPermisoController::class, 'asignarPermisos']);
});
