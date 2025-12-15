<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobanteDetalle extends Model
{
    protected $fillable = ['comprobante_id','cuenta_id','glosa_detalle','debe','haber','orden'];

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }

    public function cuenta()
    {
        return $this->belongsTo(PlanCuenta::class, 'cuenta_id');
    }
}
