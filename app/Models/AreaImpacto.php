<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class area_impacto extends Model
{
    use HasFactory;

    protected $table = 'areas_impacto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_protocolo', // Incluye esto solo si la FK está en esta tabla
    ];

    /**
     * Relación: Un área de impacto pertenece a un protocolo.
     */
    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class, 'id_protocolo');
    }
}
