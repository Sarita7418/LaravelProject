<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoComercial extends Model
{
    use HasFactory;

    protected $table = 'productos_comerciales';

    protected $fillable = [
        'producto_generico_id',
        'nro_registro_sanitario',
        'nombre_comercial',
        'forma_farmaceutica',
        'concentracion',
        'laboratorio_fabricante',
        'empresa_proveedora',
        'pais_origen',
    ];

    // Relación inversa
    public function generico()
    {
        return $this->belongsTo(ProductoGenerico::class, 'producto_generico_id');
    }
}