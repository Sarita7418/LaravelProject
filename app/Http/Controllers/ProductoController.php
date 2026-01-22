<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Subdominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->has('search') && $request->wantsJson()) {
                return $this->buscarProductos($request);
            }

            $query = Producto::with([
                'categoria', 
                'unidadMedida', 
                'unidadVenta',
                'estadoProducto',
                'liname.generico',
                'liname.clasificacion'
            ])
            ->withSum('stocks', 'cantidad')
            ->where('id_estado_producto', 22);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo_interno', 'like', "%{$search}%")
                      ->orWhereHas('liname.generico', function($q2) use ($search) {
                          $q2->where('nombre', 'like', "%{$search}%");
                      });
                });
            }

            $productos = $query->orderBy('nombre')->get();

            return response()->json([
                'success' => true,
                'data' => $productos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en index de productos:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function buscarProductos(Request $request)
    {
        try {
            $search = $request->get('search', '');
            
            $productos = Producto::with([
                'categoria', 
                'unidadMedida',
                'unidadVenta',
                'liname.generico'
            ])
                ->where('id_estado_producto', 22)
                ->where(function($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                          ->orWhere('codigo_interno', 'like', "%{$search}%")
                          ->orWhereHas('liname.generico', function($q) use ($search) {
                              $q->where('nombre', 'like', "%{$search}%");
                          });
                })
                ->limit(20)
                ->get();

            $formatted = $productos->map(function($producto) {
                $nombre = $producto->nombre;
                if ($producto->liname && $producto->liname->generico) {
                    $nombre .= " ({$producto->liname->generico->nombre})";
                }
                
                return [
                    'id' => $producto->id,
                    'value' => $producto->id,
                    'label' => $nombre,
                    'nombre' => $producto->nombre,
                    'precio_entrada' => $producto->precio_entrada,
                    'codigo_interno' => $producto->codigo_interno,
                    'categoria' => $producto->categoria ? $producto->categoria->descripcion : 'Sin categoría',
                    'unidad' => $producto->unidadMedida ? $producto->unidadMedida->descripcion : 'Sin unidad',
                    'unidad_venta' => $producto->unidadVenta ? $producto->unidadVenta->descripcion : 'Sin unidad',
                    'unidades_empaque' => $producto->unidades_empaque ?? 1,
                    'medicamento_liname' => $producto->liname ? [
                        'id' => $producto->liname->id,
                        'codigo' => $producto->liname->codigo_completo,
                        'generico' => $producto->liname->generico->nombre ?? null,
                        'forma' => $producto->liname->forma_farmaceutica,
                        'concentracion' => $producto->liname->concentracion
                    ] : null
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

    public function show($id)
    {
        try {
            $producto = Producto::with([
                'categoria', 
                'unidadMedida', 
                'unidadVenta',
                'estadoProducto',
                'liname.generico',
                'liname.clasificacion'
            ])
                ->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $producto
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en show producto:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'id_categoria' => 'required|exists:subdominios,id',
            'id_unidad_medida' => 'required|exists:subdominios,id',
            'id_unidad_venta' => 'required|exists:subdominios,id',
            'precio_entrada' => 'nullable|numeric|min:0',
            'precio_salida' => 'nullable|numeric|min:0',
            'codigo_interno' => 'nullable|string|max:50|unique:productos,codigo_interno',
            'stock_minimo' => 'nullable|integer|min:0',
            'unidades_empaque' => 'required|integer|min:1',
            'id_medicamento_liname' => 'nullable|exists:medicamentos_liname,id'
        ]);

        DB::beginTransaction();
        try {
            $producto = Producto::create([
                'nombre' => $request->nombre,
                'id_categoria' => $request->id_categoria,
                'id_unidad_medida' => $request->id_unidad_medida,
                'id_unidad_venta' => $request->id_unidad_venta,
                'precio_entrada' => $request->precio_entrada ?? 0,
                'precio_salida' => $request->precio_salida ?? 0,
                'codigo_interno' => $request->codigo_interno,
                'stock_minimo' => $request->stock_minimo ?? 0,
                'unidades_empaque' => $request->unidades_empaque,
                'id_estado_producto' => 22,
                'rastrea_inventario' => true,
                'id_medicamento_liname' => $request->id_medicamento_liname
            ]);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => $producto->load(['categoria', 'unidadMedida', 'unidadVenta', 'liname.generico'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store producto:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCategorias()
    {
        try {
            $categorias = Subdominio::where('id_dominio', 5)
                ->select('id', 'descripcion as label', 'id as value')
                ->orderBy('descripcion')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $categorias
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getCategorias:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar categorías',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnidades()
    {
        try {
            $unidades = Subdominio::where('id_dominio', 6)
                ->select('id', 'descripcion as label', 'id as value')
                ->orderBy('descripcion')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $unidades
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getUnidades:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar unidades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnidadesVenta()
    {
        try {
            // Usamos el mismo dominio 6 pero podrías filtrar específicamente
            $unidadesVenta = Subdominio::where('id_dominio', 6)
                ->whereIn('id', [15, 17, 18, 21]) // UNIDAD, BLISTER, TABLETA, SOBRE
                ->select('id', 'descripcion as label', 'id as value')
                ->orderBy('descripcion')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $unidadesVenta
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getUnidadesVenta:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar unidades de venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEstados()
    {
        try {
            $estados = Subdominio::where('id_dominio', 7)
                ->select('id', 'descripcion as label', 'id as value')
                ->orderBy('descripcion')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $estados
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en getEstados:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estados',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
