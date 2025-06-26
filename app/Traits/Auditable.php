<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\PistaAuditoria;

trait Auditable
{
    public static function bootAuditable()
    {
        static::updating(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getDirty();

            PistaAuditoria::create([
                'ip_maquina' => Request::ip(),
                'nombre_maquina' => gethostname(),
                'tabla_afectada' => $model->getTable(),
                'accion_realizada' => 'actualización',
                'info_antes' => json_encode($original),
                'info_despues' => json_encode($changes),
                'id_usuario' => Auth::id(),
            ]);
        });

        static::creating(function ($model) {
            PistaAuditoria::create([
                'ip_maquina' => Request::ip(),
                'nombre_maquina' => gethostname(),
                'tabla_afectada' => $model->getTable(),
                'accion_realizada' => 'creación',
                'info_antes' => null,
                'info_despues' => json_encode($model->getAttributes()),
                'id_usuario' => Auth::id(),
            ]);
        });

        static::deleting(function ($model) {
            PistaAuditoria::create([
                'ip_maquina' => Request::ip(),
                'nombre_maquina' => gethostname(),
                'tabla_afectada' => $model->getTable(),
                'accion_realizada' => 'eliminación',
                'info_antes' => json_encode($model->getAttributes()),
                'info_despues' => null,
                'id_usuario' => Auth::id(),
            ]);
        });
    }
}
