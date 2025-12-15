<?php
namespace App\Http\Controllers;
use App\Models\Comprobante;
use App\Models\ComprobanteDetalle;
use App\Models\SecuenciaComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ComprobanteController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo' => ['required', Rule::in(['ingreso','egreso','diario'])],
            'fecha' => 'required|date',
            'glosa_general' => 'nullable|string',
            'elaborado_por' => 'nullable|string',
            'aprobado_por' => 'nullable|string',
            'verificado_por' => 'nullable|string',
            'monto_letras' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.cuenta_id' => 'required|integer|exists:plan_cuentas,id',
            'detalles.*.glosa_detalle' => 'nullable|string',
            'detalles.*.debe' => 'nullable|numeric|min:0',
            'detalles.*.haber' => 'nullable|numeric|min:0',
        ]);

        $totalDebe = 0;
        $totalHaber = 0;
        foreach ($data['detalles'] as $line) {
            $totalDebe += floatval($line['debe'] ?? 0);
            $totalHaber += floatval($line['haber'] ?? 0);
        }

        if (round($totalDebe,2) !== round($totalHaber,2)) {
            return response()->json(['message' => 'El total Debe debe ser igual al total Haber.'], 422);
        }

        $anio = intval(date('Y', strtotime($data['fecha'])));

        return DB::transaction(function () use ($data, $anio, $totalDebe, $totalHaber) {
            $seq = SecuenciaComprobante::where('tipo', $data['tipo'])
                    ->where('anio', $anio)
                    ->lockForUpdate()
                    ->first();

            if (!$seq) {
                $seq = SecuenciaComprobante::create([
                    'tipo' => $data['tipo'],
                    'anio' => $anio,
                    'ultimo' => 0,
                ]);
            }

            $seq->ultimo++;
            $seq->save();

            $comprobante = Comprobante::create([
                'numero' => $seq->ultimo,
                'tipo' => $data['tipo'],
                'anio' => $anio,
                'fecha' => $data['fecha'],
                'glosa_general' => $data['glosa_general'] ?? null,
                'total_debe' => $totalDebe,
                'total_haber' => $totalHaber,
                'monto_letras' => $data['monto_letras'] ?? null,
                'elaborado_por' => $data['elaborado_por'] ?? null,
                'aprobado_por' => $data['aprobado_por'] ?? null,
                'verificado_por' => $data['verificado_por'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($data['detalles'] as $idx => $line) {
                ComprobanteDetalle::create([
                    'comprobante_id' => $comprobante->id,
                    'cuenta_id' => $line['cuenta_id'],
                    'glosa_detalle' => $line['glosa_detalle'] ?? null,
                    'debe' => floatval($line['debe'] ?? 0),
                    'haber' => floatval($line['haber'] ?? 0),
                    'orden' => $idx,
                ]);
            }

            return response()->json(['message' => 'Comprobante creado', 'comprobante_id' => $comprobante->id], 201);
        });
    }

    public function show($id)
    {
        $com = Comprobante::with('detalles.cuenta')->findOrFail($id);
        return response()->json($com);
    }
    public function index()
{
    // devolvemos todos los comprobantes con detalles, si quieres
    $comprobantes = Comprobante::with('detalles.cuenta')
        ->orderBy('fecha','desc')
        ->get();

    return response()->json($comprobantes);
}

}
