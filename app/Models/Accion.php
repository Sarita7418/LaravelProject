<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Accion extends Model
{
    protected $table = 'acciones';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación con roles (quiénes tienen acceso a esta acción)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'accion_rol',
            'id_accion',
            'id_rol'
        );
    }
}
