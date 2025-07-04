<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    
    protected $fillable = [
        'nombre'
    ];
    
    // Relaciones
    public function protocolos()
    {
        return $this->hasMany(Protocolo::class, 'id_especialidad');
    }
}
