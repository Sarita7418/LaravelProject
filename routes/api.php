<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;

use App\Http\Controllers\AutenticacionDosPasosController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\RolPermisoController;
use App\Http\Controllers\Crud\RolCrudController;
use App\Http\Controllers\Crud\UsuarioCrudController;

Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    SubstituteBindings::class,
    ThrottleRequests::class,
])->group(function () {

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest');

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $user->load('role.permisos.url');

        $permisos = $user->role ? $user->role->permisos->map(function ($permiso) {
            return [
                'item' => $permiso->item,
                'ruta' => optional($permiso->url)->ruta,
            ];
        }) : collect([]);

        $codigo2fa = $user->codigoVerificacion()->where('habilitado', true)->first();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'dos_pasos_habilitado' => $codigo2fa ? true : false,
            'rol' => $user->role ? $user->role->descripcion : null,
            'permisos' => $permisos,
        ]);
    })->middleware('auth:sanctum');

    Route::get('/accesos', function (Request $request) {
        $user = $request->user();

        $menuItems = $user->role->permisos()->with('url')->get();

        $accesos = $menuItems->map(function ($item) {
            return [
                'item' => $item->item,
                'nivel' => $item->nivel,
                'ruta' => optional($item->url)->ruta,
            ];
        });

        return response()->json([
            'rol' => $user->role->descripcion,
            'accesos' => $accesos,
        ]);
    })->middleware('auth:sanctum');

    Route::get('/menu-items', function () {
        $menuItems = \App\Models\MenuItem::with(['hijos.url', 'url'])->whereNull('id_padre')->get();
        return response()->json($menuItems);
    })->middleware('auth:sanctum');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');

    Route::post('/dos-pasos/enviar-codigo', [AutenticacionDosPasosController::class, 'enviarCodigo'])->middleware('auth:sanctum');
    Route::post('/dos-pasos/verificar-codigo', [AutenticacionDosPasosController::class, 'verificarCodigo'])->middleware('auth:sanctum');
    Route::post('/dos-pasos/habilitar', [AutenticacionDosPasosController::class, 'habilitarDosPasos'])->middleware('auth:sanctum');
    Route::post('/dos-pasos/deshabilitar', [AutenticacionDosPasosController::class, 'deshabilitarDosPasos'])->middleware('auth:sanctum');

    Route::get('/roles', [RolPermisoController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/roles', [RolPermisoController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/permisos', [RolPermisoController::class, 'permisos'])->middleware('auth:sanctum');
    Route::post('/roles/{id}/permisos', [RolPermisoController::class, 'asignarPermisos'])->middleware('auth:sanctum');

    Route::get('/roles', [RolCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/roles', [RolCrudController::class, 'store'])->middleware('auth:sanctum');
    Route::delete('/roles/{id}', [RolCrudController::class, 'destroy'])->middleware('auth:sanctum');
    Route::put('/roles/{id}', [RolCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/roles/inactivos', [RolCrudController::class, 'inactivos'])->middleware('auth:sanctum');
    Route::put('/roles/{id}/reactivar', [RolCrudController::class, 'reactivar'])->middleware('auth:sanctum');

    Route::get('/usuarios', [UsuarioCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/usuarios', [UsuarioCrudController::class, 'store'])->middleware('auth:sanctum');
    Route::delete('/usuarios/{id}', [UsuarioCrudController::class, 'destroy'])->middleware('auth:sanctum');
    Route::put('/usuarios/{id}', [UsuarioCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/usuarios/roles', [UsuarioCrudController::class, 'getRoles'])->middleware('auth:sanctum');
    Route::get('/usuarios/inactivos', [UsuarioCrudController::class, 'inactivos'])->middleware('auth:sanctum');
    Route::put('/usuarios/{id}/reactivar', [UsuarioCrudController::class, 'reactivar'])->middleware('auth:sanctum');

});
