<?php

namespace App\Http\Controllers\Navegacion;

use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class NavegacionController extends Controller
{

    // GET /api/user
    public function obtenerUsuarioConPermisos(Request $request)
{
    $user = $request->user()->load('role');

    // Obtener los ítems de menú permitidos según el rol del usuario
    $menuItems = DB::table('menu_item_rol')
        ->join('menu_items', 'menu_items.id', '=', 'menu_item_rol.id_menu_item')
        ->where('menu_item_rol.id_rol', $user->id_rol)
        ->select('menu_items.item', 'menu_items.ruta')
        ->get();

    // Obtener las acciones permitidas según el rol del usuario
    $acciones = DB::table('accion_rol')
        ->join('acciones', 'acciones.id', '=', 'accion_rol.id_accion')
        ->where('accion_rol.id_rol', $user->id_rol)
        ->pluck('acciones.nombre');

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'dos_pasos_habilitado' => $user->dos_pasos_habilitado ?? false,
        'rol' => $user->role->descripcion,
        'permisos' => $menuItems,
        'acciones' => $acciones,
    ]);
}


    // GET /api/menu/{idRol}
    public function obtenerMenu($idRol)
    {
        $rol = Role::findOrFail($idRol);

        // Traer ítems de menú con relaciones jerárquicas (opcional)
        $menu = $rol->menuItems()
            ->select(
                'menu_items.id',
                'menu_items.item',
                'menu_items.ruta',
                'menu_items.id_padre',
                'menu_items.nivel',
                'menu_items.orden'
            )
            ->orderBy('menu_items.nivel')
            ->orderBy('menu_items.orden')
            ->get();


        return response()->json($menu);
    }

    public function obtenerAccionesPorUsuario($idUsuario)
{
    $usuario = \App\Models\User::with('role.acciones')->findOrFail($idUsuario);

    // Obtener acciones del rol del usuario
    $acciones = $usuario->role && $usuario->role->acciones
        ? $usuario->role->acciones->pluck('nombre')
        : [];

    return response()->json($acciones);
}
}
