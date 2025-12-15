<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
     protected $fillable = [
        'numero','tipo','anio','fecha','glosa_general',
        'total_debe','total_haber','monto_letras',
        'elaborado_por','aprobado_por','verificado_por','user_id'
    ];

    public function detalles()
    {
        return $this->hasMany(ComprobanteDetalle::class);
    }
}
