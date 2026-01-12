<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClasificacionLiname extends Model
{
    use HasFactory;

    // Importante: definir la tabla porque Laravel buscaría 'clasificacion_linames'
    protected $table = 'clasificaciones_liname';

    protected $fillable = [
        'nivel',
        'codigo',
        'nombre',
        'padre_id',
    ];

    // Relación: Un subgrupo tiene un padre (El 01 pertenece al A)
    public function padre()
    {
        return $this->belongsTo(ClasificacionLiname::class, 'padre_id');
    }

    // Relación: Un grupo tiene muchos hijos (El A tiene al 01, 02, 03...)
    public function hijos()
    {
        return $this->hasMany(ClasificacionLiname::class, 'padre_id');
    }

    // Relación: Una clasificación tiene muchos medicamentos
    public function medicamentos()
    {
        return $this->hasMany(MedicamentoLiname::class, 'clasificacion_id');
    }
}