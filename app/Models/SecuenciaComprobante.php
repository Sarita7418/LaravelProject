<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecuenciaComprobante extends Model
{
        protected $table = 'secuencias_comprobantes';
        protected $fillable = ['tipo','anio','ultimo'];
}
