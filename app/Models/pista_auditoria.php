<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pista_auditoria extends Model
{
    public $timestamps = false;
    protected $table = 'pista_auditoria';

    protected $fillable = [
        'fecha_hora',
        'ip_maquina',
        'nombre_maquina',
        'tabla_afectada',
        'accion_realizada',
        'info_antes',
        'info_despues',
        'id_usuario',
    ];
}
