<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\AutenticacionDosPasosController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Crud\RolCrudController;
use App\Http\Controllers\Crud\UsuarioCrudController;
use App\Http\Controllers\ProtocoloController;
use App\Http\Controllers\Crud\PersonaCrudController;
use App\Http\Controllers\Crud\PlanCuentasCrudController;
use App\Http\Controllers\Navegacion\NavegacionController;
use App\Http\Controllers\Auth\NewPasswordController;

use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReportePDFController;



Route::post('/personas', [PersonaCrudController::class, 'store']);

Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    SubstituteBindings::class,
    ThrottleRequests::class,
])->group(function () {

    
    Route::get('/reportes/usuarios/excel', [ReporteController::class, 'exportUsuarios']);
    Route::get('/reportes/usuarios/pdf', [ReportePDFController::class, 'usuarios']);


    // Autenticación y permisos
    Route::get('/user', [NavegacionController::class, 'obtenerUsuarioConPermisos'])->middleware('auth:sanctum');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');

    Route::post('/dos-pasos/enviar-codigo', [AutenticacionDosPasosController::class, 'enviarCodigo']);
    Route::post('/dos-pasos/verificar-codigo', [AutenticacionDosPasosController::class, 'verificarCodigo']);
    Route::post('/dos-pasos/habilitar', [AutenticacionDosPasosController::class, 'habilitarDosPasos']);
    Route::post('/dos-pasos/deshabilitar', [AutenticacionDosPasosController::class, 'deshabilitarDosPasos']);

    Route::post('/reset-password/enviar-codigo', [NewPasswordController::class, 'enviarCodigoReset']);
    Route::post('/reset-password/verificar-codigo', [NewPasswordController::class, 'verificarCodigoReset']);
    Route::post('/reset-password', [NewPasswordController::class, 'resetPassword']);
    
    // Roles
    Route::get('/roles', [RolCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/roles', [RolCrudController::class, 'store'])->middleware('auth:sanctum');
    Route::delete('/roles/{id}', [RolCrudController::class, 'destroy'])->middleware('auth:sanctum');
    Route::put('/roles/{id}', [RolCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/roles/inactivos', [RolCrudController::class, 'inactivos'])->middleware('auth:sanctum');
    Route::put('/roles/{id}/reactivar', [RolCrudController::class, 'reactivar'])->middleware('auth:sanctum');

    Route::get('/roles/menus-acciones', [RolCrudController::class, 'todosLosMenusYAcciones'])->middleware('auth:sanctum');
    Route::get('/roles/{idRol}/menus', [RolCrudController::class, 'menusDeRol'])->middleware('auth:sanctum');
    Route::get('/roles/{idRol}/acciones', [RolCrudController::class, 'accionesDeRol'])->middleware('auth:sanctum');
    Route::put('/roles/{idRol}/menus', [RolCrudController::class, 'actualizarMenusDeRol'])->middleware('auth:sanctum');
    Route::put('/roles/{idRol}/acciones', [RolCrudController::class, 'actualizarAccionesDeRol'])->middleware('auth:sanctum');

    // Personas
    Route::get('/personas', [PersonaCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::put('/personas/{id}', [PersonaCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/personas/{id}', [PersonaCrudController::class, 'destroy'])->middleware('auth:sanctum');
    Route::get('/personas/inactivos', [PersonaCrudController::class, 'inactivos'])->middleware('auth:sanctum');
    Route::put('/personas/{id}/reactivar', [PersonaCrudController::class, 'reactivar'])->middleware('auth:sanctum');

    // Usuarios
    Route::get('/usuarios', [UsuarioCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/usuarios', [UsuarioCrudController::class, 'store'])->middleware('auth:sanctum');
    Route::delete('/usuarios/{id}', [UsuarioCrudController::class, 'destroy'])->middleware('auth:sanctum');
    Route::put('/usuarios/{id}', [UsuarioCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/usuarios/roles', [UsuarioCrudController::class, 'getRoles'])->middleware('auth:sanctum');
    Route::get('/usuarios/inactivos', [UsuarioCrudController::class, 'inactivos'])->middleware('auth:sanctum');
    Route::put('/usuarios/{id}/reactivar', [UsuarioCrudController::class, 'reactivar'])->middleware('auth:sanctum');
    Route::get('/usuario/verificar-username', [UsuarioCrudController::class, 'verificarUsername']);

    // Protocolos
    Route::get('/protocolos', [ProtocoloController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/protocolos/catalogos', [ProtocoloController::class, 'catalogos'])->middleware('auth:sanctum');
    Route::get('/protocolos/estado/{estado}', [ProtocoloController::class, 'porEstado'])->middleware('auth:sanctum');
    Route::post('/protocolos', [ProtocoloController::class, 'store'])->middleware('auth:sanctum');
    Route::delete('/protocolos/{id}', [ProtocoloController::class, 'destroy'])->middleware('auth:sanctum');
    Route::put('/protocolos/{id}', [ProtocoloController::class, 'update'])->middleware('auth:sanctum');
    Route::put('/protocolos/{id}/reactivar', [ProtocoloController::class, 'reactivar'])->middleware('auth:sanctum');
    Route::put('/protocolos/{id}/archivar', [ProtocoloController::class, 'archivar'])->middleware('auth:sanctum');

    // Navegación
    Route::get('/menu/{id_usuario}', [NavegacionController::class, 'obtenerMenu'])->middleware('auth:sanctum');
    Route::get('/acciones/{id_usuario}', [NavegacionController::class, 'obtenerAcciones'])->middleware('auth:sanctum');

    // Plan cuentas - RUTAS CORREGIDAS (ORDEN IMPORTANTE)
    // PRIMERO: Rutas específicas (más específicas primero)
    Route::get('/plan-cuentas/todas', [PlanCuentasCrudController::class, 'indexTodas']);
    Route::get('/plan-cuentas/inactivas', [PlanCuentasCrudController::class, 'inactivas']);
    Route::get('/plan-cuentas/debug', [PlanCuentasCrudController::class, 'debug']);
    Route::get('/plan-cuentas/padres', [PlanCuentasCrudController::class, 'getCuentasPadre']);
    Route::get('/plan-cuentas/subdominios/{dominio}', [PlanCuentasCrudController::class, 'getSubdominiosPorDominio']);
    Route::get('/plan-cuentas/subdominios', [PlanCuentasCrudController::class, 'getSubdominios']);
    
    // SEGUNDO: Rutas con parámetros variables
    Route::put('/plan-cuentas/{id}/reactivar', [PlanCuentasCrudController::class, 'reactivar']);
    
    // TERCERO: Rutas CRUD básicas
    Route::get('/plan-cuentas', [PlanCuentasCrudController::class, 'index']);
    Route::post('/plan-cuentas', [PlanCuentasCrudController::class, 'store']);
    Route::put('/plan-cuentas/{id}', [PlanCuentasCrudController::class, 'update']);
    Route::delete('/plan-cuentas/{id}', [PlanCuentasCrudController::class, 'destroy']);
    
});