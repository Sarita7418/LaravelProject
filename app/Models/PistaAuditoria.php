<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PistaAuditoria extends Model
{
    protected $table = 'pista_auditoria';

    protected $fillable = [
        'fecha',
        'usuario_bd',
        'accion',
        'nombre_host',
        'ip_host',
        'pk',
        'nombre_tabla',
        //'codigo_usuario',
        //'codigo_regional_usuario',
        'registros1',
        'registros2',
    ];

    public $timestamps = true;
}
