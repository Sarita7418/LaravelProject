<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Models\StockActual;
use App\Models\MovimientoInventario;
use App\Models\Empresa;
use App\Models\Subdominio;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        try {
            $empresaId = Auth::user()->id_empresa ?? 1;
            $sucursalId = Auth::user()->id_sucursal ?? 1;

            $query = Compra::with([
                'proveedor', 
                'sucursal', 
                'usuario', 
                'estadoCompra',
                'detalles.producto'
            ])
            ->where('id_empresa', $empresaId)
            ->where('id_sucursal', $sucursalId)
            ->orderBy('created_at', 'desc');

            if ($request->filled('estado')) {
                $query->where('id_estado_compra', $request->estado);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nro_documento', 'like', "%{$search}%")
                      ->orWhereHas('proveedor', function($q2) use ($search) {
                          $q2->where('razon_social', 'like', "%{$search}%")
                             ->orWhere('nit', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('fecha_compra', [$request->fecha_inicio, $request->fecha_fin]);
            }

            $perPage = $request->get('per_page', 20);
            $compras = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $compras,
                'estados' => Subdominio::where('id_dominio', 8)->get()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en index de compras:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar compras',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_proveedor' => 'required|exists:empresas,id',
            'fecha_compra' => 'required|date',
            'nro_documento' => 'required|string|max:50',
            'observacion' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01',
            'detalles.*.descuento_pct' => 'nullable|numeric|min:0|max:100',
            'detalles.*.descuento_monto' => 'nullable|numeric|min:0',
            'detalles.*.codigo_barras' => 'nullable|string|max:50',
            'detalles.*.numero_lote' => 'nullable|string|max:100',
            'detalles.*.fecha_vencimiento' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            Log::error('Validación fallida:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $subtotalTotal = 0;
            $descuentoTotal = 0;

            $empresaId = Auth::user()->id_empresa ?? 1;
            $sucursalId = Auth::user()->id_sucursal ?? 1;

            $compraData = [
                'id_empresa' => $empresaId,
                'id_sucursal' => $sucursalId,
                'id_proveedor' => $validated['id_proveedor'],
                'id_usuario' => Auth::id(),
                'fecha_compra' => $validated['fecha_compra'],
                'nro_documento' => $validated['nro_documento'],
                'observacion' => $validated['observacion'] ?? null,
                'subtotal' => 0,
                'descuento_total' => 0,
                'total_compra' => 0,
                'id_estado_compra' => 25,
            ];

            $tableColumns = Schema::getColumnListing('compras');
            foreach ($compraData as $key => $value) {
                if (!in_array($key, $tableColumns)) {
                    unset($compraData[$key]);
                }
            }

            $compra = Compra::create($compraData);

            foreach ($validated['detalles'] as $det) {
                $cantidad = floatval($det['cantidad']);
                $precioUnitario = floatval($det['precio_unitario']);
                $descuentoPct = isset($det['descuento_pct']) ? floatval($det['descuento_pct']) : 0;
                $descuentoMonto = isset($det['descuento_monto']) ? floatval($det['descuento_monto']) : 0;
                
                $totalSinDescuento = $cantidad * $precioUnitario;
                
                if ($descuentoPct > 0) {
                    $descuentoMonto = ($totalSinDescuento * $descuentoPct) / 100;
                }
                
                if ($descuentoMonto > $totalSinDescuento) {
                    $descuentoMonto = $totalSinDescuento;
                }
                
                $subtotal = $totalSinDescuento - $descuentoMonto;
                
                $detalleData = [
                    'id_compra' => $compra->id,
                    'id_producto' => $det['id_producto'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento_pct' => $descuentoPct,
                    'descuento_monto' => $descuentoMonto,
                    'subtotal' => $subtotal
                ];

                $detalleColumns = Schema::getColumnListing('compras_detalle');
                
                if (isset($det['codigo_barras']) && in_array('codigo_barras', $detalleColumns)) {
                    $detalleData['codigo_barras'] = trim($det['codigo_barras']);
                }
                
                if (isset($det['numero_lote']) && in_array('numero_lote', $detalleColumns)) {
                    $detalleData['numero_lote'] = trim($det['numero_lote']);
                }
                
                if (isset($det['fecha_vencimiento']) && in_array('fecha_vencimiento', $detalleColumns)) {
                    $detalleData['fecha_vencimiento'] = $det['fecha_vencimiento'];
                }

                $detalleCompra = CompraDetalle::create($detalleData);
                
                $subtotalTotal += $totalSinDescuento;
                $descuentoTotal += $descuentoMonto;

                $producto = Producto::find($det['id_producto']);
                if ($producto) {
                    if (isset($det['codigo_barras']) && !empty(trim($det['codigo_barras']))) {
                        $producto->update([
                            'codigo_barras' => trim($det['codigo_barras'])
                        ]);
                    }

                    $precioEntradaActual = ProductoPrecio::where('id_producto', $det['id_producto'])
                        ->where('id_tipo_precio', 33)
                        ->where('activo', true)
                        ->first();

                    if ($precioEntradaActual) {
                        $precioEntradaActual->update([
                            'activo' => false,
                            'fecha_fin' => now()->subDay()
                        ]);
                    }

                    ProductoPrecio::create([
                        'id_producto' => $det['id_producto'],
                        'id_tipo_precio' => 33,
                        'precio' => $precioUnitario,
                        'fecha_inicio' => now(),
                        'activo' => true
                    ]);

                    $precioSalida = $precioUnitario * 1.30;
                    $precioSalidaActual = ProductoPrecio::where('id_producto', $det['id_producto'])
                        ->where('id_tipo_precio', 34)
                        ->where('activo', true)
                        ->first();

                    if ($precioSalidaActual) {
                        $precioSalidaActual->update([
                            'activo' => false,
                            'fecha_fin' => now()->subDay()
                        ]);
                    }

                    ProductoPrecio::create([
                        'id_producto' => $det['id_producto'],
                        'id_tipo_precio' => 34,
                        'precio' => $precioSalida,
                        'fecha_inicio' => now(),
                        'activo' => true
                    ]);

                    $producto->update([
                        'precio_entrada' => $precioUnitario,
                        'precio_salida' => $precioSalida
                    ]);

                    $idLote = null;
                    if (isset($det['numero_lote']) && !empty(trim($det['numero_lote']))) {
                        $loteData = [
                            'id_compra' => $compra->id,
                            'id_producto' => $det['id_producto'],
                            'numero_lote' => trim($det['numero_lote']),
                            'cantidad_inicial' => $cantidad,
                            'fecha_ingreso' => now(),
                            'id_estado_lote' => 27
                        ];

                        if (isset($det['fecha_vencimiento'])) {
                            $loteData['fecha_vencimiento'] = $det['fecha_vencimiento'];
                        }

                        $lote = Lote::create($loteData);
                        $idLote = $lote->id;
                    }

                    $stockActual = StockActual::where('id_producto', $det['id_producto'])
                        ->where('id_lote', $idLote)
                        ->where('id_ubicacion', 1)
                        ->first();

                    if ($stockActual) {
                        $stockActual->incrementarStock($cantidad);
                    } else {
                        $stockData = [
                            'id_producto' => $det['id_producto'],
                            'id_lote' => $idLote,
                            'id_ubicacion' => 1,
                            'cantidad' => $cantidad
                        ];
                        
                        $stockActual = StockActual::create($stockData);
                    }

                    $movimientoData = [
                        'fecha' => now(),
                        'id_tipo_movimiento' => 30,
                        'referencia' => 'Compra #' . $compra->nro_documento,
                        'id_producto' => $det['id_producto'],
                        'cantidad_entrada' => $cantidad,
                        'costo_unitario' => $precioUnitario,
                        'costo_total' => $cantidad * $precioUnitario,
                        'id_usuario' => Auth::id(),
                        'id_compra' => $compra->id
                    ];

                    if ($idLote) {
                        $movimientoData['id_lote'] = $idLote;
                    }

                    MovimientoInventario::create($movimientoData);
                }
            }

            $compra->update([
                'subtotal' => $subtotalTotal,
                'descuento_total' => $descuentoTotal,
                'total_compra' => $subtotalTotal - $descuentoTotal
            ]);

            DB::commit();
            
            $compra->load(['proveedor', 'detalles.producto']);
            
            return response()->json([
                'success' => true,
                'message' => 'Compra registrada exitosamente',
                'data' => $compra
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store de compras:', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la compra: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $compra = Compra::with([
                'proveedor',
                'sucursal',
                'usuario',
                'estadoCompra',
                'detalles.producto.unidadMedida',
                'detalles.producto.categoria'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $compra
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en show de compra:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Compra no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function confirmar($id)
    {
        try {
            $compra = Compra::with(['detalles.producto'])->findOrFail($id);
            
            if ($compra->id_estado_compra != 24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden confirmar compras en estado BORRADOR'
                ], 400);
            }

            DB::beginTransaction();
            
            $compra->id_estado_compra = 25;
            $compra->save();

            foreach ($compra->detalles as $detalle) {
                $idLote = null;
                if ($detalle->numero_lote) {
                    $lote = Lote::firstOrCreate(
                        [
                            'id_compra' => $compra->id,
                            'id_producto' => $detalle->id_producto,
                            'numero_lote' => $detalle->numero_lote
                        ],
                        [
                            'cantidad_inicial' => $detalle->cantidad,
                            'fecha_ingreso' => now(),
                            'fecha_vencimiento' => $detalle->fecha_vencimiento,
                            'id_estado_lote' => 27
                        ]
                    );
                    $idLote = $lote->id;
                }

                $stockActual = StockActual::firstOrCreate(
                    [
                        'id_producto' => $detalle->id_producto,
                        'id_lote' => $idLote,
                        'id_ubicacion' => 1
                    ],
                    ['cantidad' => 0]
                );

                $stockActual->incrementarStock($detalle->cantidad);

                MovimientoInventario::create([
                    'fecha' => now(),
                    'id_tipo_movimiento' => 30,
                    'referencia' => 'Confirmación compra #' . $compra->nro_documento,
                    'id_producto' => $detalle->id_producto,
                    'id_lote' => $idLote,
                    'cantidad_entrada' => $detalle->cantidad,
                    'costo_unitario' => $detalle->precio_unitario,
                    'costo_total' => $detalle->cantidad * $detalle->precio_unitario,
                    'id_usuario' => Auth::id(),
                    'id_compra' => $compra->id,
                    'id_ubicacion' => 1
                ]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Compra confirmada exitosamente',
                'data' => $compra->refresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al confirmar compra:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function anular(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $compra = Compra::findOrFail($id);
            
            if ($compra->id_estado_compra != 25) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden anular compras en estado CONFIRMADO'
                ], 400);
            }

            DB::beginTransaction();
            
            $compra->id_estado_compra = 26;
            $observacionAnulacion = "ANULADO - Motivo: " . $request->motivo . " - Fecha: " . now()->format('Y-m-d H:i:s');
            $compra->observacion = $compra->observacion ? $compra->observacion . "\n" . $observacionAnulacion : $observacionAnulacion;
            $compra->save();

            foreach ($compra->detalles as $detalle) {
                $stockActual = StockActual::where('id_producto', $detalle->id_producto)
                    ->where('id_lote', null)
                    ->where('id_ubicacion', 1)
                    ->first();

                if ($stockActual) {
                    $stockActual->decrementarStock($detalle->cantidad);
                }

                MovimientoInventario::create([
                    'fecha' => now(),
                    'id_tipo_movimiento' => 32,
                    'referencia' => 'Anulación compra #' . $compra->nro_documento . ' - ' . $request->motivo,
                    'id_producto' => $detalle->id_producto,
                    'cantidad_salida' => $detalle->cantidad,
                    'costo_unitario' => $detalle->precio_unitario,
                    'costo_total' => $detalle->cantidad * $detalle->precio_unitario,
                    'id_usuario' => Auth::id(),
                    'id_compra' => $compra->id
                ]);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Compra anulada exitosamente',
                'data' => $compra->refresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al anular compra:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al anular compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $compra = Compra::findOrFail($id);
            
            if ($compra->id_estado_compra != 24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar compras en estado BORRADOR'
                ], 400);
            }

            DB::beginTransaction();
            
            CompraDetalle::where('id_compra', $id)->delete();
            $compra->delete();

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Compra eliminada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar compra:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generarNumeroDocumento()
    {
        try {
            $empresaId = Auth::user()->id_empresa ?? 1;
            $sucursalId = Auth::user()->id_sucursal ?? 1;
            
            $ultimaCompra = Compra::where('id_empresa', $empresaId)
                ->where('id_sucursal', $sucursalId)
                ->whereYear('created_at', date('Y'))
                ->orderBy('id', 'desc')
                ->first();
            
            if ($ultimaCompra && $ultimaCompra->nro_documento) {
                $parts = explode('-', $ultimaCompra->nro_documento);
                if (count($parts) >= 3) {
                    $ultimoNumero = end($parts);
                    $numeroSecuencial = intval($ultimoNumero) + 1;
                } else {
                    $numeroSecuencial = 1;
                }
            } else {
                $numeroSecuencial = 1;
            }
            
            $numeroDocumento = sprintf('CMP-%s-%06d', date('Y'), $numeroSecuencial);
            
            return response()->json([
                'success' => true,
                'data' => $numeroDocumento
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al generar número de documento:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al generar número',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEstadosCompra()
    {
        try {
            $estados = Subdominio::where('id_dominio', 8)->get();
            return response()->json([
                'success' => true,
                'data' => $estados
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getEstadosCompra:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estados de compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function buscarProductos(Request $request)
    {
        try {
            $term = $request->search ?? '';
            
            $productos = Producto::where('id_estado_producto', 22)
                ->where(function($q) use ($term) {
                    $q->where('nombre', 'like', "%{$term}%")
                      ->orWhere('codigo_interno', 'like', "%{$term}%")
                      ->orWhere('codigo_barras', 'like', "%{$term}%");
                })
                ->with(['unidadMedida', 'categoria'])
                ->limit(20)
                ->get();

            $formatted = $productos->map(function($producto) {
                return [
                    'value' => $producto->id,
                    'label' => $producto->nombre . ($producto->codigo_interno ? ' (' . $producto->codigo_interno . ')' : ''),
                    'nombre' => $producto->nombre,
                    'precio_entrada' => $producto->precio_entrada,
                    'codigo_interno' => $producto->codigo_interno,
                    'categoria' => $producto->categoria ? $producto->categoria->descripcion : 'Sin categoría',
                    'unidad' => $producto->unidadMedida ? $producto->unidadMedida->descripcion : 'Sin unidad'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en buscarProductos:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function verificarEstructura()
    {
        try {
            $estructura = [
                'compras' => Schema::getColumnListing('compras'),
                'compras_detalle' => Schema::getColumnListing('compras_detalle'),
                'lotes' => Schema::getColumnListing('lotes'),
                'stock_actual' => Schema::getColumnListing('stock_actual'),
                'productos' => Schema::getColumnListing('productos'),
                'producto_precios' => Schema::getColumnListing('producto_precios')
            ];
            
            return response()->json([
                'success' => true,
                'data' => $estructura,
                'message' => 'Estructura de tablas verificada'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar estructura',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}