<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\Crud\RolCrudController;
use App\Http\Controllers\Crud\UsuarioCrudController;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AutenticacionDosPasosController;

use App\Http\Controllers\Admin\RolPermisoController;
use App\Models\MenuItem;

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
           $user = $request->user();

           $menuItemsPermitidos = $user->role->permisos()
               ->with('menuItem.hijosRecursive.url', 'menuItem.url')
               ->get()
               ->pluck('menuItem')
               ->filter() // eliminar nulls por si hay permisos sin menuItem
               ->whereNull('id_padre')
               ->values();

           return response()->json($menuItemsPermitidos);
       })->middleware('auth:sanctum');

       Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth');

       // 👇 Rutas protegidas para gestión de dos pasos
       Route::post('/dos-pasos/enviar-codigo', [AutenticacionDosPasosController::class, 'enviarCodigo']);
       Route::post('/dos-pasos/verificar-codigo', [AutenticacionDosPasosController::class, 'verificarCodigo']);
       Route::post('/dos-pasos/habilitar', [AutenticacionDosPasosController::class, 'habilitarDosPasos']);
       Route::post('/dos-pasos/deshabilitar', [AutenticacionDosPasosController::class, 'deshabilitarDosPasos']);

       // 👇 Rutas protegidas para gestión de roles y permisos
       Route::get('/roles', [RolPermisoController::class, 'index']);
       Route::post('/roles', [RolPermisoController::class, 'store']);
       Route::get('/permisos', [RolPermisoController::class, 'permisos']);
       Route::post('/roles/{id}/permisos', [RolPermisoController::class, 'asignarPermisos']);

       //Roles Crud
       Route::get('/roles', [RolCrudController::class, 'index']);
       Route::post('/roles', [RolCrudController::class, 'store']);
       Route::delete('/roles/{id}', [RolCrudController::class, 'destroy']);
       Route::put('/roles/{id}', [RolCrudController::class, 'update']);

       //Usuarios Crud
       Route::get('/usuarios', [UsuarioCrudController::class, 'index']);
       Route::post('/usuarios', [UsuarioCrudController::class, 'store']);
       Route::delete('/usuarios/{id}', [UsuarioCrudController::class, 'destroy']);
       Route::put('/usuarios/{id}', [UsuarioCrudController::class, 'update']);
       Route::get('/usuarios/roles', [UsuarioCrudController::class, 'getRoles']);
   });
