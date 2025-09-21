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

    // Relación con todas las cuentas hijas (incluyendo inactivas)
    public function todosLosHijos()
    {
        return $this->hasMany(self::class, 'id_padre')
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

    // Scopes para filtros comunes
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

    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    public function scopeNivel1($query)
    {
        return $query->where('nivel', 1);
    }

    public function scopeHijasDe($query, $padreId)
    {
        return $query->where('id_padre', $padreId);
    }

    public function scopeConCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }

    public function scopeQueInicianCon($query, $prefijo)
    {
        return $query->where('codigo', 'LIKE', $prefijo . '%');
    }

    // Métodos helper para verificar estados
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
        return $this->cuenta_presupuesto === 11; // ID 11 = Sí (CORREGIDO: era 13)
    }

    public function esNivel1()
    {
        return $this->nivel === 1;
    }

    public function puedeSerEliminada()
    {
        // No se pueden eliminar cuentas de nivel 1 o que tengan hijos activos
        if ($this->nivel === 1) {
            return false;
        }
        
        return !$this->hijos()->exists();
    }

    public function puedeSerPadre()
    {
        // Solo cuentas de nivel 1-5 pueden ser padres (máximo nivel 6)
        return $this->nivel >= 1 && $this->nivel <= 5 && $this->estaActiva();
    }

    // Métodos para obtener información jerárquica
    public function obtenerRutaCompleta()
    {
        $ruta = [];
        $cuenta = $this;
        
        while ($cuenta) {
            array_unshift($ruta, $cuenta->descripcion);
            $cuenta = $cuenta->padre;
        }
        
        return implode(' > ', $ruta);
    }

    public function obtenerCodigoCompleto()
    {
        return $this->codigo;
    }

    public function contarHijosActivos()
    {
        return $this->hijos()->count();
    }

    public function contarTodosLosHijos()
    {
        return $this->todosLosHijos()->count();
    }

    // Accessors para obtener descripciones de subdominios
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

    // Accessor para mostrar el estado como texto
    public function getEstadoTextoAttribute()
    {
        return $this->estado ? 'ACTIVO' : 'INACTIVO';
    }

    // Accessor para mostrar información completa de la cuenta
    public function getInfoCompletaAttribute()
    {
        return [
            'codigo' => $this->codigo,
            'descripcion' => $this->descripcion,
            'nivel' => $this->nivel,
            'tipo' => $this->tipoTexto,
            'grupo_estado' => $this->grupoEstadoTexto,
            'cuenta_ajuste' => $this->cuentaAjusteTexto,
            'cuenta_presupuesto' => $this->cuentaPresupuestoTexto,
            'estado' => $this->estadoTexto,
            'ruta_completa' => $this->obtenerRutaCompleta(),
            'puede_ser_eliminada' => $this->puedeSerEliminada(),
            'puede_ser_padre' => $this->puedeSerPadre(),
            'hijos_activos' => $this->contarHijosActivos()
        ];
    }

    // Métodos estáticos útiles
    public static function obtenerCuentasNivel1()
    {
        return self::nivel1()->activas()->orderBy('codigo')->get();
    }

    public static function obtenerCuentasPadre()
    {
        return self::whereIn('nivel', [1, 2, 3, 4, 5])
               ->activas()
               ->orderBy('codigo')
               ->get();
    }

    public static function buscarPorCodigo($codigo)
    {
        return self::where('codigo', $codigo)->first();
    }

    public static function contarPorNivel()
    {
        return self::selectRaw('nivel, COUNT(*) as total')
               ->groupBy('nivel')
               ->orderBy('nivel')
               ->pluck('total', 'nivel')
               ->toArray();
    }

    public static function contarPorEstado()
    {
        return [
            'activas' => self::where('estado', true)->count(),
            'inactivas' => self::where('estado', false)->count()
        ];
    }

    // Método para obtener el siguiente código disponible
    public function obtenerSiguienteCodigo()
    {
        if (!$this->puedeSerPadre()) {
            return null;
        }

        $hermanos = self::where('id_padre', $this->id)
                       ->where('estado', true)
                       ->count();

        $nuevoNivel = $this->nivel + 1;
        $siguienteNumero = $hermanos + 1;

        if ($nuevoNivel <= 3) {
            return $this->codigo . '.' . $siguienteNumero;
        } else {
            $numeroFormateado = str_pad($siguienteNumero, 3, '0', STR_PAD_LEFT);
            return $this->codigo . '.' . $numeroFormateado;
        }
    }

    // Validaciones personalizadas
    public function validarEstructuraJerarquica()
    {
        $errores = [];

        // Verificar que el padre exista si no es nivel 1
        if ($this->nivel > 1 && !$this->padre) {
            $errores[] = 'Cuenta de nivel ' . $this->nivel . ' debe tener un padre válido';
        }

        // Verificar que el nivel sea consistente con el padre
        if ($this->padre && $this->nivel !== ($this->padre->nivel + 1)) {
            $errores[] = 'El nivel debe ser exactamente 1 más que el nivel del padre';
        }

        // Verificar que no exceda el nivel máximo
        if ($this->nivel > 6) {
            $errores[] = 'No se pueden crear cuentas más allá del nivel 6';
        }

        return $errores;
    }
}