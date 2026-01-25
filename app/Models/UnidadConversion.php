<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadConversion extends Model
{
    protected $table = 'unidades_conversion';

    protected $fillable = [
        'id_producto',
        'unidad_origen',
        'unidad_destino',
        'factor'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
