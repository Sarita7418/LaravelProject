<?php

namespace App\Http\Controllers;

use App\Exports\ExportarUsuarios;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function exportUsuarios()
    {
        return Excel::download(new ExportarUsuarios, 'usuarios.xlsx');
    }
}
