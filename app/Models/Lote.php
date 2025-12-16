<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Lote extends Model
{
    protected $table = 'lotes';
    
    protected $fillable = [
        'id_compra',
        'id_producto',
        'numero_lote',
        'cantidad_inicial',
        'fecha_ingreso',
        'fecha_vencimiento',
        'id_estado_lote'
    ];

    protected $casts = [
        'id_compra' => 'integer',
        'id_producto' => 'integer',
        'cantidad_inicial' => 'integer',
        'fecha_ingreso' => 'date',
        'fecha_vencimiento' => 'date',
        'id_estado_lote' => 'integer'
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

    public function estadoLote()
    {
        return $this->belongsTo(Subdominio::class, 'id_estado_lote');
    }

    public function stockActual()
    {
        return $this->hasMany(StockActual::class, 'id_lote');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_lote');
    }

    // Scopes para filtros comunes
    public function scopeActivos($query)
    {
        return $query->where('id_estado_lote', 27); // ID 27 = ACTIVO
    }

    public function scopeAgotados($query)
    {
        return $query->where('id_estado_lote', 28); // ID 28 = AGOTADO
    }

    public function scopeDanados($query)
    {
        return $query->where('id_estado_lote', 29); // ID 29 = DAÑADO
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('id_producto', $productoId);
    }

    public function scopePorCompra($query, $compraId)
    {
        return $query->where('id_compra', $compraId);
    }

    public function scopeConVencimiento($query)
    {
        return $query->whereNotNull('fecha_vencimiento');
    }

    public function scopeSinVencimiento($query)
    {
        return $query->whereNull('fecha_vencimiento');
    }

    public function scopeProximosAVencer($query, $dias = 30)
    {
        $fechaLimite = now()->addDays($dias);
        return $query->whereNotNull('fecha_vencimiento')
                    ->where('fecha_vencimiento', '<=', $fechaLimite)
                    ->where('fecha_vencimiento', '>=', now())
                    ->activos();
    }

    public function scopeVencidos($query)
    {
        return $query->whereNotNull('fecha_vencimiento')
                    ->where('fecha_vencimiento', '<', now())
                    ->activos();
    }

    public function scopePorFechaIngreso($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('fecha_ingreso', $fechaInicio);
    }

    public function scopeBuscarPorNumero($query, $numeroLote)
    {
        return $query->where('numero_lote', 'LIKE', '%' . $numeroLote . '%');
    }

    public function scopeConRelaciones($query)
    {
        return $query->with(['compra', 'producto', 'estadoLote']);
    }

    public function scopeOrdenadoPorVencimiento($query, $orden = 'asc')
    {
        return $query->orderBy('fecha_vencimiento', $orden);
    }

    public function scopeOrdenadoPorIngreso($query, $orden = 'desc')
    {
        return $query->orderBy('fecha_ingreso', $orden);
    }

    // Métodos helper para verificar estados
    public function estaActivo()
    {
        return $this->id_estado_lote === 27; // ID 27 = ACTIVO
    }

    public function estaAgotado()
    {
        return $this->id_estado_lote === 28; // ID 28 = AGOTADO
    }

    public function estaDanado()
    {
        return $this->id_estado_lote === 29; // ID 29 = DAÑADO
    }

    public function tieneVencimiento()
    {
        return !is_null($this->fecha_vencimiento);
    }

    public function estaVencido()
    {
        if (!$this->tieneVencimiento()) {
            return false;
        }
        return $this->fecha_vencimiento->isPast();
    }

    public function estaPorVencer($dias = 30)
    {
        if (!$this->tieneVencimiento() || $this->estaVencido()) {
            return false;
        }
        $fechaLimite = now()->addDays($dias);
        return $this->fecha_vencimiento->lte($fechaLimite);
    }

    public function diasParaVencer()
    {
        if (!$this->tieneVencimiento()) {
            return null;
        }
        
        if ($this->estaVencido()) {
            return 0;
        }
        
        return now()->diffInDays($this->fecha_vencimiento);
    }

    // Accessors para obtener información
    public function getEstadoTextoAttribute()
    {
        return $this->estadoLote?->descripcion ?? 'N/A';
    }

    public function getProductoNombreAttribute()
    {
        return $this->producto?->nombre ?? 'N/A';
    }

    public function getCompraNroDocumentoAttribute()
    {
        return $this->compra?->nro_documento ?? 'N/A';
    }

    public function getCantidadDisponibleAttribute()
    {
        return $this->stockActual()->sum('cantidad');
    }

    public function getCantidadUtilizadaAttribute()
    {
        return $this->cantidad_inicial - $this->cantidadDisponible;
    }

    public function getPorcentajeUtilizadoAttribute()
    {
        if ($this->cantidad_inicial == 0) {
            return 0;
        }
        return ($this->cantidadUtilizada / $this->cantidad_inicial) * 100;
    }

    public function getEdadDiasAttribute()
    {
        return $this->fecha_ingreso->diffInDays(now());
    }

    // Accessor para alerta de vencimiento
    public function getAlertaVencimientoAttribute()
    {
        if (!$this->tieneVencimiento()) {
            return 'sin_vencimiento';
        }

        if ($this->estaVencido()) {
            return 'vencido';
        }

        $diasRestantes = $this->diasParaVencer();

        if ($diasRestantes <= 7) {
            return 'critico'; // Menos de 1 semana
        } elseif ($diasRestantes <= 30) {
            return 'advertencia'; // Menos de 1 mes
        } else {
            return 'normal';
        }
    }

    // Accessor para mostrar información completa del lote
    public function getInfoCompletaAttribute()
    {
        return [
            'id' => $this->id,
            'numero_lote' => $this->numero_lote,
            'producto' => $this->productoNombre,
            'compra' => $this->compraNroDocumento,
            'cantidad_inicial' => $this->cantidad_inicial,
            'cantidad_disponible' => $this->cantidadDisponible,
            'cantidad_utilizada' => $this->cantidadUtilizada,
            'porcentaje_utilizado' => round($this->porcentajeUtilizado, 2) . '%',
            'fecha_ingreso' => $this->fecha_ingreso->format('d/m/Y'),
            'fecha_vencimiento' => $this->tieneVencimiento() ? $this->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento',
            'dias_para_vencer' => $this->diasParaVencer(),
            'alerta_vencimiento' => $this->alertaVencimiento,
            'estado' => $this->estadoTexto,
            'edad_dias' => $this->edadDias
        ];
    }

    // Métodos de gestión de estado
    public function marcarComoAgotado()
    {
        if ($this->cantidadDisponible == 0 && $this->estaActivo()) {
            $this->update(['id_estado_lote' => 28]); // ID 28 = AGOTADO
            return true;
        }
        return false;
    }

    public function marcarComoDanado($motivo = null)
    {
        if (!$this->estaDanado()) {
            $this->update(['id_estado_lote' => 29]); // ID 29 = DAÑADO
            
            // Aquí puedes registrar el motivo en una tabla de auditoría
            // o en observaciones
            
            return true;
        }
        return false;
    }

    public function reactivar()
    {
        if ($this->cantidadDisponible > 0 && !$this->estaActivo()) {
            $this->update(['id_estado_lote' => 27]); // ID 27 = ACTIVO
            return true;
        }
        return false;
    }

    // Validación automática de estado basado en stock
    public function validarEstado()
    {
        $cantidadActual = $this->cantidadDisponible;
        
        if ($cantidadActual == 0 && $this->estaActivo()) {
            $this->marcarComoAgotado();
        } elseif ($cantidadActual > 0 && $this->estaAgotado()) {
            $this->reactivar();
        }
        
        return $this;
    }

    // Event observers
    protected static function boot()
    {
        parent::boot();

        // Antes de crear, validar datos
        static::creating(function ($lote) {
            // Si no tiene fecha de ingreso, usar hoy
            if (!$lote->fecha_ingreso) {
                $lote->fecha_ingreso = now();
            }
            
            // Si no tiene estado, marcar como ACTIVO
            if (!$lote->id_estado_lote) {
                $lote->id_estado_lote = 27; // ID 27 = ACTIVO
            }
        });

        // Después de actualizar, validar estado automáticamente
        static::updated(function ($lote) {
            $lote->validarEstado();
        });
    }

    // Métodos estáticos útiles
    public static function crearLote($compraId, $productoId, $numeroLote, $cantidadInicial, $fechaVencimiento = null)
    {
        return self::create([
            'id_compra' => $compraId,
            'id_producto' => $productoId,
            'numero_lote' => $numeroLote,
            'cantidad_inicial' => $cantidadInicial,
            'fecha_ingreso' => now(),
            'fecha_vencimiento' => $fechaVencimiento,
            'id_estado_lote' => 27 // ID 27 = ACTIVO
        ]);
    }

    public static function obtenerLotesActivos($productoId = null)
    {
        $query = self::activos()->with('producto');
        
        if ($productoId) {
            $query->where('id_producto', $productoId);
        }
        
        return $query->orderBy('fecha_vencimiento', 'asc')
                    ->orderBy('fecha_ingreso', 'asc')
                    ->get();
    }

    public static function obtenerLotesProximosAVencer($dias = 30, $limite = 10)
    {
        return self::proximosAVencer($dias)
                  ->with(['producto', 'compra'])
                  ->orderBy('fecha_vencimiento', 'asc')
                  ->limit($limite)
                  ->get();
    }

    public static function obtenerLotesVencidos()
    {
        return self::vencidos()
                  ->with(['producto', 'compra'])
                  ->orderBy('fecha_vencimiento', 'asc')
                  ->get();
    }

    public static function contarPorEstado()
    {
        return [
            'activos' => self::where('id_estado_lote', 27)->count(),
            'agotados' => self::where('id_estado_lote', 28)->count(),
            'danados' => self::where('id_estado_lote', 29)->count()
        ];
    }

    public static function obtenerLotesPorProducto($productoId)
    {
        return self::where('id_producto', $productoId)
                  ->activos()
                  ->ordenadoPorVencimiento()
                  ->get();
    }

    public static function buscarLote($numeroLote, $productoId = null)
    {
        $query = self::where('numero_lote', $numeroLote);
        
        if ($productoId) {
            $query->where('id_producto', $productoId);
        }
        
        return $query->first();
    }

    public static function generarNumeroLote($compraId, $productoId)
    {
        $compra = Compra::find($compraId);
        $producto = Producto::find($productoId);
        
        if (!$compra || !$producto) {
            return null;
        }
        
        // Formato: PROD{ID}-COMP{ID}-FECHA
        $fecha = now()->format('Ymd');
        $secuencia = self::where('id_producto', $productoId)
                        ->whereDate('created_at', now())
                        ->count() + 1;
        
        return sprintf('L-%d-%d-%s-%03d', $productoId, $compraId, $fecha, $secuencia);
    }

    // Método FIFO - Obtener lotes en orden First In First Out
    public static function obtenerLotesFIFO($productoId, $cantidadRequerida)
    {
        $lotes = self::where('id_producto', $productoId)
                    ->activos()
                    ->orderBy('fecha_ingreso', 'asc')
                    ->get();
        
        $lotesSeleccionados = [];
        $cantidadAcumulada = 0;
        
        foreach ($lotes as $lote) {
            $disponible = $lote->cantidadDisponible;
            
            if ($disponible > 0) {
                $cantidad = min($disponible, $cantidadRequerida - $cantidadAcumulada);
                
                $lotesSeleccionados[] = [
                    'lote' => $lote,
                    'cantidad' => $cantidad
                ];
                
                $cantidadAcumulada += $cantidad;
                
                if ($cantidadAcumulada >= $cantidadRequerida) {
                    break;
                }
            }
        }
        
        return [
            'lotes' => $lotesSeleccionados,
            'cantidad_total' => $cantidadAcumulada,
            'completo' => $cantidadAcumulada >= $cantidadRequerida
        ];
    }

    // Método FEFO - Obtener lotes en orden First Expired First Out
    public static function obtenerLotesFEFO($productoId, $cantidadRequerida)
    {
        $lotes = self::where('id_producto', $productoId)
                    ->activos()
                    ->conVencimiento()
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->get();
        
        $lotesSeleccionados = [];
        $cantidadAcumulada = 0;
        
        foreach ($lotes as $lote) {
            $disponible = $lote->cantidadDisponible;
            
            if ($disponible > 0) {
                $cantidad = min($disponible, $cantidadRequerida - $cantidadAcumulada);
                
                $lotesSeleccionados[] = [
                    'lote' => $lote,
                    'cantidad' => $cantidad
                ];
                
                $cantidadAcumulada += $cantidad;
                
                if ($cantidadAcumulada >= $cantidadRequerida) {
                    break;
                }
            }
        }
        
        return [
            'lotes' => $lotesSeleccionados,
            'cantidad_total' => $cantidadAcumulada,
            'completo' => $cantidadAcumulada >= $cantidadRequerida
        ];
    }
}