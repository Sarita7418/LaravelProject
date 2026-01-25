<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;

class KardexController extends Controller
{
    public function show($producto_id)
    {
        // 1. Buscamos el producto para mostrar su nombre en el reporte
        $producto = Producto::with('unidadMedida')->findOrFail($producto_id);

        // 2. Traemos TODOS los movimientos históricos
        // Ordenados por fecha ASCENDENTE (del más viejo al más nuevo)
        // para poder reconstruir la historia paso a paso.
        $movimientos = MovimientoInventario::where('id_producto', $producto_id)
            ->with('tipoMovimiento') // Para saber si fue "VENTA", "COMPRA", "AJUSTE"
            ->orderBy('fecha', 'asc')
            ->orderBy('created_at', 'asc') // En caso de empate en fecha, usar el orden de creación
            ->get();

        // 3. ALGORITMO DE PROMEDIO PONDERADO (PPP)
        $kardex = [];
        
        // Acumuladores (Saldos iniciales en 0)
        $saldoCantidad = 0;
        $saldoCostoTotal = 0;
        $costoPromedio = 0;

        foreach ($movimientos as $mov) {
            // Estructura de la fila para la tabla visual
            $fila = [
                'id' => $mov->id,
                'fecha' => $mov->fecha,
                'detalle' => $mov->referencia . ' (' . ($mov->tipoMovimiento->descripcion ?? 'Mov') . ')',
                // ENTRADAS
                'entrada_q' => 0, 'entrada_u' => 0, 'entrada_t' => 0,
                // SALIDAS
                'salida_q' => 0, 'salida_u' => 0, 'salida_t' => 0,
                // SALDOS
                'saldo_q' => 0, 'saldo_u' => 0, 'saldo_t' => 0,
            ];

            // --- LÓGICA MATEMÁTICA ---
            
            if ($mov->cantidad_entrada > 0) {
                // === ES UNA ENTRADA (COMPRA) ===
                // 1. Registramos la entrada con su costo real de compra
                $fila['entrada_q'] = $mov->cantidad_entrada;
                $fila['entrada_u'] = $mov->costo_unitario; // Precio de la factura de compra
                $fila['entrada_t'] = $mov->cantidad_entrada * $mov->costo_unitario;

                // 2. Sumamos al saldo acumulado
                $saldoCantidad += $mov->cantidad_entrada;
                $saldoCostoTotal += $fila['entrada_t'];
                
                // 3. RECALCULAMOS EL PROMEDIO (La clave del PPP)
                // Nuevo Promedio = Costo Total Acumulado / Cantidad Total Acumulada
                if ($saldoCantidad > 0) {
                    $costoPromedio = $saldoCostoTotal / $saldoCantidad;
                }

            } else {
                // === ES UNA SALIDA (VENTA) ===
                // 1. La salida se valoriza al COSTO PROMEDIO actual (no al precio de venta)
                $fila['salida_q'] = $mov->cantidad_salida;
                $fila['salida_u'] = $costoPromedio; // <--- Usamos el promedio que traíamos
                $fila['salida_t'] = $mov->cantidad_salida * $costoPromedio;

                // 2. Restamos del saldo
                $saldoCantidad -= $mov->cantidad_salida;
                $saldoCostoTotal -= $fila['salida_t'];
                
                // Nota: En salidas por PPP, el costo promedio NO cambia, se mantiene.
            }

            // Guardamos cómo quedó el saldo después de este movimiento
            $fila['saldo_q'] = $saldoCantidad;
            $fila['saldo_u'] = $costoPromedio;
            $fila['saldo_t'] = $saldoCostoTotal;

            $kardex[] = $fila;
        }

        return response()->json([
            'producto' => $producto,
            'kardex' => $kardex
        ]);
    }
}