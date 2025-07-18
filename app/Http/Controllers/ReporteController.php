<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Exports\ExportarUsuarios;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function exportUsuarios(Request $request)
{
    $desde = $request->input('desde');
    $hasta = $request->input('hasta');

    $usuarios = User::query()
        ->when($desde, function ($query) use ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        })
        ->when($hasta, function ($query) use ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        })
        ->get();

    // Aquí usás la lógica que ya tenés para generar el Excel
    return Excel::download(new ExportarUsuarios($usuarios), 'usuarios.xlsx');
}

}
