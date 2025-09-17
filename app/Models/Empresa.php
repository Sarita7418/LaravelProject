<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    // habilita asignación masiva
    protected $fillable = [
        'razon_social',
        'nombre_comercial',
        'nit',
        'matricula_comercio',
        'direccion_fiscal',
        'telefono',
        'email',
        'municipio',
        'departamento',
        'estado',
    ];

    // para que estado se maneje como bool y timestamps se auto-completen
    protected $casts = [
        'estado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;
}
