<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentosAdjunto extends Model
{
    protected $table = 'documentos_adjuntos';
    
    protected $fillable = [
        'id_protocolos',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_documento'
    ];
    
    // Relaciones
    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class, 'id_protocolos');
    }
}