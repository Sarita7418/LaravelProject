<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoPresupuestario extends Model
{
    protected $table = 'movimientos_presupuestarios';
    
    protected $fillable = [
        'comprometido',
        'devengado',
        'pago',
        'consumido'
    ];
}