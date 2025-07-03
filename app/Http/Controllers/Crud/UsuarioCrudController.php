<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UsuarioCrudController extends Controller
{
    public function index()
    {
        // Solo mostrar usuarios activos (estado = 1)
        $usuarios = User::with('role')->where('estado', 1)->get();
        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'id_rol' => 'required|exists:roles,id',
        ]);

        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'id_rol' => $request->id_rol,
            'estado' => 1, // Por defecto activo
        ]);

        $usuario->load('role');

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ], 201);
    }

    public function destroy($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // En lugar de eliminar, cambiar estado a 0 (inactivo)
        $usuario->estado = 0;
        $usuario->save();

        return response()->json(['message' => 'Usuario desactivado correctamente']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $id,
            'password' => 'nullable|string|min:6',
            'id_rol' => 'required|exists:roles,id',
        ]);

        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->id_rol = $request->id_rol;
                         
        // Solo actualizar la contraseña si se proporciona
        if ($request->filled('password')) {
            $usuario->password = bcrypt($request->password);
        }

        $usuario->save();
        $usuario->load('role');

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'usuario' => $usuario
        ]);
    }

    // Método adicional para reactivar un usuario
    public function reactivar($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->estado = 1;
        $usuario->save();

        return response()->json(['message' => 'Usuario reactivado correctamente']);
    }

    // Método para ver usuarios inactivos
    public function inactivos()
    {
        $usuarios = User::with('role')->where('estado', 0)->get();
        return response()->json($usuarios);
    }

    public function getRoles()
    {
        // Solo obtener roles activos para la selección
        $roles = Role::where('estado', 1)->get();
        return response()->json($roles);
    }
}