<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoliticoUbicacion extends Model
{
    protected $table = 'politicos_ubicacion';
    protected $fillable = ['id_padre', 'tipo', 'descripcion'];

    public function padre()
    {
        return $this->belongsTo(self::class, 'id_padre');
    }

    public function hijos()
    {
        return $this->hasMany(self::class, 'id_padre')->orderBy('descripcion');
    }
}