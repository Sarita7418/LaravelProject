<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class area_impacto_protocolo extends Model
{
    protected $table = 'area_impactos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function protocolos(): BelongsToMany
    {
        return $this->belongsToMany(Protocolo::class, 'area_impacto_protocolo', 'id_area_impactos', 'id_protocolo')->withTimestamps();
    }
}
