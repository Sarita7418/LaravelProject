<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Subdominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Listar productos
     */
    public function index(Request $request)
    {
        try {
            // Búsqueda para select2/compras
            if ($request->has('search') && $request->wantsJson()) {
                return $this->buscarProductos($request);
            }

            // Listado normal
            $query = Producto::with(['categoria', 'unidadMedida', 'estadoProducto'])
                ->where('id_estado_producto', 22); // Solo activos

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo_interno', 'like', "%{$search}%")
                      ->orWhere('codigo_barras', 'like', "%{$search}%");
                });
            }

            $productos = $query->orderBy('nombre')->get();

            return response()->json([
                'success' => true,
                'data' => $productos
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para búsqueda de productos (para select2 en compras)
     */
    public function buscarProductos(Request $request)
    {
        try {
            $search = $request->get('search', '');
            
            $productos = Producto::with(['categoria', 'unidadMedida'])
                ->where('id_estado_producto', 22) // Activos
                ->where(function($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                          ->orWhere('codigo_interno', 'like', "%{$search}%")
                          ->orWhere('codigo_barras', 'like', "%{$search}%");
                })
                ->limit(20)
                ->get();

            // Formatear para Select2
            $formatted = $productos->map(function($producto) {
                return [
                    'value' => $producto->id,
                    'label' => $producto->nombre,
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
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $producto = Producto::with(['categoria', 'unidadMedida', 'estadoProducto'])
                ->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $producto
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    /**
     * Crear nuevo producto
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'id_categoria' => 'required|exists:subdominios,id',
            'id_unidad_medida' => 'required|exists:subdominios,id',
            'precio_entrada' => 'nullable|numeric|min:0',
            'precio_salida' => 'nullable|numeric|min:0',
            'codigo_interno' => 'nullable|string|max:50|unique:productos,codigo_interno',
            'codigo_barras' => 'nullable|string|max:50',
            'stock_minimo' => 'nullable|integer|min:0',
            'numero_lote' => 'nullable|string',
            'stock_inicial' => 'nullable|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // 1. Crear el producto
            $producto = Producto::create([
                'nombre' => $request->nombre,
                'id_categoria' => $request->id_categoria,
                'id_unidad_medida' => $request->id_unidad_medida,
                'precio_entrada' => $request->precio_entrada ?? 0, // Valor por defecto
                'precio_salida' => $request->precio_salida ?? 0,
                'codigo_interno' => $request->codigo_interno,
                'codigo_barras' => $request->codigo_barras,
                'stock_minimo' => $request->stock_minimo ?? 0,
                'id_estado_producto' => 22, // Activo
                'rastrea_inventario' => true,
            ]);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => $producto->load(['categoria', 'unidadMedida'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener categorías - VERSIÓN CORREGIDA (usa DB facade)
     */
    public function getCategorias()
    {
        try {
            // Usa DB facade para evitar problemas de relaciones
            $categorias = DB::table('subdominios')
                ->where('id_dominio', 5)
                ->select('id', 'descripcion')
                ->orderBy('descripcion')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $categorias
            ]);
            
        } catch (\Exception $e) {
            // Da más detalles para debug
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar categorías',
                'error' => $e->getMessage(),
                'debug' => 'Tabla: subdominios, id_dominio: 5'
            ], 500);
        }
    }

    /**
     * Obtener unidades de medida - VERSIÓN CORREGIDA (usa DB facade)
     */
    public function getUnidades()
    {
        try {
            // Usa DB facade para evitar problemas de relaciones
            $unidades = DB::table('subdominios')
                ->where('id_dominio', 6)
                ->select('id', 'descripcion')
                ->orderBy('descripcion')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $unidades
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar unidades',
                'error' => $e->getMessage(),
                'debug' => 'Tabla: subdominios, id_dominio: 6'
            ], 500);
        }
    }

    /**
     * Obtener estados de producto - VERSIÓN CORREGIDA (usa DB facade)
     */
    public function getEstados()
    {
        try {
            // Usa DB facade para evitar problemas de relaciones
            $estados = DB::table('subdominios')
                ->where('id_dominio', 7)
                ->select('id', 'descripcion')
                ->orderBy('descripcion')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $estados
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estados',
                'error' => $e->getMessage(),
                'debug' => 'Tabla: subdominios, id_dominio: 7'
            ], 500);
        }
    }

    /**
     * Método de diagnóstico - Para probar si la tabla subdominios tiene datos
     */
    public function diagnosticarSubdominios()
    {
        try {
            $total = DB::table('subdominios')->count();
            
            $porDominio = DB::table('subdominios')
                ->select('id_dominio', DB::raw('count(*) as total'))
                ->groupBy('id_dominio')
                ->get();
            
            $datosDominio5 = DB::table('subdominios')
                ->where('id_dominio', 5)
                ->get();
                
            $datosDominio6 = DB::table('subdominios')
                ->where('id_dominio', 6)
                ->get();
                
            $datosDominio7 = DB::table('subdominios')
                ->where('id_dominio', 7)
                ->get();
            
            return response()->json([
                'success' => true,
                'diagnostico' => [
                    'total_subdominios' => $total,
                    'distribucion_por_dominio' => $porDominio,
                    'dominio_5_categorias' => [
                        'total' => count($datosDominio5),
                        'datos' => $datosDominio5
                    ],
                    'dominio_6_unidades' => [
                        'total' => count($datosDominio6),
                        'datos' => $datosDominio6
                    ],
                    'dominio_7_estados' => [
                        'total' => count($datosDominio7),
                        'datos' => $datosDominio7
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en diagnóstico',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}