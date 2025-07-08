<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccionRol extends Model
{
    protected $table = 'accion_rol';

    protected $fillable = [
        'id_rol',
        'id_accion',
    ];

    public $timestamps = true;

    // Relación con el rol
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

    // Relación con la acción
    public function accion(): BelongsTo
    {
        return $this->belongsTo(Accion::class, 'id_accion');
    }
}
