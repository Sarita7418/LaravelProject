<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $table = 'usuarios';
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
    'name', 'email', 'password', 'id_rol', 'codigo_verificacion', 'codigo_expira_en', 'dos_pasos_habilitado',
];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'codigo_verificacion',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'codigo_expira_en' => 'datetime',
            'dos_pasos_habilitado' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function role()
{
    return $this->belongsTo(Role::class, 'id_rol');
}

    public function permisos()
{
    return $this->role->permisos();
}

}
