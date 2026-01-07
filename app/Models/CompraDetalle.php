<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    protected $table = 'compras_detalle';
    
    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'descuento_pct',
        'descuento_monto',
        'subtotal'
    ];

    protected $casts = [
        'id_compra' => 'integer',
        'id_producto' => 'integer',
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento_pct' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public $timestamps = true;

    // Relaciones
    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    // Scopes para filtros comunes
    public function scopePorCompra($query, $compraId)
    {
        return $query->where('id_compra', $compraId);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('id_producto', $productoId);
    }

    public function scopeConRelaciones($query)
    {
        return $query->with(['compra', 'producto']);
    }

    public function scopeConProducto($query)
    {
        return $query->with('producto');
    }

    public function scopeOrdenadoPorProducto($query)
    {
        return $query->join('productos', 'compras_detalle.id_producto', '=', 'productos.id')
                    ->orderBy('productos.nombre')
                    ->select('compras_detalle.*');
    }

    // Accessors para obtener información relacionada
    public function getProductoNombreAttribute()
    {
        return $this->producto?->nombre ?? 'N/A';
    }

    public function getProductoCodigoAttribute()
    {
        return $this->producto?->codigo_interno ?? 'N/A';
    }

    public function getTotalSinDescuentoAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }

    public function getTotalConDescuentoAttribute()
    {
        return $this->subtotal;
    }

    public function getDescuentoAplicadoAttribute()
    {
        return $this->descuento_monto;
    }

    // Accessor para mostrar información completa del detalle
    public function getInfoCompletaAttribute()
    {
        return [
            'id' => $this->id,
            'producto' => $this->productoNombre,
            'codigo_producto' => $this->productoCodigo,
            'cantidad' => $this->cantidad,
            'precio_unitario' => $this->precio_unitario,
            'total_sin_descuento' => $this->totalSinDescuento,
            'descuento_pct' => $this->descuento_pct,
            'descuento_monto' => $this->descuento_monto,
            'subtotal' => $this->subtotal
        ];
    }

    // Métodos de cálculo
    public function calcularSubtotal()
    {
        $totalSinDescuento = $this->cantidad * $this->precio_unitario;
        
        // Si tiene porcentaje de descuento, calcular el monto
        if ($this->descuento_pct > 0) {
            $this->descuento_monto = ($totalSinDescuento * $this->descuento_pct) / 100;
        }
        
        $this->subtotal = $totalSinDescuento - $this->descuento_monto;
        
        return $this;
    }

    public function calcularDescuentoPorcentaje()
    {
        $totalSinDescuento = $this->cantidad * $this->precio_unitario;
        
        if ($totalSinDescuento > 0 && $this->descuento_monto > 0) {
            $this->descuento_pct = ($this->descuento_monto / $totalSinDescuento) * 100;
        } else {
            $this->descuento_pct = 0;
        }
        
        return $this;
    }

    public function calcularDescuentoMonto()
    {
        $totalSinDescuento = $this->cantidad * $this->precio_unitario;
        
        if ($this->descuento_pct > 0) {
            $this->descuento_monto = ($totalSinDescuento * $this->descuento_pct) / 100;
        }
        
        return $this;
    }

    public function obtenerPrecioUnitarioConDescuento()
    {
        if ($this->cantidad == 0) {
            return 0;
        }
        
        return $this->subtotal / $this->cantidad;
    }

    // Métodos de validación
    public function validarCantidad()
    {
        return $this->cantidad > 0;
    }

    public function validarPrecio()
    {
        return $this->precio_unitario >= 0;
    }

    public function validarDescuento()
    {
        return $this->descuento_pct >= 0 && $this->descuento_pct <= 100;
    }

    public function esValido()
    {
        return $this->validarCantidad() && 
               $this->validarPrecio() && 
               $this->validarDescuento();
    }

    // Event observers (se pueden definir en un Observer separado)
    protected static function boot()
    {
        parent::boot();

        // Antes de crear, calcular subtotal
        static::creating(function ($detalle) {
            $detalle->calcularSubtotal();
        });

        // Antes de actualizar, recalcular subtotal
        static::updating(function ($detalle) {
            $detalle->calcularSubtotal();
        });

        // Después de crear, actualizar totales de la compra
        static::created(function ($detalle) {
            $detalle->compra->calcularTotales();
        });

        // Después de actualizar, actualizar totales de la compra
        static::updated(function ($detalle) {
            $detalle->compra->calcularTotales();
        });

        // Después de eliminar, actualizar totales de la compra
        static::deleted(function ($detalle) {
            $detalle->compra->calcularTotales();
        });
    }

    // Métodos estáticos útiles
    public static function crearDetalle($compraId, $productoId, $cantidad, $precioUnitario, $descuentoPct = 0, $descuentoMonto = 0)
    {
        $detalle = new self();
        $detalle->id_compra = $compraId;
        $detalle->id_producto = $productoId;
        $detalle->cantidad = $cantidad;
        $detalle->precio_unitario = $precioUnitario;
        $detalle->descuento_pct = $descuentoPct;
        $detalle->descuento_monto = $descuentoMonto;
        $detalle->calcularSubtotal();
        $detalle->save();
        
        return $detalle;
    }

    public static function obtenerDetallesPorCompra($compraId)
    {
        return self::where('id_compra', $compraId)
                  ->with('producto')
                  ->orderBy('id')
                  ->get();
    }

    public static function obtenerTotalPorCompra($compraId)
    {
        return self::where('id_compra', $compraId)->sum('subtotal');
    }

    public static function obtenerCantidadItemsPorCompra($compraId)
    {
        return self::where('id_compra', $compraId)->count();
    }

    public static function obtenerCantidadTotalPorCompra($compraId)
    {
        return self::where('id_compra', $compraId)->sum('cantidad');
    }

    public static function obtenerProductosMasComprados($limite = 10, $fechaInicio = null, $fechaFin = null)
    {
        $query = self::selectRaw('id_producto, SUM(cantidad) as total_cantidad, COUNT(*) as veces_comprado, SUM(subtotal) as total_invertido')
                    ->groupBy('id_producto')
                    ->orderByDesc('total_cantidad')
                    ->limit($limite);

        if ($fechaInicio && $fechaFin) {
            $query->whereHas('compra', function($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_compra', [$fechaInicio, $fechaFin])
                  ->where('id_estado_compra', 25); // Solo confirmadas
            });
        }

        return $query->with('producto')->get();
    }

    public static function calcularPromedioCompra($productoId, $meses = 3)
    {
        $fechaInicio = now()->subMonths($meses)->startOfMonth();
        
        return self::where('id_producto', $productoId)
                  ->whereHas('compra', function($q) use ($fechaInicio) {
                      $q->where('fecha_compra', '>=', $fechaInicio)
                        ->where('id_estado_compra', 25); // Solo confirmadas
                  })
                  ->avg('cantidad') ?? 0;
    }

    public static function obtenerUltimoPrecioCompra($productoId)
    {
        $ultimoDetalle = self::where('id_producto', $productoId)
                            ->whereHas('compra', function($q) {
                                $q->where('id_estado_compra', 25); // Solo confirmadas
                            })
                            ->orderByDesc('created_at')
                            ->first();

        return $ultimoDetalle?->precio_unitario ?? 0;
    }

    public static function obtenerHistorialPrecios($productoId, $limite = 10)
    {
        return self::where('id_producto', $productoId)
                  ->whereHas('compra', function($q) {
                      $q->where('id_estado_compra', 25); // Solo confirmadas
                  })
                  ->with(['compra' => function($q) {
                      $q->select('id', 'fecha_compra', 'id_proveedor')
                        ->with('proveedor:id,nombres,apellido_paterno,apellido_materno');
                  }])
                  ->orderByDesc('created_at')
                  ->limit($limite)
                  ->get(['id', 'id_compra', 'id_producto', 'cantidad', 'precio_unitario', 'descuento_monto', 'subtotal', 'created_at']);
    }

    // Método para duplicar detalle (útil para copiar compras)
    public function duplicar($nuevaCompraId)
    {
        $nuevoDetalle = $this->replicate();
        $nuevoDetalle->id_compra = $nuevaCompraId;
        $nuevoDetalle->save();
        
        return $nuevoDetalle;
    }

    // Método para actualizar precio del producto basado en la compra
    public function actualizarPrecioProducto($actualizarPrecioEntrada = true)
    {
        if (!$actualizarPrecioEntrada || !$this->producto) {
            return false;
        }

        $this->producto->update([
            'precio_entrada' => $this->precio_unitario
        ]);

        return true;
    }
}