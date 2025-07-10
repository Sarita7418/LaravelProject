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

    // Relación con usuarios
    public function users()
    {
        return $this->hasMany(User::class, 'id_rol');
    }

    // Relación con items de menú (accesos de navegación)
    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(
            MenuItem::class,
            'menu_item_rol',
            'id_rol',
            'id_menu_item'
        );
    }

    // Scope para roles activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    // Scope para roles inactivos
    public function scopeInactivos($query)
    {
        return $query->where('estado', 0);
    }
}
