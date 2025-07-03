<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

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
}