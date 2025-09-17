<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    // Campos permitidos para create()/update()
    protected $fillable = [
        'id_empresa',
        'nombre',
        'codigo_sucursal',
        'direccion',
        'telefono',
        'email',
        'estado',
        'id_sucursal_padre', // Nuevo campo para sub-sucursales
        'id_representante_legal', // Nuevo campo para representante legal
    ];

    // Casts (estado boolean; id_empresa entero si lo usas así en BDD)
    protected $casts = [
        'estado' => 'boolean',
        'id_empresa' => 'integer',
        'id_sucursal_padre' => 'integer',  // Convertir id_sucursal_padre en entero
    ];

    public $timestamps = true;

    // Relación con la empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    // Relación recursiva con sub-sucursales (si es una sub-sucursal)
    public function sucursalPadre()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal_padre');
    }

    // Relación con las sub-sucursales
    public function subSucursales()
    {
        return $this->hasMany(Sucursal::class, 'id_sucursal_padre');
    }

    // Relación con el representante legal
    public function representanteLegal()
    {
        return $this->belongsTo(Persona::class, 'id_representante_legal');
    }

    // Relación con los logos (polimórfica)
    public function logos()
    {
        return $this->morphMany(Logo::class, 'entidad');
    }
}
