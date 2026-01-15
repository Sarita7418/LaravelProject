<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\StockActual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventarioController extends Controller
{
    /**
     * Registrar entrada de almacén (con conversión de unidades)
     */
    public function entrada(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'cantidad_compra' => 'required|numeric|min:1',
            'factor_conversion' => 'required|numeric|min:1',
            'lote' => 'required|string',
            'fecha_vencimiento' => 'required|date|after:today',
            'costo_unitario' => 'required|numeric|min:0',
            'id_ubicacion' => 'required|exists:politicos_ubicacion,id'
        ]);

        return DB::transaction(function () use ($request) {

            $producto = Producto::findOrFail($request->id_producto);

            // Convertir unidades según factor
            $cantidad_final = $request->cantidad_compra * $request->factor_conversion;

            // Crear o recuperar lote
            $lote = Lote::firstOrCreate(
                [
                    'id_producto' => $producto->id,
                    'numero_lote' => $request->lote
                ],
                [
                    'cantidad_inicial' => $cantidad_final,
                    'fecha_ingreso' => now(),
                    'fecha_vencimiento' => $request->fecha_vencimiento,
                    'id_estado_lote' => 27 // ACTIVO
                ]
            );

            // Registrar movimiento
            MovimientoInventario::create([
                'fecha' => now(),
                'id_tipo_movimiento' => 30, // COMPRA
                'referencia' => 'Entrada almacén',
                'id_producto' => $producto->id,
                'id_lote' => $lote->id,
                'cantidad_entrada' => $cantidad_final,
                'costo_unitario' => $request->costo_unitario,
                'costo_total' => $cantidad_final * $request->costo_unitario,
                'id_ubicacion_destino' => $request->id_ubicacion,
                'id_usuario' => auth()->id()
            ]);

            // Actualizar stock
            StockActual::updateOrCreate(
                [
                    'id_producto' => $producto->id,
                    'id_lote' => $lote->id,
                    'id_ubicacion' => $request->id_ubicacion
                ],
                [
                    'cantidad' => DB::raw("cantidad + $cantidad_final")
                ]
            );

            return response()->json([
                'message' => 'Entrada registrada correctamente',
                'cantidad_convertida' => $cantidad_final
            ]);
        });
    }

    /**
     * Salida de almacén con FIFO
     */
    public function salida(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:1',
            'id_ubicacion' => 'required|exists:politicos_ubicacion,id'
        ]);

        return DB::transaction(function () use ($request) {

            $producto = Producto::findOrFail($request->id_producto);
            $cantidad_solicitada = $request->cantidad;

            // LOTES FIFO (ordenados por vencimiento ascendente)
            $lotes = Lote::where('id_producto', $producto->id)
                         ->where('id_estado_lote', 27)
                         ->orderBy('fecha_vencimiento', 'asc')
                         ->get();

            foreach ($lotes as $lote) {
                if ($cantidad_solicitada <= 0) break;

                $stock = StockActual::where('id_producto', $producto->id)
                                    ->where('id_lote', $lote->id)
                                    ->where('id_ubicacion', $request->id_ubicacion)
                                    ->first();

                if (!$stock || $stock->cantidad <= 0) continue;

                $cantidad_a_descontar = min($cantidad_solicitada, $stock->cantidad);

                MovimientoInventario::create([
                    'fecha' => now(),
                    'id_tipo_movimiento' => 31, 
                    'referencia' => 'Salida almacén (FIFO)',
                    'id_producto' => $producto->id,
                    'id_lote' => $lote->id,
                    'cantidad_salida' => $cantidad_a_descontar,
                    'costo_unitario' => $producto->precioEntradaActual ?? 0,
                    'costo_total' => $cantidad_a_descontar * ($producto->precioEntradaActual ?? 0),
                    'id_ubicacion_origen' => $request->id_ubicacion,
                    'id_usuario' => auth()->id(),
                ]);

                // Actualizar stock
                $stock->cantidad -= $cantidad_a_descontar;
                $stock->save();

                $cantidad_solicitada -= $cantidad_a_descontar;
            }

            if ($cantidad_solicitada > 0) {
                return response()->json([
                    'error' => 'Stock insuficiente'
                ], 400);
            }

            return ['message' => 'Salida registrada correctamente'];
        });
    }
}
