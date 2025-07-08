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

use App\Http\Controllers\Crud\PersonaCrudController;


use App\Models\MenuItem;

use App\Models\Permiso;


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
           $user = $request->user()->load('role.permisos.menuItem.url');

           $permisos = $user->role->permisos->map(function ($permiso) {
               return [
                   'item' => optional($permiso->menuItem)->item,
                   'ruta' => optional($permiso->menuItem?->url)->ruta,
                   'componente' => optional($permiso->menuItem?->url)->componente,
               ];
           });

           return [
               'id' => $user->id,
               'name' => $user->name,
               'email' => $user->email,
               'dos_pasos_habilitado' => $user->dos_pasos_habilitado,
               'rol' => $user->role->descripcion,
               'permisos' => $permisos,
           ];
       })->middleware('auth:sanctum');

       // ✅ NUEVO: Endpoint para obtener rutas reales (URL) accesibles para el usuario autenticado
       Route::get('/accesos', function (Request $request) {
           $user = $request->user();

           $permisos = $user->role->permisos()->with('menuItem.url')->get();

           $accesos = $permisos->map(function ($permiso) {
               return [
                   'item' => optional($permiso->menuItem)->item,
                   'nivel' => optional($permiso->menuItem)->nivel,
                   'ruta' => optional($permiso->menuItem?->url)->ruta,
               ];
           });

           return response()->json([
               'rol' => $user->role->descripcion,
               'accesos' => $accesos,
           ]);
       })->middleware('auth:sanctum');

       // ✅ Endpoint para construir menú jerárquico filtrado por permisos del rol
            Route::get('/menu-items', function (Request $request) {
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        if (!$user->role) {
            return response()->json(['error' => 'Usuario sin rol'], 400);
        }

        $permisosIds = DB::table('permiso_rol')
            ->where('rol_id', $user->role->id)
            ->pluck('permiso_id');

        $permisos = Permiso::whereIn('id', $permisosIds)
            ->whereHas('menuItem', fn ($q) => $q->whereNull('id_padre'))
            ->with(['menuItem.hijosRecursive', 'menuItem.url'])
            ->get();

        $menuItemsPermitidos = $permisos->pluck('menuItem');

        return response()->json($menuItemsPermitidos);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ], 500);
    }
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

    Route::get('/personas', [PersonaCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/personas', [PersonaCrudController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/personas/{id}', [PersonaCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/personas/{id}', [PersonaCrudController::class, 'destroy'])->middleware('auth:sanctum');
    Route::get('/personas/inactivos', [PersonaCrudController::class, 'inactivos'])->middleware('auth:sanctum');
    Route::put('/personas/{id}/reactivar', [PersonaCrudController::class, 'reactivar'])->middleware('auth:sanctum');


    Route::get('/usuarios', [UsuarioCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/usuarios', [UsuarioCrudController::class, 'store'])->middleware('auth:sanctum');
    Route::delete('/usuarios/{id}', [UsuarioCrudController::class, 'destroy'])->middleware('auth:sanctum');
    Route::put('/usuarios/{id}', [UsuarioCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/usuarios/roles', [UsuarioCrudController::class, 'getRoles'])->middleware('auth:sanctum');
    Route::get('/usuarios/inactivos', [UsuarioCrudController::class, 'inactivos'])->middleware('auth:sanctum');
    Route::put('/usuarios/{id}/reactivar', [UsuarioCrudController::class, 'reactivar'])->middleware('auth:sanctum');

});
