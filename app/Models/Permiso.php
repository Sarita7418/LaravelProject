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

    public function roles()
{
    return $this->belongsToMany(Role::class, 'permiso_rol', 'permiso_id', 'rol_id');
}

}
