<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';
    // Si tu PK no es "id", descomenta y ajusta:
    // protected $primaryKey = 'id_empresas';
    // public $incrementing = true;
    // protected $keyType = 'int';

    public $timestamps = true;

    /**
     * Campos permitidos para asignación masiva (create/update).
     */
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
        'id_representante_legal',
    ];

    /**
     * Casts de tipos para lecturas/escrituras consistentes.
     */
    protected $casts = [
        'estado'                 => 'boolean',
        'id_representante_legal' => 'integer',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    /**
     * Valores por defecto (opcional).
     */
    protected $attributes = [
        'estado' => 1,
    ];

    /**
     * Relaciones
     */

    // Representante legal (FK local: id_representante_legal -> personas.id)
    public function representanteLegal()
    {
        return $this->belongsTo(Persona::class, 'id_representante_legal', 'id');
    }

    // Sucursales (FK en sucursales: id_empresa -> empresas.id)
    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'id_empresa', 'id');
    }

    // Logos: tu FE asocia por id_entidad, así que no uses morph aquí.
    public function logos()
    {
        return $this->hasMany(Logo::class, 'id_entidad', 'id');
    }

    /**
     * Scopes útiles (opcional)
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 1);
    }

    public function scopeInactivas($query)
    {
        return $query->where('estado', 0);
    }
}
