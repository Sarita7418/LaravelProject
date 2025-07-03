<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected $fillable = ['id_padre', 'nivel', 'item', 'id_url'];

    // Relación recursiva con hijos + sus propios hijos + URL
    public function hijos()
    {
        return $this->hasMany(MenuItem::class, 'id_padre')->with(['hijos', 'url']);
    }

    public function padre()
    {
        return $this->belongsTo(MenuItem::class, 'id_padre');
    }

    public function url()
    {
        return $this->belongsTo(Url::class, 'id_url');
    }

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'id_menu_item');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permisos', 'id_menu_item', 'id_rol');
    }

    // Hijos filtrados según el rol autenticado
public function hijosRecursive()
{
    return $this->hasMany(MenuItem::class, 'id_padre')
        ->whereHas('permisos', function ($q) {
            $q->where('id_rol', auth()->user()->role->id);
        })
        ->with(['hijosRecursive', 'url']);
}

}
