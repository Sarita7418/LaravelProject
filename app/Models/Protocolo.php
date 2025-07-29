<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Protocolo extends Model
{
    protected $table = 'protocolos';
    
    protected $fillable = [
        'id_usuario_creador',
        'id_especialidad',
        'id_estado',
        'titulo',
        'resumen',
        'objetivo_general',
        'metodologia',
        'justificacion',
        'fecha_creacion',
    ];
    
    protected $casts = [
        'fecha_creacion' => 'date'
    ];
    
    // Relaciones
    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'id_usuario_creador');
    }
    
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'id_especialidad');
    }
    
    public function estado()
    {
        return $this->belongsTo(Subdominio::class, 'id_estado');
    }
        
    public function versionesProtocolo()
    {
        return $this->hasMany(VersionesProtocolo::class, 'id_protocolo');
    }
    
    public function documentosAdjuntos()
    {
        return $this->hasMany(DocumentosAdjunto::class, 'id_protocolos');
    }
    
    public function comentariosRevision()
    {
        return $this->hasMany(ComentariosRevision::class, 'id_protocolos');
    }

    public function areasImpacto()
    {
        return $this->belongsToMany(
            AreaImpacto::class,
            'area_impacto_protocolo',
            'id_protocolo',
            'id_area_impactos'
        )->withTimestamps();
    }

}