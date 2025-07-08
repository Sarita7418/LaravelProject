<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemRol extends Model
{
    protected $table = 'menu_item_rol';

    protected $fillable = [
        'id_rol',
        'id_menu_item',
    ];

    public $timestamps = true;

    // Rol asociado
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

    // Menú asociado
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'id_menu_item');
    }
}
