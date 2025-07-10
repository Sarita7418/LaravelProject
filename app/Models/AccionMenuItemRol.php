<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccionMenuItemRol extends Model
{
    protected $table = 'accion_menu_item_rol';

    protected $fillable = [
        'id_rol',
        'id_menu_item',
        'id_accion',
    ];

    public $timestamps = true;

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'id_menu_item');
    }

    public function accion(): BelongsTo
    {
        return $this->belongsTo(Accion::class, 'id_accion');
    }
}
