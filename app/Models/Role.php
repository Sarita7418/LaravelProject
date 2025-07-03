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

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'permisos', 'id_rol', 'id_menu_item');
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