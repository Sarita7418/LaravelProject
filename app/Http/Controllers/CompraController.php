<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use App\Models\Empresa;
use App\Models\Subdominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class CompraController extends Controller
{
    /**
     * Listar compras
     */
    public function index(Request $request)
    {
        try {
            // Obtener empresa y sucursal del usuario
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

            // Filtros
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

            // Paginación
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

    /**
     * Crear nueva compra - VERSIÓN SIMPLIFICADA Y FUNCIONAL
     */
    public function store(Request $request)
    {
        Log::info('=== INICIANDO STORE DE COMPRA ===');
        
        // Validación básica
        $validator = Validator::make($request->all(), [
            'id_proveedor' => 'required|exists:empresas,id',
            'fecha_compra' => 'required|date',
            'nro_documento' => 'required|string|max:50',
            'observacion' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01'
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
            // Calcular total
            $subtotal = 0;
            foreach ($validated['detalles'] as $det) {
                $subtotal += $det['cantidad'] * $det['precio_unitario'];
            }

            // Obtener empresa y sucursal del usuario
            $empresaId = Auth::user()->id_empresa ?? 1;
            $sucursalId = Auth::user()->id_sucursal ?? 1;

            Log::info('Creando compra', [
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'usuario_id' => Auth::id(),
                'total_detalles' => count($validated['detalles']),
                'subtotal' => $subtotal
            ]);

            // Datos para la compra - SOLO campos que existen
            $compraData = [
                'id_empresa' => $empresaId,
                'id_sucursal' => $sucursalId,
                'id_proveedor' => $validated['id_proveedor'],
                'id_usuario' => Auth::id(),
                'fecha_compra' => $validated['fecha_compra'],
                'nro_documento' => $validated['nro_documento'],
                'observacion' => $validated['observacion'] ?? null,
                'subtotal' => $subtotal,
                'descuento_total' => 0,
                'total_compra' => $subtotal,
                'id_estado_compra' => 24, // BORRADOR
            ];

            // Verificar campos que NO existen en la tabla y removerlos
            $tableColumns = Schema::getColumnListing('compras');
            Log::info('Columnas en tabla compras:', $tableColumns);
            
            // Asegurar que solo usamos campos que existen
            foreach ($compraData as $key => $value) {
                if (!in_array($key, $tableColumns)) {
                    unset($compraData[$key]);
                    Log::warning("Campo {$key} no existe en tabla, removido");
                }
            }

            Log::info('Datos finales para compra:', $compraData);

            // Crear la compra
            $compra = Compra::create($compraData);
            Log::info('Compra creada exitosamente', ['compra_id' => $compra->id]);

            // Crear detalles de la compra
            foreach ($validated['detalles'] as $det) {
                $subtotalDetalle = $det['cantidad'] * $det['precio_unitario'];
                
                // Buscar tabla de detalles
                $detallesTable = null;
                $possibleTables = ['compra_detalles', 'compras_detalle', 'detalles_compra', 'compra_detalle'];
                
                foreach ($possibleTables as $table) {
                    if (Schema::hasTable($table)) {
                        $detallesTable = $table;
                        break;
                    }
                }
                
                if ($detallesTable) {
                    DB::table($detallesTable)->insert([
                        'id_compra' => $compra->id,
                        'id_producto' => $det['id_producto'],
                        'cantidad' => $det['cantidad'],
                        'precio_unitario' => $det['precio_unitario'],
                        'descuento_pct' => 0,
                        'descuento_monto' => 0,
                        'subtotal' => $subtotalDetalle,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    Log::info('Detalle creado', [
                        'producto_id' => $det['id_producto'],
                        'cantidad' => $det['cantidad'],
                        'precio' => $det['precio_unitario'],
                        'subtotal' => $subtotalDetalle
                    ]);
                } else {
                    throw new \Exception("No se encontró la tabla de detalles de compra");
                }

                // Actualizar precio de entrada del producto en la tabla productos
                Producto::where('id', $det['id_producto'])
                    ->update(['precio_entrada' => $det['precio_unitario']]);
                    
                Log::info('Precio actualizado para producto', [
                    'producto_id' => $det['id_producto'],
                    'nuevo_precio' => $det['precio_unitario']
                ]);
            }

            DB::commit();
            
            Log::info('Compra registrada exitosamente', ['compra_id' => $compra->id]);
            
            // Cargar relaciones para respuesta
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
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except('detalles') // No loguear todos los detalles
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la compra: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar compra específica
     */
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

    /**
     * Confirmar compra
     */
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
            
            $compra->id_estado_compra = 25; // CONFIRMADO
            $compra->save();

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

    /**
     * Anular compra
     */
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
            
            $compra->id_estado_compra = 26; // ANULADO
            $observacionAnulacion = "ANULADO - Motivo: " . $request->motivo . " - Fecha: " . now()->format('Y-m-d H:i:s');
            $compra->observacion = $compra->observacion ? $compra->observacion . "\n" . $observacionAnulacion : $observacionAnulacion;
            $compra->save();

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

    /**
     * Eliminar compra (solo borradores)
     */
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
            
            // Buscar y eliminar detalles
            $possibleTables = ['compra_detalles', 'compras_detalle', 'detalles_compra', 'compra_detalle'];
            foreach ($possibleTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('id_compra', $id)->delete();
                    break;
                }
            }
            
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

    /**
     * Generar número de documento automático
     */
    public function generarNumeroDocumento()
    {
        try {
            // Obtener empresa y sucursal del usuario
            $empresaId = Auth::user()->id_empresa ?? 1;
            $sucursalId = Auth::user()->id_sucursal ?? 1;
            
            // Buscar el último número del año actual
            $ultimaCompra = Compra::where('id_empresa', $empresaId)
                ->where('id_sucursal', $sucursalId)
                ->whereYear('created_at', date('Y'))
                ->orderBy('id', 'desc')
                ->first();
            
            if ($ultimaCompra && $ultimaCompra->nro_documento) {
                // Intentar extraer el número secuencial
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

    /**
     * Obtener estados de compra
     */
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

    /**
     * Buscar productos para compras
     */
    public function buscarProductos(Request $request)
    {
        try {
            $term = $request->search ?? '';
            
            $productos = Producto::where('id_estado_producto', 22) // Activo
                ->where(function($q) use ($term) {
                    $q->where('nombre', 'like', "%{$term}%")
                      ->orWhere('codigo_interno', 'like', "%{$term}%")
                      ->orWhere('codigo_barras', 'like', "%{$term}%");
                })
                ->with(['unidadMedida', 'categoria'])
                ->limit(20)
                ->get();

            // Formatear para Select2
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
}