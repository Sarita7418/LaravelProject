<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductoGenerico extends Model
{
    use HasFactory;

    protected $table = 'productos_genericos'; // Forzamos el nombre por si acaso

    protected $fillable = [
        'nombre',
        'codigo_atq',
    ];

    public function medicamentosLiname()
    {
        return $this->hasMany(MedicamentoLiname::class, 'producto_generico_id');
    }

    // EL ACCESSOR ACTUALIZADO
    protected function clasificacionSugerida(): Attribute
    {
        return Attribute::make(
            get: function () {
                // 1. Si no hay relación con Liname, retornamos null
                if ($this->medicamentosLiname->isEmpty()) {
                    return null;
                }

                return $this->medicamentosLiname
                    // Cargar la relación para optimizar (Eager Loading interno)
                    ->load('clasificacion')
                    
                    // 2. LA CLAVE: Usamos 'clasificacion.nombre'
                    // Esto entra al modelo MedicamentoLiname, salta a la relación 'clasificacion'
                    // y toma el campo 'nombre' de la tabla recursiva.
                    ->pluck('clasificacion.nombre') 
                    
                    // 3. Limpieza estándar
                    ->map(fn($item) => trim(mb_strtoupper($item))) 
                    ->filter()        // Quitar nulos (por si un liname no tiene clasificacion id)
                    ->unique()        // Quitar repetidos
                    ->implode(' / '); // Unir con barra
            }
        );
    }
}
