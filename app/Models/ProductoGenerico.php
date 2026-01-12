<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoGenerico extends Model
{
    use HasFactory;

    protected $table = 'productos_genericos'; // Forzamos el nombre por si acaso

    protected $fillable = [
        'nombre',
        'codigo_atq',
    ];
}