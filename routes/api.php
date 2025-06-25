<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\Crud\RolCrudController;

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

        Route::get('/user', function (Request $request) {
            return $request->user()->load('role');
        })->middleware('auth:sanctum');

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth');

        // 👇 Rutas protegidas para gestión de roles y permisos
        Route::get('/roles', [RolPermisoController::class, 'index']);
        Route::post('/roles', [RolPermisoController::class, 'store']);
        Route::get('/permisos', [RolPermisoController::class, 'permisos']);
        Route::post('/roles/{id}/permisos', [RolPermisoController::class, 'asignarPermisos']);


        //Roles Crud
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/roles', [RolCrudController::class, 'index']);
            Route::post('/roles', [RolCrudController::class, 'store']);
            Route::delete('/roles/{id}', [RolCrudController::class, 'destroy']);
        });

        //Usuarios Crud


    });
