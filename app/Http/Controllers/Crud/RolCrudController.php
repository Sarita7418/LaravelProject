<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;

class RolCrudController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255|unique:roles,descripcion',
        ]);

        $rol = Role::create([
            'descripcion' => $request->descripcion,
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

        $rol->delete();

        return response()->json(['message' => 'Rol eliminado correctamente']);
    }
}
