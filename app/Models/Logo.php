<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logo extends Model
{
    protected $table = 'logos';
    public $timestamps = true;

    protected $fillable = [
        'id_entidad',
        'tipo_entidad', // 'empresa' | 'sucursal'
        'logo',         // BLOB
    ];

    // Si quieres, puedes declarar el cast a "binary" (opcional)
    // protected $casts = ['logo' => 'binary'];

    // Si usas la relación para index():
    // La tabla no usa los nombres morfológicos por defecto,
    // así que declara morphTo con columnas personalizadas:
    public function entidad()
    {
        return $this->morphTo(
            name: null,
            type: 'tipo_entidad',
            id: 'id_entidad'
        );
    }
}
