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
        $usuarios = User::with('role')->get();
        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email', // Cambio aquí: usuarios en lugar de users
            'password' => 'required|string|min:6',
            'id_rol' => 'required|exists:roles,id',
        ]);

        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'id_rol' => $request->id_rol,
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

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $id, // Cambio aquí: usuarios en lugar de users
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

    public function getRoles()
    {
        $roles = Role::all();
        return response()->json($roles);
    }
}