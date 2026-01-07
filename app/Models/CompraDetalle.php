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
        'subtotal',
        'codigo_barras',
        'numero_lote',
        'fecha_vencimiento'
    ];

    protected $casts = [
        'id_compra' => 'integer',
        'id_producto' => 'integer',
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'descuento_pct' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'fecha_vencimiento' => 'date'
    ];

    public $timestamps = true;

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function getProductoNombreAttribute()
    {
        return $this->producto?->nombre ?? 'N/A';
    }

    public function getTotalSinDescuentoAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }

    public function calcularSubtotal()
    {
        $totalSinDescuento = $this->cantidad * $this->precio_unitario;
        
        if ($this->descuento_pct > 0) {
            $this->descuento_monto = ($totalSinDescuento * $this->descuento_pct) / 100;
        }
        
        $this->subtotal = $totalSinDescuento - $this->descuento_monto;
        
        return $this;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($detalle) {
            $detalle->calcularSubtotal();
        });

        static::updating(function ($detalle) {
            $detalle->calcularSubtotal();
        });
    }
}