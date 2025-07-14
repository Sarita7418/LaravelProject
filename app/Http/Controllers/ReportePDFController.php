<?php

namespace App\Http\Controllers;

use App\Models\User; // 👈 AGREGA ESTA LÍNEA
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportePDFController extends Controller
{
     public function usuarios()
    {
        $usuarios = User::with(['persona', 'role'])->get();

        $pdf = PDF::loadView('reportes.usuarios_pdf', compact('usuarios'));

        return $pdf->download('reporte_usuarios.pdf');
    }
}
