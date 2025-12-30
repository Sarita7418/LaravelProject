<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';
    protected $fillable = [
        'id_empresa',
        'id_sucursal',
        'id_proveedor',
        'id_usuario',
        'fecha_compra',
        'observacion',
        'subtotal',
        'descuento_total',
        'total_compra',
        'id_estado_compra',
        'nro_documento',
        'fecha_limite_emision',
        'id_sucursal_proveedor'
    ];

    protected $casts = [
        'id_empresa' => 'integer',
        'id_sucursal' => 'integer',
        'id_proveedor' => 'integer',
        'id_usuario' => 'integer',
        'fecha_compra' => 'date',
        'fecha_limite_emision' => 'date',
        'subtotal' => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'total_compra' => 'decimal:2',
        'id_estado_compra' => 'integer'
    ];

    public $timestamps = true;
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }
    public function proveedor()
    {
        return $this->belongsTo(Empresa::class, 'id_proveedor');
    }
    public function sucursalProveedor()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal_proveedor');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function estadoCompra()
    {
        return $this->belongsTo(Subdominio::class, 'id_estado_compra');
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class, 'id_compra');
    }

    public function scopeBorradores($query)
    {
        return $query->where('id_estado_compra', 24); 
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('id_estado_compra', 25); 
    }

    public function scopeAnuladas($query)
    {
        return $query->where('id_estado_compra', 26); 
    }

    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('id_empresa', $empresaId);
    }

    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->where('id_sucursal', $sucursalId);
    }

    public function scopePorProveedor($query, $proveedorId)
    {
        return $query->where('id_proveedor', $proveedorId);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_compra', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('fecha_compra', $fechaInicio);
    }

    public function scopePorMes($query, $mes, $anio)
    {
        return $query->whereMonth('fecha_compra', $mes)
                    ->whereYear('fecha_compra', $anio);
    }

    public function scopePorAnio($query, $anio)
    {
        return $query->whereYear('fecha_compra', $anio);
    }

    public function scopeConDetalle($query)
    {
        return $query->with('detalles');
    }

    public function scopeConRelaciones($query)
    {
        return $query->with([
            'empresa',
            'sucursal',
            'proveedor',
            'usuario',
            'estadoCompra',
            'detalles'
        ]);
    }

    public function esBorrador()
    {
        return $this->id_estado_compra === 24; 
    }

    public function estaConfirmada()
    {
        return $this->id_estado_compra === 25; 
    }

    public function estaAnulada()
    {
        return $this->id_estado_compra === 26; 
    }

    public function puedeEditarse()
    {
        return $this->esBorrador();
    }

    public function puedeAnularse()
    {
        return $this->estaConfirmada();
    }

    public function puedeConfirmarse()
    {
        return $this->esBorrador() && $this->detalles()->count() > 0;
    }

    public function getEstadoTextoAttribute()
    {
        return $this->estadoCompra?->descripcion ?? 'N/A';
    }

    public function getProveedorNombreCompletoAttribute()
    {
        if (!$this->proveedor) {
            return 'N/A';
        }
        return trim($this->proveedor->nombres . ' ' . 
                   $this->proveedor->apellido_paterno . ' ' . 
                   ($this->proveedor->apellido_materno ?? ''));
    }

    public function getEmpresaNombreAttribute()
    {
        return $this->empresa?->razon_social ?? 'N/A';
    }

    public function getSucursalNombreAttribute()
    {
        return $this->sucursal?->nombre ?? 'N/A';
    }

    public function getUsuarioNombreAttribute()
    {
        return $this->usuario?->name ?? 'N/A';
    }

    public function getInfoCompletaAttribute()
    {
        return [
            'id' => $this->id,
            'empresa' => $this->empresaNombre,
            'sucursal' => $this->sucursalNombre,
            'proveedor' => $this->proveedorNombreCompleto,
            'usuario' => $this->usuarioNombre,
            'fecha_compra' => $this->fecha_compra->format('d/m/Y'),
            'nro_documento' => $this->nro_documento,
            'subtotal' => $this->subtotal,
            'descuento_total' => $this->descuento_total,
            'total_compra' => $this->total_compra,
            'estado' => $this->estadoTexto,
            'puede_editarse' => $this->puedeEditarse(),
            'puede_confirmarse' => $this->puedeConfirmarse(),
            'puede_anularse' => $this->puedeAnularse(),
            'cantidad_items' => $this->detalles()->count()
        ];
    }

    public function calcularTotales()
    {
        $detalles = $this->detalles;
        
        $subtotal = $detalles->sum('subtotal');
        $descuentoTotal = $detalles->sum('descuento_monto');
        $totalCompra = $subtotal - $descuentoTotal;

        $this->update([
            'subtotal' => $subtotal,
            'descuento_total' => $descuentoTotal,
            'total_compra' => $totalCompra
        ]);

        return $this;
    }

    public function obtenerTotalItems()
    {
        return $this->detalles()->sum('cantidad');
    }

    public function confirmar()
    {
        if (!$this->puedeConfirmarse()) {
            throw new \Exception('No se puede confirmar esta compra');
        }

        $this->update(['id_estado_compra' => 25]); 
        
        return $this;
    }

    public function anular($motivo = null)
    {
        if (!$this->puedeAnularse()) {
            throw new \Exception('No se puede anular esta compra');
        }

        $observacionAnulacion = "ANULADO";
        if ($motivo) {
            $observacionAnulacion .= " - Motivo: " . $motivo;
        }

        $this->update([
            'id_estado_compra' => 26, 
            'observacion' => $this->observacion . "\n" . $observacionAnulacion
        ]);
        
        return $this;
    }

    public static function obtenerComprasPorEstado($estadoId)
    {
        return self::where('id_estado_compra', $estadoId)
                   ->with(['empresa', 'sucursal', 'proveedor', 'estadoCompra'])
                   ->orderBy('fecha_compra', 'desc')
                   ->get();
    }

    public static function obtenerComprasDelMes($mes = null, $anio = null)
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        return self::porMes($mes, $anio)
                   ->confirmadas()
                   ->with('proveedor')
                   ->orderBy('fecha_compra', 'desc')
                   ->get();
    }

    public static function calcularTotalComprasMes($mes = null, $anio = null)
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        return self::porMes($mes, $anio)
                   ->confirmadas()
                   ->sum('total_compra');
    }

    public static function obtenerTopProveedores($limite = 10, $fechaInicio = null, $fechaFin = null)
    {
        $query = self::confirmadas()
                    ->selectRaw('id_proveedor, SUM(total_compra) as total_comprado, COUNT(*) as cantidad_compras')
                    ->groupBy('id_proveedor')
                    ->orderByDesc('total_comprado')
                    ->limit($limite);

        if ($fechaInicio && $fechaFin) {
            $query->porFecha($fechaInicio, $fechaFin);
        }

        return $query->with('proveedor')->get();
    }

    public static function contarPorEstado()
    {
        return [
            'borradores' => self::where('id_estado_compra', 24)->count(),
            'confirmadas' => self::where('id_estado_compra', 25)->count(),
            'anuladas' => self::where('id_estado_compra', 26)->count()
        ];
    }

    public static function generarNumeroDocumento($empresaId, $sucursalId)
    {
        $ultimaCompra = self::where('id_empresa', $empresaId)
                           ->where('id_sucursal', $sucursalId)
                           ->orderBy('id', 'desc')
                           ->first();

        $numeroSecuencial = $ultimaCompra ? ($ultimaCompra->id + 1) : 1;
        
        return sprintf('COMP-%d-%d-%06d', $empresaId, $sucursalId, $numeroSecuencial);
    }
}