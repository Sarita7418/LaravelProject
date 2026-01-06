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
use App\Http\Controllers\Crud\PlanPresupuestarioCrudController;
use App\Http\Controllers\Navegacion\NavegacionController;
use App\Http\Controllers\Auth\NewPasswordController;

use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReportePDFController;

use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\PlanCuentaController;

use App\Http\Controllers\Crud\EmpresaCrudController;
use App\Http\Controllers\Crud\SucursalCrudController;
use App\Http\Controllers\Crud\LogoCrudController;

use App\Http\Controllers\FacturaController;


Route::post('/personas', [PersonaCrudController::class, 'store']);

// ... imports ...
Route::post('/personas', [PersonaCrudController::class, 'store']);

// 👇 PONLA AQUÍ (Temporalmente pública para probar)
Route::post('/facturas', [FacturaController::class, 'store']);


Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    SubstituteBindings::class,
    ThrottleRequests::class,
])->group(function () {

    
    //comprobantes
    Route::get('comprobantes', [ComprobanteController::class, 'index']); // 👈 esta faltaba
    Route::post('comprobantes', [ComprobanteController::class, 'store']);
    Route::get('comprobantes/{id}', [ComprobanteController::class, 'show']);
    Route::get('cuentas', [PlanCuentaController::class, 'index']);


    
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
    // Empresas
    Route::get('/empresas', [EmpresaCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/empresas-inactivas', [EmpresaCrudController::class, 'inactivos'])->middleware('auth:sanctum');
    Route::post('/empresas', [EmpresaCrudController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/empresas/{id}', [EmpresaCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/empresas/{id}', [EmpresaCrudController::class, 'destroy'])->middleware('auth:sanctum');
    Route::patch('/empresas/{id}/reactivar', [EmpresaCrudController::class, 'reactivar'])->middleware('auth:sanctum');


    // Sucursales
    Route::get('/sucursales', [SucursalCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/sucursales-inactivas', [SucursalCrudController::class, 'inactivos'])->middleware('auth:sanctum');
    Route::post('/sucursales', [SucursalCrudController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/sucursales/{id}', [SucursalCrudController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/sucursales/{id}', [SucursalCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/sucursales/{id}', [SucursalCrudController::class, 'destroy'])->middleware('auth:sanctum');
    Route::patch('/sucursales/{id}/reactivar', [SucursalCrudController::class, 'reactivar'])->middleware('auth:sanctum');

    // Logos
    Route::get('/logos', [LogoCrudController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/logos', [LogoCrudController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/logos/{id}', [LogoCrudController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/logos/{id}', [LogoCrudController::class, 'destroy'])->middleware('auth:sanctum');



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
    // Plan Presupuestario - RUTAS CORREGIDAS (ORDEN IMPORTANTE)
    // PRIMERO: Rutas específicas (más específicas primero)
    Route::get('/plan-presupuestarios/todas', [PlanPresupuestarioCrudController::class, 'indexTodas']);
    Route::get('/plan-presupuestarios/inactivas', [PlanPresupuestarioCrudController::class, 'inactivas']);
    Route::get('/plan-presupuestarios/debug', [PlanPresupuestarioCrudController::class, 'debug']);
    Route::get('/plan-presupuestarios/padres', [PlanPresupuestarioCrudController::class, 'getCuentasPadre']);
    Route::get('/plan-presupuestarios/subdominios/{dominio}', [PlanPresupuestarioCrudController::class, 'getSubdominiosPorDominio']);
    Route::get('/plan-presupuestarios/subdominios', [PlanPresupuestarioCrudController::class, 'getSubdominios']);
    
    // SEGUNDO: Rutas con parámetros variables
    Route::put('/plan-presupuestarios/{id}/reactivar', [PlanPresupuestarioCrudController::class, 'reactivar']);
    
    // TERCERO: Rutas CRUD básicas
    Route::get('/plan-presupuestarios', [PlanPresupuestarioCrudController::class, 'index']);
    Route::post('/plan-presupuestarios', [PlanPresupuestarioCrudController::class, 'store']);
    Route::put('/plan-presupuestarios/{id}', [PlanPresupuestarioCrudController::class, 'update']);
    Route::delete('/plan-presupuestarios/{id}', [PlanPresupuestarioCrudController::class, 'destroy']);

    
});