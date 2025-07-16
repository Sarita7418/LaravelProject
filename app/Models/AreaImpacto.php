<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AreaImpacto extends Model
{
    use HasFactory;

    // Asegúrate que esta tabla exista como 'area_impactos' (en plural)
    protected $table = 'area_impactos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación muchos a muchos con el modelo Protocolo
     */
    public function protocolos(): BelongsToMany
    {
        return $this->belongsToMany(
            Protocolo::class,            // Modelo relacionado
            'area_impacto_protocolo',    // Nombre de la tabla pivote
            'id_area_impactos',           // Clave foránea en la tabla pivote que apunta a este modelo
            'id_protocolo'               // Clave foránea en la tabla pivote que apunta al modelo Protocolo
        )->withTimestamps();             // Mantiene timestamps en la tabla pivote
    }
}
