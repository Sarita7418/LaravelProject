<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Especificamos el nombre de la tabla personalizada
    protected $table = 'usuarios';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'nombre',
        'correo',
        'contrasena',
        'rol_id',
    ];

    // Campos ocultos en serialización (JSON, etc.)
    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    // Casts automáticos de atributos
    protected function casts(): array
    {
        return [
            'correo_verificado_en' => 'datetime',
            'contrasena' => 'hashed',
        ];
    }

    // Relación con roles
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function hasVerifiedEmail()
    {
        return ! is_null($this->correo_verificado_en);
    }

    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'correo_verificado_en' => $this->freshTimestamp(),
        ])->save();
    }

    public function getEmailForVerification()
    {
        return $this->correo;
    }

    public function getAuthPassword()
    {
        return $this->contrasena; // 👈 Laravel usará esto en vez de 'password'
    }

    public function getAuthIdentifierName()
    {
        return 'correo'; // 👈 Laravel usará esto en vez de 'email'
    }
}
