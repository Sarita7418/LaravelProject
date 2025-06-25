<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permiso;

class RolPermisoController extends Controller
{
    // Obtener todos los roles con sus permisos
    public function index()
    {
        $roles = Role::with('permisos')->get();
        return response()->json($roles);
    }

    // Crear nuevo rol
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|unique:roles,descripcion',
        ]);

        $rol = Role::create([
            'descripcion' => $request->descripcion,
        ]);

        return response()->json($rol, 201);
    }

    // Asignar permisos a un rol
    public function asignarPermisos(Request $request, $rolId)
    {
        $request->validate([
            'permisos' => 'array', // array de IDs de permisos
            'permisos.*' => 'exists:permisos,id',
        ]);

        $rol = Role::findOrFail($rolId);
        $rol->permisos()->sync($request->permisos);

        return response()->json(['mensaje' => 'Permisos actualizados']);
    }

    // Obtener todos los permisos
    public function permisos()
    {
        return response()->json(Permiso::all());
    }
}
