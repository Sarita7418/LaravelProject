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
        return $query->whereHas('stockActual', function($q) {
            $q->whereRaw('cantidad <= stock_minimo');
        });
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
            'precio_entrada' => $this->precio_entrada,
            'precio_salida' => $this->precio_salida,
            'stock_minimo' => $this->stock_minimo,
            'rastrea_inventario' => $this->rastrea_inventario ? 'Sí' : 'No',
            'estado' => $this->estadoTexto
        ];
    }

    // Métodos de cálculo
    public function calcularMargen()
    {
        if ($this->precio_entrada == 0) {
            return 0;
        }
        
        return (($this->precio_salida - $this->precio_entrada) / $this->precio_entrada) * 100;
    }

    public function calcularUtilidad()
    {
        return $this->precio_salida - $this->precio_entrada;
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
}