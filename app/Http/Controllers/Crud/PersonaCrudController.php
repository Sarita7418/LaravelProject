<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Persona;
use Illuminate\Database\QueryException;


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
        $persona = Persona::create([
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'ci' => $request->ci,
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento, 
            'estado' => 1,
        ]);

        return response()->json([
            'message' => 'Persona creada exitosamente',
            'persona' => $persona
        ], 201);
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
