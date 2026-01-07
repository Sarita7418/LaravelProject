<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\StockActual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FacturaController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validación inicial de datos
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id', // O puedes recibir datos para crear cliente al vuelo
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($data) {
                // ---------------------------------------------------------
                // PASO 1: VERIFICAR STOCK DE TODO EL PEDIDO ANTES DE CREAR NADA
                // ---------------------------------------------------------
                $totalVenta = 0;
                $itemsProcesados = []; // Guardamos info para no consultar DB doble vez

                foreach ($data['detalles'] as $item) {
                    $producto = Producto::findOrFail($item['producto_id']);
                    
                    // Usamos la función mágica de tu compañero
                    $validacion = StockActual::validarDisponibilidadParaVenta($producto->id, $item['cantidad']);

                    if (!$validacion['disponible']) {
                        throw new \Exception("Stock insuficiente para el producto: {$producto->nombre}. Solicitado: {$item['cantidad']}, Disponible: {$validacion['stock_actual']}");
                    }

                    $subtotal = $item['cantidad'] * $producto->precio_salida; // Asumiendo que precio_salida es el precio venta
                    $totalVenta += $subtotal;

                    $itemsProcesados[] = [
                        'producto' => $producto,
                        'cantidad' => $item['cantidad'],
                        'precio' => $producto->precio_salida,
                        'subtotal' => $subtotal
                    ];
                }

                // ---------------------------------------------------------
                // PASO 2: CREAR LA FACTURA (CABECERA)
                // ---------------------------------------------------------
                // Generar número correlativo simple (luego lo mejoras con lógica fiscal)
                $ultimoNumero = Factura::max('numero_factura') ?? 0;
                
                $factura = Factura::create([
                    'cliente_id' => $data['cliente_id'],
                    'user_id' => Auth::id() ?? 1, // Usuario logueado o default 1 para pruebas
                    'numero_factura' => $ultimoNumero + 1,
                    'fecha_emision' => now(),
                    'monto_total' => $totalVenta,
                    'estado' => 'VALIDA',
                    'cuf' => '123456789' // Aquí iría la lógica de Impuestos (SIAT) luego
                ]);

                // ---------------------------------------------------------
                // PASO 3: GUARDAR DETALLES Y DESCONTAR STOCK (La parte Hardcore)
                // ---------------------------------------------------------
                foreach ($itemsProcesados as $item) {
                    // A. Guardar en tabla detalle_facturas
                    DetalleFactura::create([
                        'factura_id' => $factura->id,
                        'producto_id' => $item['producto']->id,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'subtotal' => $item['subtotal']
                    ]);

                    // B. DESCONTAR DEL INVENTARIO (Lógica FEFO automática)
                    // Obtenemos los lotes disponibles ordenados por vencimiento (tu compañero ya hizo esto)
                    $lotesDisponibles = StockActual::obtenerStockDisponibleParaVenta($item['producto']->id);
                    
                    $cantidadPendiente = $item['cantidad'];

                    foreach ($lotesDisponibles as $stockLote) {
                        if ($cantidadPendiente <= 0) break; // Ya terminamos con este producto

                        // Cuánto podemos sacar de este lote específico
                        $cantidadA_Tomar = min($cantidadPendiente, $stockLote->cantidad);

                        // Restamos
                        $stockLote->decrementarStock($cantidadA_Tomar);
                        
                        // Reducimos lo que falta por sacar
                        $cantidadPendiente -= $cantidadA_Tomar;
                    }
                }

                return response()->json([
                    'message' => 'Venta realizada con éxito',
                    'factura_id' => $factura->id
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}