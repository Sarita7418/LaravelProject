<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{

    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'telefono',
        'fecha_nacimiento',
        'estado'
    ];


        
        // Persona.php
    public function usuario()
    {
        return $this->hasOne(User::class, 'id_persona');
    }

    // User.php
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

}
