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
        'id_estado_producto'
    ];

    protected $casts = [
        'id_categoria' => 'integer',
        'rastrea_inventario' => 'boolean',
        'id_unidad_medida' => 'integer',
        'precio_entrada' => 'decimal:2',
        'precio_salida' => 'decimal:2',
        'stock_minimo' => 'integer',
        'id_estado_producto' => 'integer'
    ];

    public $timestamps = true;

    // Relaciones con subdominios
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

    // ========== NUEVAS RELACIONES CON PRECIOS ==========
    
    public function precios()
    {
        return $this->hasMany(ProductoPrecio::class, 'id_producto');
    }

    public function preciosVigentes()
    {
        return $this->hasMany(ProductoPrecio::class, 'id_producto')
                    ->vigentes();
    }

    public function precioEntradaVigente()
    {
        return $this->hasOne(ProductoPrecio::class, 'id_producto')
                    ->where('id_tipo_precio', 33) // ID 33 = ENTRADA
                    ->vigentes()
                    ->ordenadoPorFecha('desc');
    }

    public function precioSalidaVigente()
    {
        return $this->hasOne(ProductoPrecio::class, 'id_producto')
                    ->where('id_tipo_precio', 34) // ID 34 = SALIDA
                    ->vigentes()
                    ->ordenadoPorFecha('desc');
    }

    public function preciosEntrada()
    {
        return $this->hasMany(ProductoPrecio::class, 'id_producto')
                    ->where('id_tipo_precio', 33);
    }

    public function preciosSalida()
    {
        return $this->hasMany(ProductoPrecio::class, 'id_producto')
                    ->where('id_tipo_precio', 34);
    }

    // Relaciones de inventario (mantener las que ya tenías)
    public function stocks()
    {
        return $this->hasMany(StockActual::class, 'id_producto');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'id_producto');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto');
    }

    // Scopes para filtros comunes
    public function scopeActivos($query)
    {
        return $query->where('id_estado_producto', 22); // ID 22 = ACTIVO
    }

    public function scopeInactivos($query)
    {
        return $query->where('id_estado_producto', 23); // ID 23 = INACTIVO
    }

    public function scopeBienes($query)
    {
        return $query->where('id_categoria', 13); // ID 13 = BIEN
    }

    public function scopeServicios($query)
    {
        return $query->where('id_categoria', 14); // ID 14 = SERVICIO
    }

    public function scopeConInventario($query)
    {
        return $query->where('rastrea_inventario', true);
    }

    public function scopeSinInventario($query)
    {
        return $query->where('rastrea_inventario', false);
    }

    public function scopeBuscarPorCodigo($query, $codigo)
    {
        return $query->where('codigo_interno', $codigo)
                    ->orWhere('codigo_barras', $codigo);
    }

    public function scopeBuscarPorNombre($query, $nombre)
    {
        return $query->where('nombre', 'LIKE', '%' . $nombre . '%');
    }

    public function scopeBajoStock($query)
    {
        return $query->whereHas('stocks', function($q) {
            $q->whereRaw('cantidad <= stock_minimo');
        });
    }

    public function scopeConPreciosVigentes($query)
    {
        return $query->with(['precioEntradaVigente', 'precioSalidaVigente']);
    }

    // Métodos helper para verificar estados
    public function estaActivo()
    {
        return $this->id_estado_producto === 22; // ID 22 = ACTIVO
    }

    public function esInactivo()
    {
        return $this->id_estado_producto === 23; // ID 23 = INACTIVO
    }

    public function esBien()
    {
        return $this->id_categoria === 13; // ID 13 = BIEN
    }

    public function esServicio()
    {
        return $this->id_categoria === 14; // ID 14 = SERVICIO
    }

    public function rastreInventario()
    {
        return $this->rastrea_inventario === true;
    }

    // Accessors para obtener descripciones de subdominios
    public function getCategoriaTextoAttribute()
    {
        return $this->categoria?->descripcion ?? 'N/A';
    }

    public function getUnidadMedidaTextoAttribute()
    {
        return $this->unidadMedida?->descripcion ?? 'N/A';
    }

    public function getEstadoTextoAttribute()
    {
        return $this->estadoProducto?->descripcion ?? 'N/A';
    }

    // ========== NUEVOS ACCESSORS PARA PRECIOS ==========
    
    public function getPrecioEntradaActualAttribute()
    {
        $precioVigente = $this->precioEntradaVigente;
        return $precioVigente?->precio ?? $this->precio_entrada;
    }

    public function getPrecioSalidaActualAttribute()
    {
        $precioVigente = $this->precioSalidaVigente;
        return $precioVigente?->precio ?? $this->precio_salida;
    }

    public function getStockTotalAttribute()
    {
        return $this->stocks()->sum('cantidad');
    }

    // Accessor para mostrar información completa del producto
    public function getInfoCompletaAttribute()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo_interno' => $this->codigo_interno,
            'codigo_barras' => $this->codigo_barras,
            'categoria' => $this->categoriaTexto,
            'unidad_medida' => $this->unidadMedidaTexto,
            'precio_entrada_cache' => $this->precio_entrada,
            'precio_salida_cache' => $this->precio_salida,
            'precio_entrada_actual' => $this->precioEntradaActual,
            'precio_salida_actual' => $this->precioSalidaActual,
            'stock_minimo' => $this->stock_minimo,
            'stock_total' => $this->stockTotal,
            'rastrea_inventario' => $this->rastrea_inventario ? 'Sí' : 'No',
            'estado' => $this->estadoTexto
        ];
    }

    // ========== NUEVOS MÉTODOS DE PRECIOS ==========
    
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
            tipoPrecio: 33, // ENTRADA
            precio: $precio,
            fechaInicio: $fechaInicio,
            fechaFin: $fechaFin
        );
    }

    public function registrarPrecioSalida($precio, $fechaInicio = null, $fechaFin = null)
    {
        return ProductoPrecio::registrarPrecio(
            productoId: $this->id,
            tipoPrecio: 34, // SALIDA
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

    // Métodos de cálculo (ACTUALIZADOS para usar precios actuales)
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

    // Métodos estáticos útiles
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

    // ========== NUEVOS MÉTODOS ESTÁTICOS PARA PRECIOS ==========
    
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

    // Event observers
    protected static function boot()
    {
        parent::boot();

        // Después de crear, registrar precios iniciales si tiene precios
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