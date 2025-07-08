<?php

namespace App\Http\Controllers\Navegacion;

use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;



class NavegacionController extends Controller
{
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

    // GET /api/acciones/{idRol}
    public function obtenerAcciones($idRol)
    {
        $rol = Role::findOrFail($idRol);

        $acciones = $rol->acciones()
            ->select('nombre')
            ->pluck('nombre');

        return response()->json($acciones);
    }
}
