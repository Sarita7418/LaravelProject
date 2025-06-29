<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = ['id_menu_item', 'id_rol'];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'id_menu_item');
    }

    public function rol()
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }
}
