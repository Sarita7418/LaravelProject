<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Persona;
use Illuminate\Database\QueryException;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PersonaCrudController extends Controller
{
    public function index()
    {
        // Solo personas activas (estado = 1)
        $personas = Persona::where('estado', 1)->get();
        return response()->json($personas);
    }

   
    public function store(Request $request)
{
    try {
        \DB::beginTransaction();

        // Crear persona
        $persona = Persona::create([
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'ci' => $request->ci,
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento, 
            'estado' => 1,
        ]);

        // Si no se envía username, generarlo
        $username = $request->username;
        if (!$username) {
            $primerNombre = explode(' ', trim($request->nombres))[0]; // solo primer nombre
            $inicialNombre = strtolower(substr($primerNombre, 0, 1));
            $apellidoPaterno = strtolower($request->apellido_paterno);
            $inicialApellidoMaterno = strtolower(substr($request->apellido_materno, 0, 1));
            $username = $inicialNombre . $apellidoPaterno . $inicialApellidoMaterno;
        }

        // Crear usuario asociado
        $usuario = User::create([
            'id_persona' => $persona->id,
            'name' => $username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_rol' => 2, // rol user por defecto
            'estado' => 1,
        ]);

        \DB::commit();

        return response()->json([
            'message' => 'Persona y usuario creados exitosamente',
            'persona' => $persona,
            'usuario' => $usuario
        ], 201);
    } catch (\Exception $e) {
        \DB::rollBack();
        return response()->json([
            'message' => 'Error al crear persona y usuario',
            'error' => $e->getMessage()
        ], 500);
    }
}



    public function update(Request $request, $id)
    {
        $persona = Persona::find($id);

        if (!$persona) {
            return response()->json(['message' => 'Persona no encontrada'], 404);
        }

        $persona->update([
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'ci' => $request->ci,
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento
        ]);

        return response()->json([
            'message' => 'Persona actualizada correctamente',
            'persona' => $persona
        ]);
    }

    public function destroy($id)
    {
        $persona = Persona::find($id);

        if (!$persona) {
            return response()->json(['message' => 'Persona no encontrada'], 404);
        }

        $persona->estado = 0;
        $persona->save();

        return response()->json(['message' => 'Persona desactivada correctamente']);
    }

    public function inactivos()
    {
        $personas = Persona::where('estado', 0)->get();
        return response()->json($personas);
    }

    public function reactivar($id)
    {
        $persona = Persona::find($id);

        if (!$persona) {
            return response()->json(['message' => 'Persona no encontrada'], 404);
        }

        $persona->estado = 1;
        $persona->save();

        return response()->json(['message' => 'Persona reactivada correctamente']);
    }
}
