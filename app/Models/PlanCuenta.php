<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanCuenta extends Model
{
    protected $table = 'plan_cuentas';
    
    protected $fillable = [
        'codigo',
        'descripcion',
        'tipo',
        'nivel',
        'grupo_estado_financiero',
        'id_padre',
        'cuenta_ajuste',
        'cuenta_presupuesto',
        'estado'
    ];

    protected $casts = [
        'tipo' => 'integer',
        'nivel' => 'integer',
        'grupo_estado_financiero' => 'integer',
        'id_padre' => 'integer',
        'cuenta_ajuste' => 'integer',
        'cuenta_presupuesto' => 'integer',
        'estado' => 'boolean'
    ];

    public $timestamps = true;

    // Relación con cuenta padre
    public function padre()
    {
        return $this->belongsTo(self::class, 'id_padre');
    }

    // Relación con cuentas hijas
    public function hijos()
    {
        return $this->hasMany(self::class, 'id_padre')
               ->where('estado', true)
               ->orderBy('codigo');
    }

    // Relaciones con subdominios
    public function tipoSubdominio()
    {
        return $this->belongsTo(Subdominio::class, 'tipo');
    }

    public function grupoEstadoFinancieroSubdominio()
    {
        return $this->belongsTo(Subdominio::class, 'grupo_estado_financiero');
    }

    public function cuentaAjusteSubdominio()
    {
        return $this->belongsTo(Subdominio::class, 'cuenta_ajuste');
    }

    public function cuentaPresupuestoSubdominio()
    {
        return $this->belongsTo(Subdominio::class, 'cuenta_presupuesto');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', true);
    }

    public function scopeInactivas($query)
    {
        return $query->where('estado', false);
    }

    public function scopeGrupos($query)
    {
        return $query->where('tipo', 4); // ID 4 = Grupo
    }

    public function scopeDetalles($query)
    {
        return $query->where('tipo', 5); // ID 5 = Detalle
    }

    public function scopePorGrupoEstado($query, $grupoId)
    {
        return $query->where('grupo_estado_financiero', $grupoId);
    }

    // Métodos helper
    public function estaActiva()
    {
        return $this->estado === true;
    }

    public function esGrupo()
    {
        return $this->tipo === 4; // ID 4 = Grupo
    }

    public function esDetalle()
    {
        return $this->tipo === 5; // ID 5 = Detalle
    }

    public function esCuentaAjuste()
    {
        return $this->cuenta_ajuste === 11; // ID 11 = Sí
    }

    public function esCuentaPresupuesto()
    {
        return $this->cuenta_presupuesto === 11; // ID 13 = Sí
    }

    // Accessors para obtener descripciones
    public function getTipoTextoAttribute()
    {
        return $this->tipoSubdominio?->descripcion ?? 'N/A';
    }

    public function getGrupoEstadoTextoAttribute()
    {
        return $this->grupoEstadoFinancieroSubdominio?->descripcion ?? 'N/A';
    }

    public function getCuentaAjusteTextoAttribute()
    {
        return $this->cuentaAjusteSubdominio?->descripcion ?? 'N/A';
    }

    public function getCuentaPresupuestoTextoAttribute()
    {
        return $this->cuentaPresupuestoSubdominio?->descripcion ?? 'N/A';
    }
}