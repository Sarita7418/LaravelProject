<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductoPrecio extends Model
{
    protected $table = 'producto_precios';
    
    protected $fillable = [
        'id_producto',
        'id_tipo_precio',
        'precio',
        'fecha_inicio',
        'fecha_fin',
        'activo'
    ];

    protected $casts = [
        'id_producto' => 'integer',
        'id_tipo_precio' => 'integer',
        'precio' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean'
    ];

    public $timestamps = true;

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function tipoPrecio()
    {
        return $this->belongsTo(Subdominio::class, 'id_tipo_precio');
    }

    // Scopes para filtros comunes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('activo', false);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('id_producto', $productoId);
    }

    public function scopePreciosEntrada($query)
    {
        return $query->where('id_tipo_precio', 33); // ID 33 = ENTRADA
    }

    public function scopePreciosSalida($query)
    {
        return $query->where('id_tipo_precio', 34); // ID 34 = SALIDA
    }

    public function scopeVigentes($query, $fecha = null)
    {
        $fecha = $fecha ?? now();
        
        return $query->where('fecha_inicio', '<=', $fecha)
                    ->where(function($q) use ($fecha) {
                        $q->whereNull('fecha_fin')
                          ->orWhere('fecha_fin', '>=', $fecha);
                    })
                    ->where('activo', true);
    }

    public function scopeHistoricos($query, $fecha = null)
    {
        $fecha = $fecha ?? now();
        
        return $query->whereNotNull('fecha_fin')
                    ->where('fecha_fin', '<', $fecha);
    }

    public function scopeOrdenadoPorFecha($query, $orden = 'desc')
    {
        return $query->orderBy('fecha_inicio', $orden);
    }

    // Métodos helper
    public function esPrecioEntrada()
    {
        return $this->id_tipo_precio === 33; // ID 33 = ENTRADA
    }

    public function esPrecioSalida()
    {
        return $this->id_tipo_precio === 34; // ID 34 = SALIDA
    }

    public function estaVigente($fecha = null)
    {
        $fecha = $fecha ?? now();
        
        if (!$this->activo) {
            return false;
        }
        
        $inicioValido = $this->fecha_inicio->lte($fecha);
        $finValido = is_null($this->fecha_fin) || $this->fecha_fin->gte($fecha);
        
        return $inicioValido && $finValido;
    }

    public function tieneVigenciaIndefinida()
    {
        return is_null($this->fecha_fin);
    }

    public function diasVigencia()
    {
        if ($this->tieneVigenciaIndefinida()) {
            return null; // Vigencia indefinida
        }
        
        return $this->fecha_inicio->diffInDays($this->fecha_fin);
    }

    public function diasRestantes()
    {
        if (!$this->estaVigente() || $this->tieneVigenciaIndefinida()) {
            return null;
        }
        
        return now()->diffInDays($this->fecha_fin);
    }

    // Accessors
    public function getTipoPrecioTextoAttribute()
    {
        return $this->tipoPrecio?->descripcion ?? 'N/A';
    }

    public function getProductoNombreAttribute()
    {
        return $this->producto?->nombre ?? 'N/A';
    }

    public function getEstadoVigenciaAttribute()
    {
        if (!$this->activo) {
            return 'inactivo';
        }
        
        if ($this->estaVigente()) {
            return 'vigente';
        }
        
        if ($this->fecha_inicio->isFuture()) {
            return 'programado';
        }
        
        return 'vencido';
    }

    public function getInfoCompletaAttribute()
    {
        return [
            'id' => $this->id,
            'producto' => $this->productoNombre,
            'tipo_precio' => $this->tipoPrecioTexto,
            'precio' => $this->precio,
            'fecha_inicio' => $this->fecha_inicio->format('d/m/Y'),
            'fecha_fin' => $this->fecha_fin ? $this->fecha_fin->format('d/m/Y') : 'Indefinido',
            'dias_vigencia' => $this->diasVigencia(),
            'dias_restantes' => $this->diasRestantes(),
            'activo' => $this->activo,
            'estado_vigencia' => $this->estadoVigencia,
            'esta_vigente' => $this->estaVigente()
        ];
    }

    // Métodos de gestión
    public function finalizarVigencia($fecha = null)
    {
        $fecha = $fecha ?? now()->subDay(); // Finaliza ayer
        
        $this->update([
            'fecha_fin' => $fecha,
            'activo' => false
        ]);
        
        return $this;
    }

    public function extenderVigencia($nuevaFechaFin)
    {
        if (!$this->estaVigente()) {
            throw new \Exception('No se puede extender un precio que no está vigente');
        }
        
        $this->update([
            'fecha_fin' => $nuevaFechaFin
        ]);
        
        return $this;
    }

    public function activar()
    {
        $this->update(['activo' => true]);
        return $this;
    }

    public function desactivar()
    {
        $this->update(['activo' => false]);
        return $this;
    }

    // Event observers
    protected static function boot()
    {
        parent::boot();

        // Antes de crear, validar que no haya solapamiento de vigencias
        static::creating(function ($precio) {
            // Validar solapamiento
            $solapamiento = self::where('id_producto', $precio->id_producto)
                               ->where('id_tipo_precio', $precio->id_tipo_precio)
                               ->where('activo', true)
                               ->where(function($q) use ($precio) {
                                   $q->where(function($q2) use ($precio) {
                                       // Caso 1: El nuevo precio empieza durante un precio existente
                                       $q2->where('fecha_inicio', '<=', $precio->fecha_inicio)
                                          ->where(function($q3) use ($precio) {
                                              $q3->whereNull('fecha_fin')
                                                 ->orWhere('fecha_fin', '>=', $precio->fecha_inicio);
                                          });
                                   })->orWhere(function($q2) use ($precio) {
                                       // Caso 2: Un precio existente empieza durante el nuevo precio
                                       $q2->where('fecha_inicio', '>=', $precio->fecha_inicio);
                                       
                                       if ($precio->fecha_fin) {
                                           $q2->where('fecha_inicio', '<=', $precio->fecha_fin);
                                       }
                                   });
                               })
                               ->exists();
            
            if ($solapamiento) {
                throw new \Exception('Ya existe un precio vigente para este producto y tipo en el rango de fechas especificado');
            }
        });

        // Después de crear, actualizar el precio cache en productos
        static::created(function ($precio) {
            if ($precio->estaVigente()) {
                $precio->actualizarPrecioCache();
            }
        });

        // Después de actualizar, actualizar el precio cache
        static::updated(function ($precio) {
            if ($precio->estaVigente()) {
                $precio->actualizarPrecioCache();
            }
        });
    }

    // Métodos helper privados
    private function actualizarPrecioCache()
    {
        $producto = $this->producto;
        
        if (!$producto) {
            return;
        }
        
        if ($this->esPrecioEntrada()) {
            $producto->update(['precio_entrada' => $this->precio]);
        } elseif ($this->esPrecioSalida()) {
            $producto->update(['precio_salida' => $this->precio]);
        }
    }

    // Métodos estáticos útiles
    public static function registrarPrecio($productoId, $tipoPrecio, $precio, $fechaInicio = null, $fechaFin = null)
    {
        $fechaInicio = $fechaInicio ?? now();
        
        return self::create([
            'id_producto' => $productoId,
            'id_tipo_precio' => $tipoPrecio,
            'precio' => $precio,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'activo' => true
        ]);
    }

    public static function obtenerPrecioVigente($productoId, $tipoPrecio, $fecha = null)
    {
        return self::where('id_producto', $productoId)
                  ->where('id_tipo_precio', $tipoPrecio)
                  ->vigentes($fecha)
                  ->ordenadoPorFecha('desc')
                  ->first();
    }

    public static function obtenerHistorialPrecios($productoId, $tipoPrecio = null)
    {
        $query = self::where('id_producto', $productoId)
                    ->with('tipoPrecio')
                    ->ordenadoPorFecha('desc');
        
        if ($tipoPrecio) {
            $query->where('id_tipo_precio', $tipoPrecio);
        }
        
        return $query->get();
    }

    public static function cambiarPrecio($productoId, $tipoPrecio, $nuevoPrecio, $fechaInicio = null)
    {
        $fechaInicio = $fechaInicio ?? now();
        
        // Finalizar precio actual
        $precioActual = self::obtenerPrecioVigente($productoId, $tipoPrecio);
        
        if ($precioActual) {
            $precioActual->finalizarVigencia($fechaInicio->copy()->subDay());
        }
        
        // Crear nuevo precio
        return self::registrarPrecio($productoId, $tipoPrecio, $nuevoPrecio, $fechaInicio);
    }

    public static function obtenerPreciosVigentes($productoId)
    {
        return [
            'entrada' => self::obtenerPrecioVigente($productoId, 33), // ID 33 = ENTRADA
            'salida' => self::obtenerPrecioVigente($productoId, 34)   // ID 34 = SALIDA
        ];
    }

    public static function programarCambioPrecio($productoId, $tipoPrecio, $nuevoPrecio, $fechaCambio)
    {
        if (Carbon::parse($fechaCambio)->isPast()) {
            throw new \Exception('La fecha de cambio debe ser futura');
        }
        
        return self::registrarPrecio($productoId, $tipoPrecio, $nuevoPrecio, $fechaCambio);
    }

    public static function obtenerPrecioEnFecha($productoId, $tipoPrecio, $fecha)
    {
        return self::where('id_producto', $productoId)
                  ->where('id_tipo_precio', $tipoPrecio)
                  ->where('fecha_inicio', '<=', $fecha)
                  ->where(function($q) use ($fecha) {
                      $q->whereNull('fecha_fin')
                        ->orWhere('fecha_fin', '>=', $fecha);
                  })
                  ->where('activo', true)
                  ->ordenadoPorFecha('desc')
                  ->first();
    }

    public static function compararPrecios($productoId, $fecha1, $fecha2 = null)
    {
        $fecha2 = $fecha2 ?? now();
        
        $precioFecha1Entrada = self::obtenerPrecioEnFecha($productoId, 33, $fecha1);
        $precioFecha2Entrada = self::obtenerPrecioEnFecha($productoId, 33, $fecha2);
        
        $precioFecha1Salida = self::obtenerPrecioEnFecha($productoId, 34, $fecha1);
        $precioFecha2Salida = self::obtenerPrecioEnFecha($productoId, 34, $fecha2);
        
        return [
            'entrada' => [
                'fecha1' => $precioFecha1Entrada?->precio ?? 0,
                'fecha2' => $precioFecha2Entrada?->precio ?? 0,
                'diferencia' => ($precioFecha2Entrada?->precio ?? 0) - ($precioFecha1Entrada?->precio ?? 0),
                'porcentaje_cambio' => $precioFecha1Entrada ? 
                    (($precioFecha2Entrada?->precio - $precioFecha1Entrada->precio) / $precioFecha1Entrada->precio) * 100 : 
                    0
            ],
            'salida' => [
                'fecha1' => $precioFecha1Salida?->precio ?? 0,
                'fecha2' => $precioFecha2Salida?->precio ?? 0,
                'diferencia' => ($precioFecha2Salida?->precio ?? 0) - ($precioFecha1Salida?->precio ?? 0),
                'porcentaje_cambio' => $precioFecha1Salida ? 
                    (($precioFecha2Salida?->precio - $precioFecha1Salida->precio) / $precioFecha1Salida->precio) * 100 : 
                    0
            ]
        ];
    }
}