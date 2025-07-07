<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dominio extends Model
{
    protected $table = 'dominios';
    
    protected $fillable = [
        'descripcion'
    ];
    
    // Relaciones
    public function subdominios()
    {
        return $this->hasMany(Subdominio::class, 'id_dominio');
    }
}