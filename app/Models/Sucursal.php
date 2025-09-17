<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{

    protected $table = 'sucursales';

    // Campos permitidos para create()/update()
    protected $fillable = [
        'id_empresa',
        'nombre',
        'codigo_sucursal',
        'direccion',
        'telefono',
        'email',
        'estado',
    ];

    // Casts (estado boolean; id_empresa entero si lo usas así en BDD)
    protected $casts = [
        'estado'     => 'boolean',
        'id_empresa' => 'integer',
    ];

    public $timestamps = true;

    // Relación (opcional, útil si luego haces with('empresa'))
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }
}
