<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

    public function permisos()
    {
        return $this->role->permisos();
    }

    public function codigoVerificacion()
    {
        return $this->hasOne(CodigoVerificacion::class, 'usuario_id');
    }
}
