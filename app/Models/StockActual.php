<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockActual extends Model
{
    protected $table = 'stock_actual';
    
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
        'cantidad' => 'integer',
        'updated_at' => 'datetime'
    ];

    // Desactivar created_at ya que solo usamos updated_at
    const CREATED_AT = null;

    // Relaciones
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

    // Scopes para filtros comunes
    public function scopePorProducto($query, $productoId)
    {
        return $query->where('id_producto', $productoId);
    }

    public function scopePorLote($query, $loteId)
    {
        return $query->where('id_lote', $loteId);
    }

    public function scopePorUbicacion($query, $ubicacionId)
    {
        return $query->where('id_ubicacion', $ubicacionId);
    }

    public function scopeConStock($query)
    {
        return $query->where('cantidad', '>', 0);
    }

    public function scopeSinStock($query)
    {
        return $query->where('cantidad', '<=', 0);
    }

    public function scopeConRelaciones($query)
    {
        return $query->with(['producto', 'lote', 'ubicacion']);
    }

    public function scopeBajoStockMinimo($query)
    {
        return $query->whereHas('producto', function($q) {
            $q->whereRaw('stock_actual.cantidad <= productos.stock_minimo');
        });
    }

    public function scopeOrdenadoPorCantidad($query, $orden = 'desc')
    {
        return $query->orderBy('cantidad', $orden);
    }

    public function scopeOrdenadoPorProducto($query)
    {
        return $query->join('productos', 'stock_actual.id_producto', '=', 'productos.id')
                    ->orderBy('productos.nombre')
                    ->select('stock_actual.*');
    }

    // Métodos helper para verificar estado
    public function tieneStock()
    {
        return $this->cantidad > 0;
    }

    public function estaSinStock()
    {
        return $this->cantidad <= 0;
    }

    public function estaBajoStockMinimo()
    {
        if (!$this->producto) {
            return false;
        }
        return $this->cantidad <= $this->producto->stock_minimo;
    }

    public function puedeDespachar($cantidadRequerida)
    {
        return $this->cantidad >= $cantidadRequerida;
    }

    // Accessors para obtener información
    public function getProductoNombreAttribute()
    {
        return $this->producto?->nombre ?? 'N/A';
    }

    public function getLoteNumeroAttribute()
    {
        return $this->lote?->numero_lote ?? 'N/A';
    }

    public function getUbicacionNombreAttribute()
    {
        return $this->ubicacion?->descripcion ?? 'N/A';
    }

    public function getStockMinimoProductoAttribute()
    {
        return $this->producto?->stock_minimo ?? 0;
    }

    public function getDiferenciaStockMinimoAttribute()
    {
        return $this->cantidad - $this->stockMinimoProducto;
    }

    public function getPorcentajeStockMinimoAttribute()
    {
        $stockMinimo = $this->stockMinimoProducto;
        
        if ($stockMinimo == 0) {
            return 100;
        }
        
        return ($this->cantidad / $stockMinimo) * 100;
    }

    public function getAlertaStockAttribute()
    {
        if ($this->estaSinStock()) {
            return 'sin_stock';
        }

        $porcentaje = $this->porcentajeStockMinimo;

        if ($porcentaje <= 50) {
            return 'critico'; // 50% o menos del stock mínimo
        } elseif ($porcentaje <= 100) {
            return 'advertencia'; // Entre 51% y 100% del stock mínimo
        } else {
            return 'normal'; // Por encima del stock mínimo
        }
    }

    // Accessor para mostrar información completa del stock
    public function getInfoCompletaAttribute()
    {
        return [
            'id' => $this->id,
            'producto' => $this->productoNombre,
            'lote' => $this->loteNumero,
            'ubicacion' => $this->ubicacionNombre,
            'cantidad' => $this->cantidad,
            'stock_minimo' => $this->stockMinimoProducto,
            'diferencia_stock_minimo' => $this->diferenciaStockMinimo,
            'porcentaje_stock_minimo' => round($this->porcentajeStockMinimo, 2) . '%',
            'alerta_stock' => $this->alertaStock,
            'tiene_stock' => $this->tieneStock(),
            'bajo_stock_minimo' => $this->estaBajoStockMinimo(),
            'ultima_actualizacion' => $this->updated_at->format('d/m/Y H:i:s')
        ];
    }

    // Métodos de gestión de stock
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

    public function ajustarStock($nuevaCantidad, $motivo = null)
    {
        $cantidadAnterior = $this->cantidad;
        $this->cantidad = $nuevaCantidad;
        $this->save();
        
        // Aquí puedes registrar el ajuste en una tabla de auditoría
        // con el motivo y la diferencia
        
        return [
            'cantidad_anterior' => $cantidadAnterior,
            'cantidad_nueva' => $nuevaCantidad,
            'diferencia' => $nuevaCantidad - $cantidadAnterior,
            'motivo' => $motivo
        ];
    }

    public function transferirStock($ubicacionDestinoId, $cantidad)
    {
        if (!$this->puedeDespachar($cantidad)) {
            throw new \Exception('Stock insuficiente para transferir');
        }

        // Decrementar stock en ubicación origen
        $this->decrementarStock($cantidad);

        // Incrementar o crear stock en ubicación destino
        $stockDestino = self::firstOrCreate(
            [
                'id_producto' => $this->id_producto,
                'id_lote' => $this->id_lote,
                'id_ubicacion' => $ubicacionDestinoId
            ],
            ['cantidad' => 0]
        );

        $stockDestino->incrementarStock($cantidad);

        return [
            'origen' => $this,
            'destino' => $stockDestino,
            'cantidad_transferida' => $cantidad
        ];
    }

    // Event observers
    protected static function boot()
    {
        parent::boot();

        // Después de actualizar, validar el estado del lote
        static::updated(function ($stock) {
            if ($stock->lote) {
                $stock->lote->validarEstado();
            }
        });

        // Antes de eliminar, validar que no tenga stock
        static::deleting(function ($stock) {
            if ($stock->cantidad > 0) {
                throw new \Exception('No se puede eliminar un registro con stock disponible');
            }
        });
    }

    // Métodos estáticos útiles
    public static function obtenerStockProducto($productoId, $ubicacionId = null)
    {
        $query = self::where('id_producto', $productoId)->conStock();
        
        if ($ubicacionId) {
            $query->where('id_ubicacion', $ubicacionId);
        }
        
        return $query->sum('cantidad');
    }

    public static function obtenerStockLote($loteId, $ubicacionId = null)
    {
        $query = self::where('id_lote', $loteId)->conStock();
        
        if ($ubicacionId) {
            $query->where('id_ubicacion', $ubicacionId);
        }
        
        return $query->sum('cantidad');
    }

    public static function obtenerStockUbicacion($ubicacionId)
    {
        return self::where('id_ubicacion', $ubicacionId)
                  ->conStock()
                  ->conRelaciones()
                  ->get();
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