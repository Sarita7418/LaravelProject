<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logo extends Model
{
    protected $table = 'logos';

    // Campos permitidos para create()/update()
    protected $fillable = [
        'id_entidad',
        'tipo_entidad',
        'logo',  // Este campo almacenará la imagen en formato binario
    ];

    // Relación polimórfica: el logo puede pertenecer a una empresa o sucursal
    public function entidad()
    {
        return $this->morphTo();
    }
}
