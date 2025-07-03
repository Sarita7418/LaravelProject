<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['descripcion'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

public function permisos()
{
    return $this->belongsToMany(Permiso::class, 'permiso_rol', 'rol_id', 'permiso_id');
}


}
