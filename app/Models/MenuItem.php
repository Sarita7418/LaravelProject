<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected $fillable = [
        'item',
        'ruta',
        'id_padre',
        'nivel',
        'orden',
    ];

    // Relación con roles (quién puede acceder a este ítem de menú)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'menu_item_rol',
            'id_menu_item',
            'id_rol'
        );
    }

    // Relación muchos a muchos con acciones
    public function acciones(): BelongsToMany
    {
        return $this->belongsToMany(
            Accion::class,
            'accion_menu_item',
            'id_menu_item',
            'id_accion'
        );
    }

    // Menú padre (si es submenú)
    public function padre(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'id_padre');
    }

    // Submenús o hijos
    public function hijos(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'id_padre');
    }
}
