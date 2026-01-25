<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\DetalleFactura;
use App\Models\Producto;
use App\Models\StockActual;
use App\Models\MovimientoInventario; // Importante para el Kardex
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FacturaController extends Controller
{
    public function index()
    {
        // Traemos todas las facturas, pero OJO: cargamos la relación 'cliente'
        // para poder mostrar la Razón Social en la tabla.
        // Ordenamos por 'created_at' descendente (las nuevas primero)
         $facturas = Factura::with(['cliente', 'detalles.producto'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($facturas);
    }
    
    public function store(Request $request)
    {
        // 1. Cambiamos la validación: Pedimos NIT y Razón Social en lugar de ID
        $data = $request->validate([
            'nit' => 'required|string',            // El NIT/CI del cliente
            'razon_social' => 'required|string',   // El nombre/razón social
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($data) {
                
                // ---------------------------------------------------------
                // PASO 0: GESTIÓN INTELIGENTE DEL CLIENTE (Find or Create)
                // ---------------------------------------------------------
                // Buscamos al cliente por NIT. Si no existe, lo creamos.
                $cliente = Cliente::firstOrCreate(
                    ['nit_ci' => $data['nit']], // <--- AQUÍ ESTABA EL ERROR
                    [
                        'razon_social' => $data['razon_social'],
                        'email' => null, // Opcional, según tu tabla
                        'complemento' => null // Opcional
                    ] 
                );

                // Si el cliente ya existía pero cambió de Razón Social, podríamos actualizarlo (Opcional)
                // $cliente->update(['razon_social' => $data['razon_social']]);

                // ---------------------------------------------------------
                // FASE 1: VALIDACIÓN Y CÁLCULO
                // ---------------------------------------------------------
                $itemsProcesados = []; 
                $totalVenta = 0;

                foreach ($data['detalles'] as $item) {
                    $producto = Producto::with(['precioSalidaVigente'])->findOrFail($item['producto_id']);
                    
                    $validacion = StockActual::validarDisponibilidadParaVenta($producto->id, $item['cantidad']);
                    if (!$validacion['disponible']) {
                        throw new \Exception("Stock insuficiente para: {$producto->nombre}");
                    }

                    // Usamos el operador '??' (Fusión de null).
                    // Significa: "Intenta usar el precioActual. Si es nulo, usa precio_salida simple".
                    $precioVenta = $producto->precioSalidaActual ?? $producto->precio_salida;

                    // Validacion extra por si AMBOS son nulos (caso imposible si tu tabla tiene datos)
                    if (!$precioVenta) {
                        throw new \Exception("El producto {$producto->nombre} no tiene precio ni nuevo ni antiguo.");
                    }
                    $subtotal = $item['cantidad'] * $precioVenta;
                    $totalVenta += $subtotal;

                    $itemsProcesados[] = [
                        'producto' => $producto,
                        'cantidad' => $item['cantidad'],
                        'precio_venta' => $precioVenta,
                        'subtotal' => $subtotal
                    ];
                }

                // ---------------------------------------------------------
                // FASE 2: CREAR FACTURA
                // ---------------------------------------------------------
                $ultimoNumero = Factura::max('numero_factura') ?? 0;

                $factura = Factura::create([
                    'cliente_id' => $cliente->id,
                    'user_id' => Auth::id() ?? 1,
                    'numero_factura' => $ultimoNumero + 1,
                    'fecha_emision' => now(),
                    'monto_total' => $totalVenta,
                    'estado' => 'VALIDA',
                    'cuf' => 'GEN-' . time()
                ]);

                // ---------------------------------------------------------
                // FASE 3: DETALLES Y KARDEX (Igual que antes)
                // ---------------------------------------------------------
                foreach ($itemsProcesados as $item) {
                    DetalleFactura::create([
                        'factura_id' => $factura->id,
                        'producto_id' => $item['producto']->id,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_venta'],
                        'subtotal' => $item['subtotal']
                    ]);

                    $cantidadPendiente = $item['cantidad'];
                    $lotesDisponibles = StockActual::obtenerStockDisponibleParaVenta($item['producto']->id);

                    foreach ($lotesDisponibles as $stockLote) {
                        if ($cantidadPendiente <= 0) break;
                        $cantidadA_Tomar = min($cantidadPendiente, $stockLote->cantidad);
                        
                        $stockLote->decrementarStock($cantidadA_Tomar);

                        // 1. Definimos el costo usando el Plan A (Nuevo) o Plan B (Viejo)
                    $costoProducto = $item['producto']->precioEntradaActual ?? $item['producto']->precio_entrada;

                    // 2. Validación de seguridad (Si no tiene costo, asumimos 0 o lanzamos error)
                    if (!$costoProducto) {
                        // Opción A: Lanzar error (Más estricto)
                        // throw new \Exception("El producto {$item['producto']->nombre} no tiene costo de entrada definido.");
                        
                        // Opción B: Asumir costo 0 (Para que no se trabe la venta)
                        $costoProducto = 0; 
                    }

                    MovimientoInventario::create([
                        'fecha' => now(),
                        'id_tipo_movimiento' => 35, // SALIDA POR VENTA
                        'referencia' => 'Venta Factura #' . $factura->numero_factura,
                        'id_producto' => $item['producto']->id,
                        'id_lote' => $stockLote->id_lote,
                        'cantidad_entrada' => 0,
                        'cantidad_salida' => $cantidadA_Tomar,
                        'costo_unitario' => $costoProducto, // <--- Aquí usamos la variable segura
                        'costo_total' => $cantidadA_Tomar * $costoProducto, // <--- Y aquí también
                        'id_ubicacion_origen' => $stockLote->id_ubicacion,
                        'id_usuario' => Auth::id() ?? 1,
                    ]);

                        $cantidadPendiente -= $cantidadA_Tomar;
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Venta registrada correctamente',
                    'factura_id' => $factura->id,
                    'cliente' => $cliente->razon_social
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta',
                'error' => $e->getMessage()
            ], 400);
        }
    }
    // Método para autocompletar cliente
    public function buscarCliente($nit)
    {
        // Buscamos en la columna correcta 'nit_ci'
        $cliente = \App\Models\Cliente::where('nit_ci', $nit)->first();

        if ($cliente) {
            return response()->json([
                'encontrado' => true,
                'cliente' => $cliente
            ]);
        }

        return response()->json(['encontrado' => false]);
    }
}