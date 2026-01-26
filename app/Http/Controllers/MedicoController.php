<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use Illuminate\Http\Request;

class MedicoController extends Controller
{
    public function index()
    {
        $medicos = Medico::with('especialidad')->get();
        return response()->json($medicos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'matricula' => 'required|string|unique:medicos,matricula',
            'id_especialidad' => 'required|exists:especialidades_medicas,id'
        ]);

        $medico = Medico::create($validated);
        return response()->json($medico, 201);
    }

    public function update(Request $request, $id)
    {
        $medico = Medico::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'matricula' => 'required|string|unique:medicos,matricula,' . $id,
            'id_especialidad' => 'required|exists:especialidades_medicas,id'
        ]);

        $medico->update($validated);
        return response()->json($medico);
    }

    public function destroy($id)
    {
        $medico = Medico::findOrFail($id);
        $medico->delete();
        return response()->json(['message' => 'Médico eliminado'], 200);
    }
}