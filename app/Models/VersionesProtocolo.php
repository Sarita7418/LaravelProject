<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VersionesProtocolo extends Model
{
    protected $table = 'versiones_protocolos';
    
    protected $fillable = [
        'id_protocolo',
        'numero_version',
        'fecha_modificacion',
        'observaciones'
    ];
    
    protected $casts = [
        'fecha_modificacion' => 'date'
    ];
    
    // Relaciones
    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class, 'id_protocolo');
    }
}