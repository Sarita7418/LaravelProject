<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    
    protected $fillable = [
        'nombre',
        'codigo_interno',
        'codigo_barras',
        'id_categoria',
        'rastrea_inventario',
        'id_unidad_medida',
        'precio_entrada',
        'precio_salida',
        'stock_minimo',
        'id_estado_producto',

        // ============================
        // NUEVOS CAMPOS PARA FARMACIA
        // ============================
        'id_medicamento_liname',
        'id_unidad_venta',
        'unidades_empaque'
    ];

    protected $appends = [
        'stock_total',
        'unidad_venta_texto',
        'factor_conversion'
    ];

    protected $casts = [
        'id_categoria' => 'integer',
        'rastrea_inventario' => 'boolean',
        'id_unidad_medida' => 'integer',
        'precio_entrada' => 'decimal:2',
        'precio_salida' => 'decimal:2',
        'stock_minimo' => 'integer',
        'id_estado_producto' => 'integer',
        'id_medicamento_liname' => 'integer',
        'id_unidad_venta' => 'integer',
        'unidades_empaque' => 'integer'
    ];

    public $timestamps = true;

    // ============================
    // RELACIONES EXISTENTES
    // ============================

    public function categoria()
    {
        return $this->belongsTo(Subdominio::class, 'id_categoria');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(Subdominio::class, 'id_unidad_medida');
    }

    public function estadoProducto()
    {
        return $this->belongsTo(Subdominio::class, 'id_estado_producto');
    }

    public function precios()
    {
        return $this->hasMany(ProductoPrecio::class, 'id_producto');
    }

    public function precioEntradaVigente()
    {
        return $this->hasOne(ProductoPrecio::class, 'id_producto')
                    ->where('id_tipo_precio', 33)
                    ->where('activo', true)
                    ->latest('fecha_inicio');
    }

    public function precioSalidaVigente()
    {
        return $this->hasOne(ProductoPrecio::class, 'id_producto')
                    ->where('id_tipo_precio', 34)
                    ->where('activo', true)
                    ->latest('fecha_inicio');
    }

    public function stocks()
    {
        return $this->hasMany(StockActual::class, 'id_producto');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'id_producto');
    }

    // ============================
    // NUEVAS RELACIONES
    // ============================

    public function liname()
    {
        return $this->belongsTo(MedicamentoLiname::class, 'id_medicamento_liname');
    }

    public function unidadVenta()
    {
        return $this->belongsTo(Subdominio::class, 'id_unidad_venta');
    }

    // ============================
    // ACCESSORS
    // ============================

    public function getCategoriaTextoAttribute()
    {
        return $this->categoria?->descripcion ?? 'N/A';
    }

    public function getUnidadMedidaTextoAttribute()
    {
        return $this->unidadMedida?->descripcion ?? 'N/A';
    }

    public function getUnidadVentaTextoAttribute()
    {
        return $this->unidadVenta?->descripcion ?? 'N/A';
    }

    public function getEstadoTextoAttribute()
    {
        return $this->estadoProducto?->descripcion ?? 'N/A';
    }

    public function getStockTotalAttribute()
    {
        return $this->stocks()->sum('cantidad');
    }

    public function getFactorConversionAttribute()
    {
        return $this->unidades_empaque ?: 1;
    }

    // ============================
    // INFORMACIÓN COMPLETA
    // ============================

    public function getInfoCompletaAttribute()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo_interno' => $this->codigo_interno,
            'codigo_barras' => $this->codigo_barras,
            'categoria' => $this->categoriaTexto,
            'unidad_medida' => $this->unidadMedidaTexto,
            'unidad_venta' => $this->unidadVentaTexto,
            'precio_entrada_cache' => $this->precio_entrada,
            'precio_salida_cache' => $this->precio_salida,
            'precio_entrada_actual' => $this->precioEntradaActual,
            'precio_salida_actual' => $this->precioSalidaActual,
            'stock_minimo' => $this->stock_minimo,
            'stock_total' => $this->stockTotal,
            'rastrea_inventario' => $this->rastrea_inventario ? 'Sí' : 'No',
            'estado' => $this->estadoTexto,
            'factor_conversion' => $this->factor_conversion
        ];
    }

    // ============================
    // MÉTODOS DE PRECIOS (ORIGINALES)
    // ============================

    public function obtenerPrecioEntradaActual()
    {
        return $this->precioEntradaActual;
    }

    public function obtenerPrecioSalidaActual()
    {
        return $this->precioSalidaActual;
    }

    public function tienePreciosVigentes()
    {
        return $this->preciosVigentes()->count() > 0;
    }

    public function registrarPrecioEntrada($precio, $fechaInicio = null, $fechaFin = null)
    {
        return ProductoPrecio::registrarPrecio(
            productoId: $this->id,
            tipoPrecio: 33,
            precio: $precio,
            fechaInicio: $fechaInicio,
            fechaFin: $fechaFin
        );
    }

    public function registrarPrecioSalida($precio, $fechaInicio = null, $fechaFin = null)
    {
        return ProductoPrecio::registrarPrecio(
            productoId: $this->id,
            tipoPrecio: 34,
            precio: $precio,
            fechaInicio: $fechaInicio,
            fechaFin: $fechaFin
        );
    }

    public function cambiarPrecioEntrada($nuevoPrecio, $fechaInicio = null)
    {
        return ProductoPrecio::cambiarPrecio(
            productoId: $this->id,
            tipoPrecio: 33,
            nuevoPrecio: $nuevoPrecio,
            fechaInicio: $fechaInicio
        );
    }

    public function cambiarPrecioSalida($nuevoPrecio, $fechaInicio = null)
    {
        return ProductoPrecio::cambiarPrecio(
            productoId: $this->id,
            tipoPrecio: 34,
            nuevoPrecio: $nuevoPrecio,
            fechaInicio: $fechaInicio
        );
    }

    public function obtenerHistorialPreciosEntrada()
    {
        return ProductoPrecio::obtenerHistorialPrecios($this->id, 33);
    }

    public function obtenerHistorialPreciosSalida()
    {
        return ProductoPrecio::obtenerHistorialPrecios($this->id, 34);
    }

    public function obtenerTodosLosPrecios()
    {
        return ProductoPrecio::obtenerPreciosVigentes($this->id);
    }

    // ============================
    // MÉTODOS DE CÁLCULO
    // ============================

    public function calcularMargen()
    {
        $precioEntrada = $this->precioEntradaActual;
        
        if ($precioEntrada == 0) {
            return 0;
        }
        
        return (($this->precioSalidaActual - $precioEntrada) / $precioEntrada) * 100;
    }

    public function calcularUtilidad()
    {
        return $this->precioSalidaActual - $this->precioEntradaActual;
    }

    public function calcularValorInventario()
    {
        $stockTotal = $this->stockTotal;
        $precioEntrada = $this->precioEntradaActual;
        
        return $stockTotal * $precioEntrada;
    }

    // ============================
    // MÉTODOS ESTÁTICOS
    // ============================

    public static function obtenerBienes()
    {
        return self::bienes()->activos()->orderBy('nombre')->get();
    }

    public static function obtenerServicios()
    {
        return self::servicios()->activos()->orderBy('nombre')->get();
    }

    public static function buscarPorCodigoONombre($termino)
    {
        return self::where('codigo_interno', 'LIKE', '%' . $termino . '%')
                   ->orWhere('codigo_barras', 'LIKE', '%' . $termino . '%')
                   ->orWhere('nombre', 'LIKE', '%' . $termino . '%')
                   ->activos()
                   ->get();
    }

    public static function contarPorCategoria()
    {
        return self::selectRaw('id_categoria, COUNT(*) as total')
                   ->groupBy('id_categoria')
                   ->with('categoria')
                   ->get()
                   ->pluck('total', 'categoria.descripcion')
                   ->toArray();
    }

    public static function contarPorEstado()
    {
        return [
            'activos' => self::where('id_estado_producto', 22)->count(),
            'inactivos' => self::where('id_estado_producto', 23)->count()
        ];
    }

    public static function obtenerProductosConPreciosVigentes()
    {
        return self::activos()
                   ->conPreciosVigentes()
                   ->orderBy('nombre')
                   ->get();
    }

    public static function obtenerProductosSinPreciosVigentes()
    {
        return self::activos()
                   ->whereDoesntHave('preciosVigentes')
                   ->orderBy('nombre')
                   ->get();
    }

    public static function obtenerProductosConPreciosPorVencer($dias = 30)
    {
        return self::activos()
                   ->whereHas('preciosVigentes', function($q) use ($dias) {
                       $q->whereNotNull('fecha_fin')
                         ->whereBetween('fecha_fin', [now(), now()->addDays($dias)]);
                   })
                   ->with('preciosVigentes')
                   ->get();
    }

    public static function calcularValorTotalInventario()
    {
        return self::activos()
                   ->conInventario()
                   ->get()
                   ->sum(function($producto) {
                       return $producto->calcularValorInventario();
                   });
    }

    public static function obtenerResumenPrecios()
    {
        $productos = self::activos()->conPreciosVigentes()->get();
        
        return [
            'total_productos' => $productos->count(),
            'con_precios_vigentes' => $productos->filter(fn($p) => $p->tienePreciosVigentes())->count(),
            'sin_precios_vigentes' => $productos->filter(fn($p) => !$p->tienePreciosVigentes())->count(),
            'precio_entrada_promedio' => $productos->avg('precioEntradaActual'),
            'precio_salida_promedio' => $productos->avg('precioSalidaActual'),
            'margen_promedio' => $productos->avg(fn($p) => $p->calcularMargen())
        ];
    }

    // ============================
    // OBSERVER
    // ============================

    protected static function boot()
    {
        parent::boot();

        static::created(function ($producto) {
            if ($producto->precio_entrada > 0) {
                $producto->registrarPrecioEntrada($producto->precio_entrada);
            }
            
            if ($producto->precio_salida > 0) {
                $producto->registrarPrecioSalida($producto->precio_salida);
            }
        });
    }
}
