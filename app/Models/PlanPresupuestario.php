<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPresupuestario extends Model
{
    use HasFactory;

    protected $table = 'plan_presupuestarios';

    protected $fillable = [
        'codigo',
        'descripcion',
        'tipo',
        'nivel',
        'id_padre',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'nivel' => 'integer',
        'tipo' => 'integer',
        'id_padre' => 'integer'
    ];

    // Relación con subdominio para tipo
    public function tipoSubdominio()
    {
        return $this->belongsTo(Subdominio::class, 'tipo', 'id');
    }

    // Relación padre-hijo
    public function padre()
    {
        return $this->belongsTo(PlanPresupuestario::class, 'id_padre');
    }

    public function hijos()
    {
        return $this->hasMany(PlanPresupuestario::class, 'id_padre');
    }

    // Accessor para obtener texto del tipo
    public function getTipoTextoAttribute()
    {
        if (!$this->tipo) {
            return '-';
        }
        return $this->tipoSubdominio ? $this->tipoSubdominio->descripcion : $this->tipo;
    }

    // Scope para obtener solo activos
    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }

    // Scope para obtener por nivel
    public function scopePorNivel($query, $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    // Scope para obtener hijos de un padre específico
    public function scopeHijosDe($query, $padreId)
    {
        return $query->where('id_padre', $padreId);
    }

    // Método para verificar si puede tener hijos
    public function puedeParent()
    {
        return $this->nivel < 5; // Solo hasta nivel 4 puede tener hijos (máximo nivel 5)
    }

    // Método para obtener la ruta completa (breadcrumb)
    public function getRutaCompleta()
    {
        $ruta = [];
        $actual = $this;
        
        while ($actual) {
            array_unshift($ruta, $actual);
            $actual = $actual->padre;
        }
        
        return $ruta;
    }

    // Método para obtener todos los descendientes
    public function getDescendientes()
    {
        $descendientes = collect();
        
        foreach ($this->hijos as $hijo) {
            $descendientes->push($hijo);
            $descendientes = $descendientes->merge($hijo->getDescendientes());
        }
        
        return $descendientes;
    }

    // Método para verificar si tiene descendientes activos
    public function tieneDescendientesActivos()
    {
        return $this->hijos()->where('estado', true)->exists() ||
               $this->hijos()->where('estado', true)->get()->some(function ($hijo) {
                   return $hijo->tieneDescendientesActivos();
               });
    }

    // Método para formatear el código con separadores visuales
    public function getCodigoFormateadoAttribute()
    {
        if (!$this->codigo) return '';
        
        // Para códigos de 5 dígitos del plan presupuestario
        $codigo = str_pad($this->codigo, 5, '0', STR_PAD_LEFT);
        
        switch ($this->nivel) {
            case 1:
                return $codigo; // 10000
            case 2:
                return substr($codigo, 0, 2) . '.' . substr($codigo, 2); // 11.000
            case 3:
                return substr($codigo, 0, 2) . '.' . substr($codigo, 2, 1) . '.' . substr($codigo, 3); // 11.1.00
            case 4:
                return substr($codigo, 0, 2) . '.' . substr($codigo, 2, 1) . '.' . substr($codigo, 3, 1) . '.' . substr($codigo, 4); // 11.1.1.0
            case 5:
                return substr($codigo, 0, 2) . '.' . substr($codigo, 2, 1) . '.' . substr($codigo, 3, 1) . '.' . substr($codigo, 4, 1); // 11.1.1.1
            default:
                return $codigo;
        }
    }

    // Método para obtener el siguiente código disponible en un nivel
    public static function siguienteCodigoEnNivel($padreId, $nivel)
    {
        $padre = null;
        if ($padreId) {
            $padre = self::find($padreId);
        }

        $ultimoCodigo = self::where('id_padre', $padreId)
            ->where('nivel', $nivel)
            ->orderBy('codigo', 'desc')
            ->first();

        if (!$ultimoCodigo) {
            // Primer hijo en este nivel
            if ($nivel == 1) {
                return '10000';
            } else if ($padre) {
                return self::calcularPrimerCodigo($padre, $nivel);
            }
        } else {
            // Incrementar basado en el último código
            return self::incrementarCodigo($ultimoCodigo->codigo, $nivel);
        }

        return null;
    }

    private static function calcularPrimerCodigo($padre, $nivel)
    {
        $codigoPadre = $padre->codigo;
        
        switch ($nivel) {
            case 2:
                return substr($codigoPadre, 0, 2) . '1000'; // 10000 -> 11000
            case 3:
                return substr($codigoPadre, 0, 3) . '00'; // 11000 -> 11100
            case 4:
                return substr($codigoPadre, 0, 4) . '0'; // 11100 -> 11110
            case 5:
                return str_pad(intval($codigoPadre) + 1, 5, '0', STR_PAD_LEFT); // 11110 -> 11111
        }
        
        return null;
    }

    private static function incrementarCodigo($codigo, $nivel)
    {
        $codigoInt = intval($codigo);
        
        switch ($nivel) {
            case 1:
                return str_pad($codigoInt + 10000, 5, '0', STR_PAD_LEFT);
            case 2:
                return str_pad($codigoInt + 1000, 5, '0', STR_PAD_LEFT);
            case 3:
                return str_pad($codigoInt + 100, 5, '0', STR_PAD_LEFT);
            case 4:
                return str_pad($codigoInt + 10, 5, '0', STR_PAD_LEFT);
            case 5:
                return str_pad($codigoInt + 1, 5, '0', STR_PAD_LEFT);
        }
        
        return null;
    }

    // Validaciones personalizadas
    public static function validarCodigo($codigo, $nivel)
    {
        $patron = '';
        
        switch ($nivel) {
            case 1:
                $patron = '/^\d0000$/'; // X0000
                break;
            case 2:
                $patron = '/^\d\d000$/'; // XX000
                break;
            case 3:
                $patron = '/^\d\d\d00$/'; // XXX00
                break;
            case 4:
                $patron = '/^\d\d\d\d0$/'; // XXXX0
                break;
            case 5:
                $patron = '/^\d{5}$/'; // XXXXX
                break;
        }
        
        return preg_match($patron, $codigo);
    }

    // Método para exportar estructura jerárquica
    public function toTreeArray()
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'codigo_formateado' => $this->codigo_formateado,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo_texto,
            'nivel' => $this->nivel,
            'estado' => $this->estado,
            'hijos' => $this->hijos()->activos()->orderBy('codigo')->get()->map(function ($hijo) {
                return $hijo->toTreeArray();
            })->toArray()
        ];
    }
}