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

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function tipoPrecio()
    {
        return $this->belongsTo(Subdominio::class, 'id_tipo_precio');
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

    protected static function boot()
    {
        parent::boot();

        static::created(function ($precio) {
            if ($precio->estaVigente()) {
                $precio->actualizarPrecioCache();
            }
        });

        static::updated(function ($precio) {
            if ($precio->estaVigente()) {
                $precio->actualizarPrecioCache();
            }
        });
    }

    private function actualizarPrecioCache()
    {
        $producto = $this->producto;
        
        if (!$producto) {
            return;
        }
        
        if ($this->id_tipo_precio === 33) {
            $producto->update(['precio_entrada' => $this->precio]);
        } elseif ($this->id_tipo_precio === 34) {
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