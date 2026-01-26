<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;

class EspecialidadController extends Controller
{
    public function index()
    {
        $especialidades = Especialidad::all();
        return response()->json($especialidades);
    }
}