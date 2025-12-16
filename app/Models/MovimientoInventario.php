<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    
    protected $fillable = [
        'fecha',
        'id_tipo_movimiento',
        'referencia',
        'id_producto',
        'id_lote',
        'cantidad_entrada',
        'cantidad_salida',
        'costo_unitario',
        'costo_total',
        'id_ubicacion_origen',
        'id_ubicacion_destino',
        'id_usuario'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'id_tipo_movimiento' => 'integer',
        'id_producto' => 'integer',
        'id_lote' => 'integer',
        'cantidad_entrada' => 'integer',
        'cantidad_salida' => 'integer',
        'costo_unitario' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'id_ubicacion_origen' => 'integer',
        'id_ubicacion_destino' => 'integer',
        'id_usuario' => 'integer'
    ];

    // Desactivar updated_at ya que solo usamos created_at
    const UPDATED_AT = null;

    // Relaciones
    public function tipoMovimiento()
    {
        return $this->belongsTo(Subdominio::class, 'id_tipo_movimiento');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function ubicacionOrigen()
    {
        return $this->belongsTo(PoliticoUbicacion::class, 'id_ubicacion_origen');
    }

    public function ubicacionDestino()
    {
        return $this->belongsTo(PoliticoUbicacion::class, 'id_ubicacion_destino');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    // Scopes para filtros comunes
    public function scopeCompras($query)
    {
        return $query->where('id_tipo_movimiento', 30); // ID 30 = COMPRA
    }

    public function scopeSalidas($query)
    {
        return $query->where('id_tipo_movimiento', 31); // ID 31 = SALIDA
    }

    public function scopeAjustes($query)
    {
        return $query->where('id_tipo_movimiento', 32); // ID 32 = AJUSTE
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('id_producto', $productoId);
    }

    public function scopePorLote($query, $loteId)
    {
        return $query->where('id_lote', $loteId);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('id_usuario', $usuarioId);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('fecha', $fechaInicio);
    }

    public function scopePorMes($query, $mes, $anio)
    {
        return $query->whereMonth('fecha', $mes)
                    ->whereYear('fecha', $anio);
    }

    public function scopePorAnio($query, $anio)
    {
        return $query->whereYear('fecha', $anio);
    }

    public function scopeConEntradas($query)
    {
        return $query->where('cantidad_entrada', '>', 0);
    }

    public function scopeConSalidas($query)
    {
        return $query->where('cantidad_salida', '>', 0);
    }

    public function scopePorUbicacionOrigen($query, $ubicacionId)
    {
        return $query->where('id_ubicacion_origen', $ubicacionId);
    }

    public function scopePorUbicacionDestino($query, $ubicacionId)
    {
        return $query->where('id_ubicacion_destino', $ubicacionId);
    }

    public function scopeConRelaciones($query)
    {
        return $query->with([
            'tipoMovimiento',
            'producto',
            'lote',
            'ubicacionOrigen',
            'ubicacionDestino',
            'usuario'
        ]);
    }

    public function scopeOrdenadoPorFecha($query, $orden = 'desc')
    {
        return $query->orderBy('fecha', $orden);
    }

    // Métodos helper para verificar tipos
    public function esCompra()
    {
        return $this->id_tipo_movimiento === 30; // ID 30 = COMPRA
    }

    public function esSalida()
    {
        return $this->id_tipo_movimiento === 31; // ID 31 = SALIDA
    }

    public function esAjuste()
    {
        return $this->id_tipo_movimiento === 32; // ID 32 = AJUSTE
    }

    public function esEntrada()
    {
        return $this->cantidad_entrada > 0;
    }

    public function tieneUbicacionOrigen()
    {
        return !is_null($this->id_ubicacion_origen);
    }

    public function tieneUbicacionDestino()
    {
        return !is_null($this->id_ubicacion_destino);
    }

    // Accessors para obtener información
    public function getTipoMovimientoTextoAttribute()
    {
        return $this->tipoMovimiento?->descripcion ?? 'N/A';
    }

    public function getProductoNombreAttribute()
    {
        return $this->producto?->nombre ?? 'N/A';
    }

    public function getLoteNumeroAttribute()
    {
        return $this->lote?->numero_lote ?? 'N/A';
    }

    public function getUbicacionOrigenTextoAttribute()
    {
        return $this->ubicacionOrigen?->descripcion ?? 'N/A';
    }

    public function getUbicacionDestinoTextoAttribute()
    {
        return $this->ubicacionDestino?->descripcion ?? 'N/A';
    }

    public function getUsuarioNombreAttribute()
    {
        return $this->usuario?->name ?? 'N/A';
    }

    public function getCantidadMovimientoAttribute()
    {
        return $this->cantidad_entrada > 0 ? $this->cantidad_entrada : $this->cantidad_salida;
    }

    public function getDireccionMovimientoAttribute()
    {
        if ($this->cantidad_entrada > 0) {
            return 'ENTRADA';
        } elseif ($this->cantidad_salida > 0) {
            return 'SALIDA';
        }
        return 'SIN MOVIMIENTO';
    }

    // Accessor para mostrar información completa del movimiento
    public function getInfoCompletaAttribute()
    {
        return [
            'id' => $this->id,
            'fecha' => $this->fecha->format('d/m/Y H:i:s'),
            'tipo_movimiento' => $this->tipoMovimientoTexto,
            'direccion' => $this->direccionMovimiento,
            'referencia' => $this->referencia,
            'producto' => $this->productoNombre,
            'lote' => $this->loteNumero,
            'cantidad_entrada' => $this->cantidad_entrada,
            'cantidad_salida' => $this->cantidad_salida,
            'cantidad_movimiento' => $this->cantidadMovimiento,
            'costo_unitario' => $this->costo_unitario,
            'costo_total' => $this->costo_total,
            'ubicacion_origen' => $this->ubicacionOrigenTexto,
            'ubicacion_destino' => $this->ubicacionDestinoTexto,
            'usuario' => $this->usuarioNombre
        ];
    }

    // Métodos de cálculo
    public function calcularCostoTotal()
    {
        $cantidad = $this->cantidad_entrada > 0 ? $this->cantidad_entrada : $this->cantidad_salida;
        $this->costo_total = $cantidad * $this->costo_unitario;
        return $this;
    }

    public function obtenerSaldoCantidad()
    {
        return $this->cantidad_entrada - $this->cantidad_salida;
    }

    // Validaciones
    public function validarCantidades()
    {
        // Solo puede haber entrada O salida, no ambas
        if ($this->cantidad_entrada > 0 && $this->cantidad_salida > 0) {
            return false;
        }
        
        // Debe haber al menos una cantidad
        if ($this->cantidad_entrada == 0 && $this->cantidad_salida == 0) {
            return false;
        }
        
        return true;
    }

    public function validarUbicaciones()
    {
        // Las salidas deben tener ubicación origen
        if ($this->esSalida() && !$this->tieneUbicacionOrigen()) {
            return false;
        }
        
        // Las compras deben tener ubicación destino
        if ($this->esCompra() && !$this->tieneUbicacionDestino()) {
            return false;
        }
        
        return true;
    }

    public function esValido()
    {
        return $this->validarCantidades() && $this->validarUbicaciones();
    }

    // Event observers
    protected static function boot()
    {
        parent::boot();

        // Antes de crear, validar y calcular
        static::creating(function ($movimiento) {
            // Si no tiene fecha, usar ahora
            if (!$movimiento->fecha) {
                $movimiento->fecha = now();
            }
            
            // Calcular costo total
            $movimiento->calcularCostoTotal();
        });

        // Antes de actualizar, recalcular
        static::updating(function ($movimiento) {
            $movimiento->calcularCostoTotal();
        });
    }

    // Métodos estáticos útiles
    public static function registrarCompra($compraId, $loteId, $cantidad, $costoUnitario, $ubicacionDestinoId, $usuarioId)
    {
        $lote = Lote::find($loteId);
        
        return self::create([
            'fecha' => now(),
            'id_tipo_movimiento' => 30, // COMPRA
            'referencia' => 'COMPRA-' . $compraId,
            'id_producto' => $lote->id_producto,
            'id_lote' => $loteId,
            'cantidad_entrada' => $cantidad,
            'cantidad_salida' => 0,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $cantidad * $costoUnitario,
            'id_ubicacion_origen' => null,
            'id_ubicacion_destino' => $ubicacionDestinoId,
            'id_usuario' => $usuarioId
        ]);
    }

    public static function registrarSalida($referencia, $loteId, $cantidad, $costoUnitario, $ubicacionOrigenId, $ubicacionDestinoId, $usuarioId)
    {
        $lote = Lote::find($loteId);
        
        return self::create([
            'fecha' => now(),
            'id_tipo_movimiento' => 31, // SALIDA
            'referencia' => $referencia,
            'id_producto' => $lote->id_producto,
            'id_lote' => $loteId,
            'cantidad_entrada' => 0,
            'cantidad_salida' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $cantidad * $costoUnitario,
            'id_ubicacion_origen' => $ubicacionOrigenId,
            'id_ubicacion_destino' => $ubicacionDestinoId,
            'id_usuario' => $usuarioId
        ]);
    }

    public static function registrarAjuste($referencia, $loteId, $cantidadEntrada, $cantidadSalida, $costoUnitario, $ubicacionId, $usuarioId, $esDestino = true)
    {
        $lote = Lote::find($loteId);
        
        return self::create([
            'fecha' => now(),
            'id_tipo_movimiento' => 32, // AJUSTE
            'referencia' => $referencia,
            'id_producto' => $lote->id_producto,
            'id_lote' => $loteId,
            'cantidad_entrada' => $cantidadEntrada,
            'cantidad_salida' => $cantidadSalida,
            'costo_unitario' => $costoUnitario,
            'costo_total' => ($cantidadEntrada > 0 ? $cantidadEntrada : $cantidadSalida) * $costoUnitario,
            'id_ubicacion_origen' => $esDestino ? null : $ubicacionId,
            'id_ubicacion_destino' => $esDestino ? $ubicacionId : null,
            'id_usuario' => $usuarioId
        ]);
    }

    public static function obtenerKardex($productoId, $loteId = null, $fechaInicio = null, $fechaFin = null)
    {
        $query = self::where('id_producto', $productoId)
                    ->with(['tipoMovimiento', 'lote', 'usuario'])
                    ->orderBy('fecha', 'asc');
        
        if ($loteId) {
            $query->where('id_lote', $loteId);
        }
        
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        
        $movimientos = $query->get();
        
        // Calcular saldos acumulados
        $saldo = 0;
        $kardex = [];
        
        foreach ($movimientos as $movimiento) {
            $saldo += $movimiento->cantidad_entrada - $movimiento->cantidad_salida;
            
            $kardex[] = [
                'fecha' => $movimiento->fecha,
                'tipo' => $movimiento->tipoMovimientoTexto,
                'referencia' => $movimiento->referencia,
                'lote' => $movimiento->loteNumero,
                'entrada' => $movimiento->cantidad_entrada,
                'salida' => $movimiento->cantidad_salida,
                'saldo' => $saldo,
                'costo_unitario' => $movimiento->costo_unitario,
                'costo_total' => $movimiento->costo_total,
                'usuario' => $movimiento->usuarioNombre
            ];
        }
        
        return $kardex;
    }

    public static function obtenerMovimientosDelDia($fecha = null)
    {
        $fecha = $fecha ?? now();
        
        return self::whereDate('fecha', $fecha)
                  ->conRelaciones()
                  ->ordenadoPorFecha('desc')
                  ->get();
    }

    public static function obtenerMovimientosPorPeriodo($fechaInicio, $fechaFin, $tipoMovimiento = null)
    {
        $query = self::whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->conRelaciones()
                    ->ordenadoPorFecha('desc');
        
        if ($tipoMovimiento) {
            $query->where('id_tipo_movimiento', $tipoMovimiento);
        }
        
        return $query->get();
    }

    public static function calcularTotalMovimientos($fechaInicio, $fechaFin, $tipoMovimiento = null)
    {
        $query = self::whereBetween('fecha', [$fechaInicio, $fechaFin]);
        
        if ($tipoMovimiento) {
            $query->where('id_tipo_movimiento', $tipoMovimiento);
        }
        
        return $query->sum('costo_total');
    }

    public static function obtenerProductosMasMovidos($limite = 10, $fechaInicio = null, $fechaFin = null)
    {
        $query = self::selectRaw('id_producto, SUM(cantidad_entrada + cantidad_salida) as total_movimientos')
                    ->groupBy('id_producto')
                    ->orderByDesc('total_movimientos')
                    ->limit($limite);
        
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        
        return $query->with('producto')->get();
    }

    public static function contarPorTipo($fechaInicio = null, $fechaFin = null)
    {
        $query = self::query();
        
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        
        return [
            'compras' => (clone $query)->where('id_tipo_movimiento', 30)->count(),
            'salidas' => (clone $query)->where('id_tipo_movimiento', 31)->count(),
            'ajustes' => (clone $query)->where('id_tipo_movimiento', 32)->count()
        ];
    }

    public static function obtenerMovimientosPorUbicacion($ubicacionId, $esOrigen = true, $fechaInicio = null, $fechaFin = null)
    {
        $campo = $esOrigen ? 'id_ubicacion_origen' : 'id_ubicacion_destino';
        
        $query = self::where($campo, $ubicacionId)
                    ->conRelaciones()
                    ->ordenadoPorFecha('desc');
        
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        
        return $query->get();
    }

    public static function obtenerResumenDiario($fecha = null)
    {
        $fecha = $fecha ?? now();
        
        $movimientos = self::whereDate('fecha', $fecha)->get();
        
        return [
            'total_entradas' => $movimientos->sum('cantidad_entrada'),
            'total_salidas' => $movimientos->sum('cantidad_salida'),
            'costo_total_entradas' => $movimientos->where('cantidad_entrada', '>', 0)->sum('costo_total'),
            'costo_total_salidas' => $movimientos->where('cantidad_salida', '>', 0)->sum('costo_total'),
            'cantidad_movimientos' => $movimientos->count(),
            'compras' => $movimientos->where('id_tipo_movimiento', 30)->count(),
            'salidas' => $movimientos->where('id_tipo_movimiento', 31)->count(),
            'ajustes' => $movimientos->where('id_tipo_movimiento', 32)->count()
        ];
    }
}