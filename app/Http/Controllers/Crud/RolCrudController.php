<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RolCrudController extends Controller
{
    public function index()
    {
        // Solo mostrar roles activos (estado = 1)
        $roles = Role::where('estado', 1)->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255|unique:roles,descripcion',
        ]);

        $rol = Role::create([
            'descripcion' => $request->descripcion,
            'estado' => 1, // Por defecto activo
        ]);

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'rol' => $rol
        ], 201);
    }

    public function destroy($id)
    {
        $rol = Role::find($id);

        if (!$rol) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        // En lugar de eliminar, cambiar estado a 0 (inactivo)
        $rol->estado = 0;
        $rol->save();

        return response()->json(['message' => 'Rol desactivado correctamente']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255|unique:roles,descripcion,' . $id,
        ]);

        $rol = Role::find($id);

        if (!$rol) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $rol->descripcion = $request->descripcion;
        $rol->save();

        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'rol' => $rol
        ]);
    }

    // Método adicional para reactivar un rol
    public function reactivar($id)
    {
        $rol = Role::find($id);

        if (!$rol) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $rol->estado = 1;
        $rol->save();

        return response()->json(['message' => 'Rol reactivado correctamente']);
    }

    // Método para ver roles inactivos
    public function inactivos()
    {
        $roles = Role::where('estado', 0)->get();
        return response()->json($roles);
    }

    public function todosLosMenusYAcciones()
    {
        // Trae todos los menús y las acciones disponibles para cada uno (mediante la pivote menu_item_accion)
        $menus = \App\Models\MenuItem::with(['acciones'])->get();

        return response()->json($menus);
    }

    public function menusDeRol($idRol)
    {
        $menus = \App\Models\MenuItem::whereHas('roles', function ($query) use ($idRol) {
            $query->where('roles.id', $idRol);
        })->get();

        return response()->json($menus);
    }

    public function accionesDeRol($idRol)
    {
        $accionesPorMenu = DB::table('accion_menu_item_rol')
            ->join('menu_items', 'accion_menu_item_rol.id_menu_item', '=', 'menu_items.id')
            ->join('acciones', 'accion_menu_item_rol.id_accion', '=', 'acciones.id')
            ->where('accion_menu_item_rol.id_rol', $idRol)
            ->select(
                'accion_menu_item_rol.id_menu_item',
                'menu_items.item as menu_nombre',
                'acciones.id as accion_id',
                'acciones.nombre as accion_nombre'
            )
            ->get();

        return response()->json($accionesPorMenu);
    }

    public function actualizarMenusDeRol(Request $request, $idRol)
    {
        $idsMenus = $request->input('menus', []); // array de IDs

        // Sincroniza los menús del rol
        $rol = \App\Models\Role::findOrFail($idRol);
        $rol->menuItems()->sync($idsMenus);

        return response()->json(['success' => true, 'menus_asignados' => $idsMenus]);
    }

    public function actualizarAccionesDeRol(Request $request, $idRol)
    {
        $acciones = $request->input('acciones', []); // array de ['id_menu_item' => X, 'id_accion' => Y]

        // Elimina todas las relaciones actuales para el rol
        DB::table('accion_menu_item_rol')->where('id_rol', $idRol)->delete();

        // Inserta las nuevas combinaciones
        $now = now();
        foreach ($acciones as $accion) {
            DB::table('accion_menu_item_rol')->insert([
                'id_rol'       => $idRol,
                'id_menu_item' => $accion['id_menu_item'],
                'id_accion'    => $accion['id_accion'],
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        return response()->json(['success' => true, 'acciones_asignadas' => $acciones]);
    }
}
