<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['descripcion', 'estado'];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

   public function permisos()
   {
    return $this->belongsToMany(Permiso::class, 'permiso_rol', 'rol_id', 'permiso_id');
   }
   // Scope para obtener solo roles activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    // Scope para obtener solo roles inactivos
    public function scopeInactivos($query)
    {
        return $query->where('estado', 0);
    }

}
