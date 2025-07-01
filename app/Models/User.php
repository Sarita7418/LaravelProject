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
        // NOTA: ya no se manejan los campos de codigo_verificacion, codigo_expira_en ni dos_pasos_habilitado aquí
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

    // Nueva relación con CodigoVerificacion
    public function codigoVerificacion()
    {
        return $this->hasOne(CodigoVerificacion::class, 'usuario_id');
    }
}
