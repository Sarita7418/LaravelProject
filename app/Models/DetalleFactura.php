<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    use HasFactory;
    
    protected $table = 'detalle_facturas'; // Especificamos la tabla por si acaso

    protected $fillable = [
        'factura_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function producto()
    {
        // Asumiendo que tu compañera tiene un modelo llamado 'Producto'
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}