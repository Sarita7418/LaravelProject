<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicamentoLiname extends Model
{
    use HasFactory;

    protected $table = 'medicamentos_liname';

    protected $fillable = [
        'clasificacion_id',
        'producto_generico_id',
        'correlativo_go',
        'codigo_completo',
        'forma_farmaceutica',
        'concentracion',
        'uso_restringido',
    ];

    // Opcional: Relaciones para usarlas después
    public function generico()
    {
        return $this->belongsTo(ProductoGenerico::class, 'producto_generico_id');
    }

    public function clasificacion()
    {
        return $this->belongsTo(ClasificacionLiname::class, 'clasificacion_id'); // Asumiendo que tengas este modelo también, si no, créalo rápido.
    }
}