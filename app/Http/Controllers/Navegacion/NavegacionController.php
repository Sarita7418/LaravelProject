<?php

namespace App\Http\Controllers\Navegacion;

use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class NavegacionController extends Controller
{

    public function obtenerUsuarioConPermisos(Request $request)
    {
        $user = $request->user()->load('role.menuItems.acciones');

        // Obtener menús permitidos para el rol
        $menuItems = $user->role->menuItems->map(function ($menu) {
            return [
                'item' => $menu->item,
                'ruta' => $menu->ruta,
            ];
        });

        // Obtener acciones permitidas (únicas, puede haber repetidas en diferentes menús)
        $acciones = $user->role->menuItems
            ->flatMap(function ($menu) {
                return $menu->acciones->pluck('nombre');
            })
            ->unique()
            ->values();

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
    public function obtenerMenu($idUsuario)
    {
        $user = \App\Models\User::with('role.menuItems')->findOrFail($idUsuario);

        $menu = $user->role->menuItems()
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


    public function obtenerAcciones($idUsuario)
    {
        $user = \App\Models\User::with('role.menuItems.acciones')->findOrFail($idUsuario);

        $acciones = $user->role->menuItems
            ->flatMap(function ($menu) {
                return $menu->acciones->pluck('nombre');
            })
            ->unique()
            ->values();

        return response()->json($acciones);
    }
}
