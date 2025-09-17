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
        'id_representante_legal',  // Nuevo campo
    ];

    // para que estado se maneje como bool y timestamps se auto-completen
    protected $casts = [
        'estado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    // Relación con el representante legal
    public function representanteLegal()
    {
        return $this->belongsTo(Persona::class, 'id_representante_legal');
    }

    // Relación con las sucursales
    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'id_empresa');
    }

    // Relación con los logos
    public function logos()
    {
        return $this->morphMany(Logo::class, 'entidad');
    }
}
