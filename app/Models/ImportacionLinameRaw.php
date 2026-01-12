<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportacionLinameRaw extends Model
{
    use HasFactory;

    // Aseguramos que apunte a la tabla correcta (plural)
    protected $table = 'importacion_liname_raws';

    // ¡Esta es la lista de permisos que faltaba!
    protected $fillable = [
        'codigo_completo',
        'grupo_co',
        'subgrupo_di',
        'correlativo_go',
        'medicamento_nombre',
        'forma',
        'concentracion',
        'codigo_atq',
        'uso_restringido',
    ];
}