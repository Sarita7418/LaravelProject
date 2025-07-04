<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComentariosRevision extends Model
{
    protected $table = 'comentarios_revision';
    
    protected $fillable = [
        'id_protocolos',
        'id_usuario',
        'comentario',
        'fecha'
    ];
    
    protected $casts = [
        'fecha' => 'date'
    ];
    
    // Relaciones
    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class, 'id_protocolos');
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}