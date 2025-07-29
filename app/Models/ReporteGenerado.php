<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteGenerado extends Model
{
        protected $fillable = [
        'nombre_reporte',
        'formato',
        'rango_desde',
        'rango_hasta',
        'user_id',
        'fecha_generacion',
        'path_archivo',
    ];

}
