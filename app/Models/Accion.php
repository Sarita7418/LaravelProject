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

    // Relación muchos a muchos con MenuItem
    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(
            MenuItem::class,
            'accion_menu_item',
            'accion_id',
            'menu_item_id'
        );
    }
}
