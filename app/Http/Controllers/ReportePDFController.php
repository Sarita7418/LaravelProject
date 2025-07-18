<?php

namespace App\Http\Controllers;

use App\Models\User; // 👈 AGREGA ESTA LÍNEA
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportePDFController extends Controller
{
     public function usuarios(Request $request)
{
    $desde = $request->input('desde');
    $hasta = $request->input('hasta');

    $usuarios = User::with(['persona', 'role'])
        ->when($desde, fn($q) => $q->whereDate('created_at', '>=', $desde))
        ->when($hasta, fn($q) => $q->whereDate('created_at', '<=', $hasta))
        ->get();

    $pdf = PDF::loadView('reportes.usuarios_pdf', compact('usuarios'));

    return $pdf->download('reporte_usuarios.pdf');
}

}
