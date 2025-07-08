<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subdominio extends Model
{
    protected $table = 'subdominios';
    
    protected $fillable = [
        'id_dominio',
        'descripcion'
    ];
    
    // Relaciones
    public function dominio()
    {
        return $this->belongsTo(Dominio::class, 'id_dominio');
    }
    
    public function protocolosEstado()
    {
        return $this->hasMany(Protocolo::class, 'id_estado');
    }
    
    public function protocolosAreaImpacto()
    {
        return $this->hasMany(Protocolo::class, 'id_area_impacto');
    }
}