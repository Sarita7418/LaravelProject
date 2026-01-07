<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockActual extends Model
{
    protected $table = 'stock_actual';
    public $timestamps = false;
    protected $fillable = [
        'id_producto',
        'id_lote',
        'id_ubicacion',
        'cantidad'
    ];

    protected $casts = [
        'id_producto' => 'integer',
        'id_lote' => 'integer',
        'id_ubicacion' => 'integer',
        'cantidad' => 'integer'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function ubicacion()
    {
        return $this->belongsTo(PoliticoUbicacion::class, 'id_ubicacion');
    }

    public function incrementarStock($cantidad)
    {
        $this->cantidad += $cantidad;
        $this->save();
        return $this;
    }

    public function decrementarStock($cantidad)
    {
        if ($this->cantidad < $cantidad) {
            throw new \Exception('Stock insuficiente. Disponible: ' . $this->cantidad . ', Requerido: ' . $cantidad);
        }
        
        $this->cantidad -= $cantidad;
        $this->save();
        return $this;
    }

    public static function obtenerStockProducto($productoId)
    {
        return self::where('id_producto', $productoId)->sum('cantidad');
    }

    public static function buscarStock($productoId, $loteId, $ubicacionId)
    {
        return self::where('id_producto', $productoId)
                  ->where('id_lote', $loteId)
                  ->where('id_ubicacion', $ubicacionId)
                  ->first();
    }

    public static function crearOActualizarStock($productoId, $loteId, $ubicacionId, $cantidad)
    {
        $stock = self::firstOrCreate(
            [
                'id_producto' => $productoId,
                'id_lote' => $loteId,
                'id_ubicacion' => $ubicacionId
            ],
            ['cantidad' => 0]
        );

        $stock->cantidad = $cantidad;
        $stock->save();

        return $stock;
    }


    public static function registrarEntrada($productoId, $loteId, $ubicacionId, $cantidad)
    {
        $stock = self::firstOrCreate(
            [
                'id_producto' => $productoId,
                'id_lote' => $loteId,
                'id_ubicacion' => $ubicacionId
            ],
            ['cantidad' => 0]
        );

        $stock->incrementarStock($cantidad);

        return $stock;
    }

    public static function registrarSalida($productoId, $loteId, $ubicacionId, $cantidad)
    {
        $stock = self::buscarStock($productoId, $loteId, $ubicacionId);

        if (!$stock) {
            throw new \Exception('No existe stock en esta ubicación');
        }

        $stock->decrementarStock($cantidad);

        return $stock;
    }

    public static function obtenerProductosBajoStockMinimo()
    {
        return self::bajoStockMinimo()
                  ->conRelaciones()
                  ->ordenadoPorProducto()
                  ->get();
    }

    public static function obtenerProductosSinStock()
    {
        return self::sinStock()
                  ->conRelaciones()
                  ->ordenadoPorProducto()
                  ->get();
    }

    public static function obtenerResumenStockPorProducto($productoId)
    {
        $stocks = self::where('id_producto', $productoId)
                     ->conStock()
                     ->with(['lote', 'ubicacion'])
                     ->get();

        $totalStock = $stocks->sum('cantidad');
        $producto = Producto::find($productoId);

        return [
            'producto_id' => $productoId,
            'producto_nombre' => $producto?->nombre,
            'stock_total' => $totalStock,
            'stock_minimo' => $producto?->stock_minimo ?? 0,
            'bajo_minimo' => $totalStock <= ($producto?->stock_minimo ?? 0),
            'cantidad_ubicaciones' => $stocks->count(),
            'cantidad_lotes' => $stocks->unique('id_lote')->count(),
            'detalle_por_ubicacion' => $stocks->map(function($stock) {
                return [
                    'ubicacion' => $stock->ubicacionNombre,
                    'lote' => $stock->loteNumero,
                    'cantidad' => $stock->cantidad
                ];
            })
        ];
    }

    public static function obtenerResumenStockPorUbicacion($ubicacionId)
    {
        $stocks = self::where('id_ubicacion', $ubicacionId)
                     ->conStock()
                     ->with(['producto', 'lote'])
                     ->get();

        $ubicacion = PoliticoUbicacion::find($ubicacionId);

        return [
            'ubicacion_id' => $ubicacionId,
            'ubicacion_nombre' => $ubicacion?->descripcion,
            'cantidad_productos' => $stocks->unique('id_producto')->count(),
            'cantidad_lotes' => $stocks->count(),
            'stock_total_items' => $stocks->sum('cantidad'),
            'productos_bajo_minimo' => $stocks->filter(function($stock) {
                return $stock->estaBajoStockMinimo();
            })->count(),
            'detalle_por_producto' => $stocks->groupBy('id_producto')->map(function($grupo) {
                $producto = $grupo->first()->producto;
                return [
                    'producto' => $producto->nombre,
                    'cantidad_total' => $grupo->sum('cantidad'),
                    'cantidad_lotes' => $grupo->count(),
                    'stock_minimo' => $producto->stock_minimo,
                    'bajo_minimo' => $grupo->sum('cantidad') <= $producto->stock_minimo
                ];
            })
        ];
    }

    public static function obtenerResumenGeneral()
    {
        $stocks = self::conStock()->conRelaciones()->get();

        return [
            'total_productos_con_stock' => $stocks->unique('id_producto')->count(),
            'total_ubicaciones_con_stock' => $stocks->unique('id_ubicacion')->count(),
            'total_lotes_activos' => $stocks->unique('id_lote')->count(),
            'total_items_stock' => $stocks->sum('cantidad'),
            'productos_bajo_minimo' => self::bajoStockMinimo()->count(),
            'productos_sin_stock' => self::sinStock()->count(),
            'valor_total_inventario' => $stocks->sum(function($stock) {
                return $stock->cantidad * ($stock->producto->precio_entrada ?? 0);
            })
        ];
    }

    public static function obtenerStockDisponibleParaVenta($productoId, $ubicacionId = null)
    {
        // Obtiene el stock disponible priorizando FEFO (First Expired First Out)
        $query = self::where('id_producto', $productoId)
                    ->conStock()
                    ->whereHas('lote', function($q) {
                        $q->activos();
                    })
                    ->with(['lote' => function($q) {
                        $q->orderBy('fecha_vencimiento', 'asc')
                          ->orderBy('fecha_ingreso', 'asc');
                    }]);

        if ($ubicacionId) {
            $query->where('id_ubicacion', $ubicacionId);
        }

        return $query->get()->sortBy(function($stock) {
            return [
                $stock->lote->fecha_vencimiento ?? '9999-12-31',
                $stock->lote->fecha_ingreso
            ];
        })->values();
    }

    public static function validarDisponibilidadParaVenta($productoId, $cantidadRequerida, $ubicacionId = null)
    {
        $stockDisponible = self::obtenerStockProducto($productoId, $ubicacionId);

        return [
            'disponible' => $stockDisponible >= $cantidadRequerida,
            'stock_actual' => $stockDisponible,
            'cantidad_requerida' => $cantidadRequerida,
            'diferencia' => $stockDisponible - $cantidadRequerida,
            'puede_despachar' => $stockDisponible >= $cantidadRequerida
        ];
    }

    public static function consolidarStockPorProducto()
    {
        return self::selectRaw('
                id_producto,
                SUM(cantidad) as total_stock,
                COUNT(DISTINCT id_lote) as total_lotes,
                COUNT(DISTINCT id_ubicacion) as total_ubicaciones
            ')
            ->conStock()
            ->groupBy('id_producto')
            ->with('producto')
            ->get();
    }

    public static function consolidarStockPorUbicacion()
    {
        return self::selectRaw('
                id_ubicacion,
                COUNT(DISTINCT id_producto) as total_productos,
                COUNT(DISTINCT id_lote) as total_lotes,
                SUM(cantidad) as total_items
            ')
            ->conStock()
            ->groupBy('id_ubicacion')
            ->with('ubicacion')
            ->get();
    }

    public static function limpiarStocksCero()
    {
        return self::where('cantidad', '<=', 0)->delete();
    }
}